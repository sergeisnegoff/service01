<?php


namespace App\Service\Form;


use App\Model\Base\FormFieldOptionQuery;
use App\Model\Form;
use App\Model\FormField;
use App\Model\FormFieldQuery;
use App\Model\FormQuery;
use App\Model\FormReportSubjectQuery;
use App\Model\FormResult;
use App\Model\FormResultField;
use App\Model\FormResultFieldQuery;
use App\Service\User\UserService;
use Cocur\Slugify\SlugifyInterface;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class FormService
{
    protected $request;
    protected $mailing;
    protected $url = '/api/forms/';
    /**
     * @var UserService
     */
    private UserService $userService;
    /**
     * @var SlugifyInterface
     */
    private SlugifyInterface $slugify;
    private $kernelProjectDir;

    public function __construct(
        RequestStack $requestStack,
        UserService $userService,
        SlugifyInterface $slugify,
        $kernelProjectDir
    )
    {
        $this->request = $requestStack->getMasterRequest();
        $this->userService = $userService;
        $this->slugify = $slugify;
        $this->kernelProjectDir = $kernelProjectDir;
    }

    public function getForm($code)
    {
        if( $code > 0) {
            return FormQuery::create()->filterByVisible(true)->findPk($code);

        } else {
            return FormQuery::create()->filterByVisible(true)->findOneByCode($code);
        }
    }

    public function getFields(Form $form)
    {
        return FormFieldQuery::create()->filterByVisible(true)->filterByFormId($form->getId())->filterByVisible(true)->orderBySortableRank()->find();
    }

    public function getConstraints($field){
        $constraints = [];
        if($field->getRequired()){
            $constraints[] = new NotBlank($field->getRequiredError() ? ['message' => $field->getRequiredError()] : null);
        }

        if($field->getValidationType()){
            $options = [];
            $regEx = false;
            if($field->getValidationType() == Form::VALIDATION_DIGITS){
                $regEx = true;
                $options = [
                    'pattern' => '/^-?(?:\d+|\d*\.\d+){2}$/',
                    'htmlPattern' => '/^-?(?:\d+|\d*\.\d+)$/'
                ];

            }elseif($field->getValidationType() == Form::VALIDATION_EMAIL){

                if($field->getInvalidError()) $options['message'] = $field->getInvalidError();
                $constraints[] = new Email($options);
            }elseif ($field->getValidationType() == Form::VALIDATION_ALPHABETICAL){

                $regEx = true;
                $options = [
                    'pattern' => '/^[a-zA-Zа-яА-Я \-]{3,50}$/ui',
                    'htmlPattern' => '/^[a-zA-Zа-яА-Я \-]{3,50}$/ui'
                ];

            }elseif ($field->getValidationType() == Form::VALIDATION_PHONE){
                $regEx = true;
                $options = [
                    'pattern' => '/^\+\d *(\(\d{3,4}\)|\d{3,4})([ -]*\d){6,7}$/',
                    'htmlPattern' => '/^\+\d *(\(\d{3,4}\)|\d{3,4})([ -]*\d){6,7}$/'
                ];

            } else if ($field->getValidationType() == Form::VALIDATION_FILE) {
                $constraints[] = new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]
                ]);
            }

            if($regEx){
                if($field->getInvalidError()) $options['message'] = $field->getInvalidError();
                $constraints[] = new Regex($options);
            }
        }

        return $constraints;
    }

    public function validate(Form $form)
    {

    }

    public function saveResult(Form $form, $data, $result = null)
    {
        $files = [];

        if(!$result){
            $result = new FormResult();
        }

        $result
            ->setIpAddress($this->request->getClientIp())
            ->setStatus(FormResult::STATUS_NEW)
            ->setUrlFrom($data['url'])
            ->setForm($form)
            ->setFormTitle($form->getTitle())
        ;

        if ($user = $this->userService->getCurrentUser()) {
            $result->setUserId($user->getId());
        }

        $result->save();

        foreach($form->getFields() as $field){
            if(!isset($data[$field->getFieldName()])) continue;
            $resultField = new FormResultField();
            $resultField
                ->setFieldId($field->getId())
                ->setForm($form)
                ->setResultId($result->getId())
                ->setSortableRank($field->getSortableRank())
                ->save();

            if ($field->isSubject()) {
                $resultField->setSubjectId($data[$field->getFieldName()]);

            } else if($field->isFile()){
                /** @var UploadedFile[] $uploadedFiles */
                $uploadedFiles = $data[$field->getFieldName()];

                if (!is_array($uploadedFiles)) {
                    $uploadedFiles = [$uploadedFiles];
                }

                $slugifyService = $this->slugify;

                $file_name = $slugifyService->slugify($form->getTitle()) . '_' . $result->getId() . '_' . $field->getId() . '_' . date('m_d_Y_H_i_s').'.zip';
                $zipWebPath = '/uploads/form/'.$file_name;
                $zipPath = $this->kernelProjectDir.'/public'.$zipWebPath;
                $zipDirPath = $this->kernelProjectDir.'/public/uploads/form';

                if(!is_dir($zipDirPath)) mkdir($zipDirPath);

                $zip = new \ZipArchive();
                $zip->open($zipPath, \ZipArchive::CREATE);

                foreach ($uploadedFiles as $uploadedFile) {
                    if (!$uploadedFile instanceof UploadedFile) {
                        continue;
                    }

                    $zip->addFile($uploadedFile->getRealPath(), $slugifyService->slugify($uploadedFile->getClientOriginalName()) . '.' . $uploadedFile->getClientOriginalExtension());
                }

                if ($zip->count()) {
                    $files[] = $zipPath;
                    $resultField->setFilePath($zipWebPath);
                }

            }else{
                if ($field->hasOptions()) {
                    $fieldData = $data[$field->getFieldName()];
                    if (!is_array($fieldData)) {
                        $fieldData = [$fieldData];
                    }

                    $resultValues = [];
                    foreach ($fieldData as $fieldDataItem) {
                        $option = FormFieldOptionQuery::create()
                            ->filterByFieldId($field->getId())
                            ->filterById($fieldDataItem)
                            ->findOne();

                        $resultValues[] = $option ? $option->getTitle() : $fieldDataItem;
                    }

                    $resultField->setValue(implode(', ', $resultValues));

                } else {
                    $resultField->setValue($data[$field->getFieldName()]);
                }

            }

            $resultField->save();
        }

        return $result;

    }

    /**
     * @param FormResult $formResult
     * @return FormResultField[]|ObjectCollection
     * @throws PropelException
     */
    public function getFormResultFields(FormResult $formResult): ObjectCollection
    {
        return FormResultFieldQuery::create()
            ->filterByFormResult($formResult)
            ->useFormFieldQuery()
            ->endUse()
            ->orderBySortableRank()
            ->find();
    }


    public function sendNotification(FormResult $result){

        $form = $result->getForm();
        $mailing = $this->mailing;
        $notification_email = $form->getNotificationEmail();

        if($notification_email){
            $message = $mailing->createMessage($result->getFormTitle(), ['form.new' => [
                'content' => $result->getContent(),
                'url' => $this->container->get('router')->generate('creonit_admin_module', ['module' => 'forms'], Router::ABSOLUTE_URL),
                'form' => $form,
                'result' => $result,
            ]]);
            $mailing->send($message, $notification_email);
        }

        if($result->getEmail() && $form->getMailingTemplateId()){
            $mailingTemplate = $form->getMailingTemplate();
            $message = $mailing->createMessage(
                $mailingTemplate ? '' : $result->getFormTitle(),
                [$mailingTemplate ? $mailingTemplate->getName() : 'form.success' => [
                    'content' => $result->getShortContent(),
                    'url' => $this->container->get('router')->generate('creonit_admin_module', ['module' => 'forms'], Router::ABSOLUTE_URL),
                    'form' => $form,
                    'result' => $result,
                ]]
            );
            $mailing->send($message, $result->getEmail());
        }

    }

    public function getFormReportSubjects()
    {
        $subjects = FormReportSubjectQuery::create()
            ->orderBySortableRank()
            ->find();

        return array_map(fn($subject) => ['id' => $subject->getId(), 'title' => $subject->getTitle()], $subjects->getData());
    }
}
