<?php


namespace App\Service\Unit;


use App\Helper\MemoizationTrait;
use App\Model\Company;
use App\Model\Unit;
use App\Model\UnitQuery;
use Propel\Runtime\ActiveQuery\Criteria;

class UnitService
{
    use MemoizationTrait;

    public function getUnit($id): ?Unit
    {
        return UnitQuery::create()->findPk($id);
    }

    public function getUnitList(?Company $company = null)
    {
        $query = UnitQuery::create()
            ->filterByVisible(true)
            ->orderBySortableRank();

        if ($company) {
            $query
                ->filterByCompanyId($company->getId())->_or()
                ->filterByCompanyId(null, Criteria::ISNULL);
        }

        return $query->find();
    }

    public function findUnit($title): ?Unit
    {
        return $this->memoization($title, function () use ($title) {
            return UnitQuery::create()->findOneByTitle($title);
        });
    }

    public function getUnitsFromIikoImport(): array
    {
        return UnitQuery::create()
            ->filterByExternalCode('', Criteria::NOT_EQUAL)
            ->find()
            ->toKeyIndex('ExternalCode');
    }

    public function getUnitsFromStoreHouseImport(Company $company): array
    {
        return UnitQuery::create()
            ->filterByCompany($company)
            ->filterByExternalCode('', Criteria::NOT_EQUAL)
            ->filterByFromStoreHouse(true)
            ->find()
            ->toKeyIndex('Title');
    }

    public function create(string $title): Unit
    {
        $unit = new Unit();
        $unit->setTitle($title);
        $unit->save();

        return $unit;
    }
}
