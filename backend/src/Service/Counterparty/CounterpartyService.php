<?php
declare(strict_types=1);

namespace App\Service\Counterparty;

use App\Model\Company;
use App\Model\Counterparty;
use App\Model\CounterpartyQuery;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\ListConfiguration\ListConfigurationService;
use Propel\Runtime\Map\TableMap;

class CounterpartyService
{
    private ListConfigurationService $listConfigurationService;

    public function __construct(ListConfigurationService $listConfigurationService)
    {
        $this->listConfigurationService = $listConfigurationService;
    }

    public function getList(Company $company, ListConfiguration $configuration)
    {
        $query = CounterpartyQuery::create()->filterByCompany($company);
        return $this->listConfigurationService->fetch($query, $configuration);
    }

    public function retrieve($id): ?Counterparty
    {
        $query = CounterpartyQuery::create();

        if (is_numeric($id)) {
            return $query->findPk($id) ?? $query->findOneByCode($id);
        }

        return $query->findOneByCode($id);
    }

    public function create(Company $company, string $title, string $code = ''): Counterparty
    {
        $counterparty = new Counterparty();
        $counterparty
            ->setCompany($company)
            ->setTitle($title)
            ->setExternalCode($code)
            ->save();

        return $counterparty;
    }

    public function edit(Counterparty $counterparty, string $title, string $code = ''): Counterparty
    {
        $counterparty->setTitle($title);

        if ($code) {
            $counterparty->setExternalCode($code);
        }

        $counterparty->save();

        return $counterparty;
    }

    public function getCounterpartiesFromIikoImport(Company $company): array
    {
        return CounterpartyQuery::create()
            ->filterByCompany($company)
            ->find()
            ->toKeyIndex('ExternalCode');
    }

    public function fillFromArray(Counterparty $counterparty, array $data, $keyType = TableMap::TYPE_PHPNAME): Counterparty
    {
        $counterparty->fromArray($data, $keyType);

        return $counterparty;
    }

    public function getCounterpartyByCode($code): ?Counterparty
    {
        return CounterpartyQuery::create()->findOneByExternalCode($code);
    }

    public function createCounterparties(Company $company, array $counterparties): array
    {
        $result = [];

        foreach ($counterparties as $counterparty) {
            $code = $counterparty['cod'] ?? '';
            $title = $counterparty['title'] ?? '';

            if (!$title) {
                $result[] = [
                    'cod' => $code,
                    'message' => 'Заполните название',
                ];
                continue;
            }

            $existCounterparty = $this->getCounterpartyByCode($code);

            if (!$existCounterparty) {
                $existCounterparty = $this->create($company, $title, $code);

            } else {
                $this->edit($existCounterparty, $title);
            }

            $result[] = $existCounterparty;
        }

        return $result;
    }
}
