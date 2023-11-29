<?php
declare(strict_types=1);

namespace App\Service\Mercury;

use App\Model\Map\VeterinaryDocumentTableMap;
use App\Model\VeterinaryDocument;
use App\Model\VeterinaryDocumentQuery;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\ListConfiguration\ListConfigurationService;
use App\Service\Mercury\MercuryDocumentList\MercuryDocumentListContext;
use Propel\Runtime\ActiveQuery\Criteria;

class MercuryDocumentListService
{
    private ListConfigurationService $listConfigurationService;

    public function __construct(ListConfigurationService $listConfigurationService)
    {
        $this->listConfigurationService = $listConfigurationService;
    }

    public function getList(MercuryDocumentListContext $context, ListConfiguration $configuration)
    {
        $query = VeterinaryDocumentQuery::create();

        if ($company = $context->getCompany()) {
            $query->filterByCompany($company);
        }

        if ($searchQuery = $context->getQuery()) {
            $query
                ->condition('c1', VeterinaryDocumentTableMap::COL_UUID . ' LIKE ?', "%{$searchQuery}%")
                ->condition('c2', VeterinaryDocumentTableMap::COL_PRODUCT_TITLE . ' LIKE ?', "%{$searchQuery}%")
                ->where(['c1', 'c2'], Criteria::LOGICAL_OR);
        }

        if ($status = $context->getStatus()) {
            $query->filterByStatus($status);
        }

        if ($sender = $context->getSender()) {
            $query->filterBySender($sender);
        }

        if ($issueDate = $context->getIssueDate()) {
            $dateFilter = [
                'min' => $issueDate,
                'max' => (clone $issueDate)->modify('next day - 1 second'),
            ];

            $query->filterByIssueDate($dateFilter);
        }

        if ($sortBy = $context->getSortBy()) {
            $tableMap = VeterinaryDocumentTableMap::getTableMap();

            if ($tableMap->hasColumnByPhpName(ucfirst($sortBy))) {
                $column = $tableMap->getColumnByPhpName(ucfirst($sortBy));
                $query->orderBy($column->getName(), $context->getSort());
            }
        }

        return $this->listConfigurationService->fetch($query, $configuration);
    }

    public function getListFilter(MercuryDocumentListContext $context): array
    {
        $query = VeterinaryDocumentQuery::create()->filterByCompany($context->getCompany());

        return [
            'sender' => $this->getSenders($query),
            'status' => VeterinaryDocument::$statusCaptions,
        ];
    }

    protected function getSenders(VeterinaryDocumentQuery $query): array
    {
        $clone = clone $query;
        return $clone->groupBySender()->find()->toKeyValue('Sender', 'Sender');
    }
}
