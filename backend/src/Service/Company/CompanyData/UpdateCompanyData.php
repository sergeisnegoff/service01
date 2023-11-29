<?php


namespace App\Service\Company\CompanyData;


use App\Model\Company;
use App\Model\UserGroup;
use App\Service\Company\Exception\UpdateCompanyException;
use App\Service\DataObject\DataObjectInterface;
use App\Validator\Constraints\NotBlank;
use Creonit\MailingBundle\Config\ParameterBag;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateCompanyData implements DataObjectInterface
{
    protected Company $company;

    protected string $title = '';
    protected string $diadocExternalCode = '';
    protected string $docrobotExternalCode = '';
    protected string $storehouseExternalCode = '';
    protected string $email = '';
    protected string $description = '';
    protected string $inn = '';
    protected string $kpp = '';
    protected string $site = '';
    protected string $deliveryTerm = '';
    protected string $paymentTerm = '';
    protected string $minOrderAmount = '';
    protected array $images = [];
    protected array $deleteImagesId = [];

    /**
     * @var UploadedFile|null|int
     */
    protected $logo;

    protected ?ValidatorInterface $validator;

    /**
     * @return string
     */
    public function getStorehouseExternalCode(): string
    {
        return $this->storehouseExternalCode;
    }

    /**
     * @param string $storehouseExternalCode
     */
    public function setStorehouseExternalCode(string $storehouseExternalCode): self
    {
        $this->storehouseExternalCode = $storehouseExternalCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getDiadocExternalCode(): string
    {
        return $this->diadocExternalCode;
    }

    /**
     * @param string $diadocExternalCode
     */
    public function setDiadocExternalCode(string $diadocExternalCode): self
    {
        $this->diadocExternalCode = $diadocExternalCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getDocrobotExternalCode(): string
    {
        return $this->docrobotExternalCode;
    }

    /**
     * @param string $docrobotExternalCode
     */
    public function setDocrobotExternalCode(string $docrobotExternalCode): self
    {
        $this->docrobotExternalCode = $docrobotExternalCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return Company
     */
    public function getCompany(): Company
    {
        return $this->company;
    }

    /**
     * @param Company $company
     */
    public function setCompany(Company $company): self
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return ValidatorInterface|null
     */
    public function getValidator(): ?ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * @param ValidatorInterface|null $validator
     */
    public function setValidator(?ValidatorInterface $validator): self
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * @return array
     */
    public function getDeleteImagesId(): array
    {
        return $this->deleteImagesId;
    }

    /**
     * @param array $deleteImagesId
     */
    public function setDeleteImagesId(array $deleteImagesId): self
    {
        $this->deleteImagesId = $deleteImagesId;
        return $this;
    }

    /**
     * @return UploadedFile|null|int
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param UploadedFile|null|int $logo
     */
    public function setLogo($logo): self
    {
        $this->logo = $logo;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getInn(): string
    {
        return $this->inn;
    }

    /**
     * @param string $inn
     */
    public function setInn(string $inn): self
    {
        $this->inn = $inn;
        return $this;
    }

    /**
     * @return string
     */
    public function getKpp(): string
    {
        return $this->kpp;
    }

    /**
     * @param string $kpp
     */
    public function setKpp(string $kpp): self
    {
        $this->kpp = $kpp;
        return $this;
    }

    /**
     * @return string
     */
    public function getSite(): string
    {
        return $this->site;
    }

    /**
     * @param string $site
     */
    public function setSite(string $site): self
    {
        $this->site = $site;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryTerm(): string
    {
        return $this->deliveryTerm;
    }

    /**
     * @param string $deliveryTerm
     */
    public function setDeliveryTerm(string $deliveryTerm): self
    {
        $this->deliveryTerm = $deliveryTerm;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentTerm(): string
    {
        return $this->paymentTerm;
    }

    /**
     * @param string $paymentTerm
     */
    public function setPaymentTerm(string $paymentTerm): self
    {
        $this->paymentTerm = $paymentTerm;
        return $this;
    }

    /**
     * @return string
     */
    public function getMinOrderAmount(): string
    {
        return $this->minOrderAmount;
    }

    /**
     * @param string $minOrderAmount
     */
    public function setMinOrderAmount(string $minOrderAmount): self
    {
        $this->minOrderAmount = $minOrderAmount;
        return $this;
    }

    /**
     * @return array
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @param array $images
     */
    public function setImages(array $images): self
    {
        $this->images = $images;
        return $this;
    }

    public function validate()
    {
        if (!$this->validator) {
            return;
        }

        $errors = new ParameterBag();

        $fields = get_object_vars($this);
        $constraints = $this->getConstraints();

        foreach ($fields as $key => $value) {
            $constraint = $constraints[$key] ?? null;

            if (!$constraint) {
                continue;
            }

            if ($key == 'logo' && is_numeric($value)) {
                continue;
            }

            $violations = $this->validator->validate($value, $constraint);

            if ($violations->count()) {
                $errors->set($key, $violations->get(0)->getMessage());
            }
        }

        return $errors;
    }

    protected function getConstraints()
    {
        return [
            'title' => [new NotBlank()],
            'inn' => [new NotBlank()],
            'site' => [new Url()],
            'images' => [new All([
                new Image([
                    'maxSize' => '5M',
                ])
            ])],
            'image' => [new Image([
                'maxSize' => '5M',
            ])],
        ];
    }

}
