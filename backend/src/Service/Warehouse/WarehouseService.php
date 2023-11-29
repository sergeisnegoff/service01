<?php
declare(strict_types=1);

namespace App\Service\Warehouse;

use App\Model\Company;
use App\Model\Warehouse;
use App\Model\WarehouseQuery;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\ListConfiguration\ListConfigurationService;
use Propel\Runtime\Map\TableMap;

class WarehouseService
{
    private ListConfigurationService $listConfigurationService;

    public function __construct(ListConfigurationService $listConfigurationService)
    {
        $this->listConfigurationService = $listConfigurationService;
    }

    public function getList(Company $company, ListConfiguration $configuration)
    {
        $query = WarehouseQuery::create()->filterByCompany($company);
        return $this->listConfigurationService->fetch($query, $configuration);
    }

    public function retrieve($id): ?Warehouse
    {
        $query = WarehouseQuery::create();

        if (is_numeric($id)) {
            return $query->findPk($id) ?? $query->findOneByCode($id);
        }

        return $query->findOneByCode($id);
    }

    public function create(Company $company, string $title, string $code = ''): Warehouse
    {
        $warehouse = new Warehouse();
        $warehouse
            ->setCompany($company)
            ->setTitle($title)
            ->setExternalCode($code)
            ->save();

        return $warehouse;
    }

    public function edit(Warehouse $warehouse, string $title, string $code = ''): Warehouse
    {
        $warehouse->setTitle($title);

        if ($code) {
            $warehouse->setExternalCode($code);
        }

        $warehouse->save();

        return $warehouse;
    }

    public function getWarehousesFromIikoImport(Company $company): array
    {
        return WarehouseQuery::create()
            ->filterByCompany($company)
            ->find()
            ->toKeyIndex('ExternalCode');
    }

    public function fillFromArray(Warehouse $warehouse, array $data, $keyType = TableMap::TYPE_PHPNAME): Warehouse
    {
        $warehouse->fromArray($data, $keyType);

        return $warehouse;
    }

    public function getWarehouseByCode($code): ?Warehouse
    {
        return WarehouseQuery::create()->findOneByExternalCode($code);
    }

    public function createWarehouses(Company $company, array $warehouses): array
    {
        $result = [];

        foreach ($warehouses as $warehouse) {
            $code = $warehouse['cod'] ?? '';
            $title = $warehouse['title'] ?? '';

            if (!$title) {
                $result[] = [
                    'cod' => $code,
                    'message' => 'Заполните название',
                ];
                continue;
            }

            $existWarehouse = $this->getWarehouseByCode($code);

            if (!$existWarehouse) {
                $existWarehouse = $this->create($company, $title, $code);

            } else {
                $this->edit($existWarehouse, $title);
            }

            $result[] = $existWarehouse;
        }

        return $result;
    }
}
