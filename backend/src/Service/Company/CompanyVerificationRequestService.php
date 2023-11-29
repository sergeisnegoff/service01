<?php


namespace App\Service\Company;


use App\Model\Company;
use App\Model\CompanyVerificationRequest;
use App\Model\CompanyVerificationRequestQuery;
use App\Model\Map\CompanyTableMap;
use App\Service\Company\CompanyVerificationRequestList\CompanyVerificationRequestListContext;
use App\Service\Company\Event\AfterChangeStatusVerificationRequestEvent;
use App\Service\Company\Event\AfterCreateCompanyVerificationRequestEvent;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\ListConfiguration\ListConfigurationService;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CompanyVerificationRequestService
{
    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;
    /**
     * @var ListConfigurationService
     */
    private ListConfigurationService $listConfigurationService;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ListConfigurationService $listConfigurationService
    )
    {
        $this->dispatcher = $dispatcher;
        $this->listConfigurationService = $listConfigurationService;
    }

    protected array $validateBeforeRequestFields = [
        'title',
        'inn',
    ];

    public function findPk($id)
    {
        return CompanyVerificationRequestQuery::create()->findPk($id);
    }

    public function countNewVerificationRequest()
    {
        return CompanyVerificationRequestQuery::create()
            ->distinct()
            ->useCompanyQuery()
                ->filterByVisible(true)
            ->endUse()
            ->filterByStatus(CompanyVerificationRequest::STATUS_NEW)
            ->count();
    }

    public function validateCompanyBeforeRequest(Company $company)
    {
        $tableMap = CompanyTableMap::getTableMap();
        $emptyFields = [];

        foreach ($this->validateBeforeRequestFields as $validateBeforeRequestField) {
            $phpName = ucfirst($validateBeforeRequestField);

            if (!$tableMap->hasColumnByPhpName($phpName)) {
                continue;
            }

            if (!$company->getByName($phpName)) {
                $emptyFields[] = $validateBeforeRequestField;
            }
        }

        $gallery = $company->getGallery();

        if ($gallery && !$gallery->countGalleryItems()) {
            $emptyFields[] = 'gallery';
        }

        return $emptyFields;
    }

    public function getLastVerificationRequest(Company $company)
    {
        return CompanyVerificationRequestQuery::create()
            ->filterByCompany($company)
            ->orderByCreatedAt(Criteria::DESC)
            ->findOne();
    }

    public function getActiveVerificationRequest(Company $company)
    {
        return CompanyVerificationRequestQuery::create()
            ->filterByCompany($company)
            ->orderByCreatedAt(Criteria::DESC)
            ->filterByStatus([
                CompanyVerificationRequest::STATUS_VERIFIED,
                CompanyVerificationRequest::STATUS_FAILED,
            ], Criteria::NOT_IN)
            ->findOne();
    }

    public function createVerificationRequest(Company $company): CompanyVerificationRequest
    {
        if (!$request = $this->getLastVerificationRequest($company)) {
            $request = new CompanyVerificationRequest();
            $request->setCompany($company)->save();

        } else {
            $request
                ->setAnswer('')
                ->setStatus(CompanyVerificationRequest::STATUS_NEW)
                ->save();
        }

        $this->dispatcher->dispatch((new AfterCreateCompanyVerificationRequestEvent())->setRequest($request));

        return $request;
    }

    public function getVerificationRequestList(
        CompanyVerificationRequestListContext $context,
        ListConfiguration $configuration
    )
    {
        $query = CompanyVerificationRequestQuery::create()
            ->distinct()
            ->useCompanyQuery()
                ->filterByVisible(true)
            ->endUse();

        $status = CompanyVerificationRequest::convertStatusCode($context->getStatusCode());

        if ($status) {
            $query->filterByStatus($status);
        }

        if ($search = $context->getQuery()) {
            $query
                ->useCompanyQuery()
                    ->filterByTitle('%' . $search . '%', Criteria::LIKE)
                ->endUse()
                ->distinct();
        }

        return $this->listConfigurationService->fetch($query, $configuration);
    }

    public function changeVerificationRequestStatus(CompanyVerificationRequest $request, string $statusCode, string $answer = ''): CompanyVerificationRequest
    {
        $status = CompanyVerificationRequest::convertStatusCode($statusCode);
        $request
            ->setAnswer($answer)
            ->setStatus($status)
            ->save();

        $this->dispatcher->dispatch(
            (new AfterChangeStatusVerificationRequestEvent())->setRequest($request)
        );

        return $request;
    }

    public function canSendVerificationRequest(Company $company)
    {
        $companyVerificationRequest = $this->getLastVerificationRequest($company);

        return (!$companyVerificationRequest || !$companyVerificationRequest->isNewStatus()) &&
            in_array($company->getVerificationStatus(), [
                CompanyVerificationRequest::STATUS_NEW,
                CompanyVerificationRequest::STATUS_FAILED,
            ]);
    }
}
