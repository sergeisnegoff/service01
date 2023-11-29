<?php

declare(strict_types=1);

namespace App\Service\Mercury;

use App\Model\Company;
use App\Model\MercuryDoctor;
use App\Model\MercuryDoctorQuery;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\ListConfiguration\ListConfigurationService;

class MercuryDoctorService
{
    private ListConfigurationService $listConfigurationService;

    public function __construct(ListConfigurationService $listConfigurationService)
    {
        $this->listConfigurationService = $listConfigurationService;
    }

    public function retrieve($id): ?MercuryDoctor
    {
        return MercuryDoctorQuery::create()->findPk($id);
    }

    public function create(Company $company, string $externalCode, string $veterinaryEmail): MercuryDoctor
    {
        $doctor = (new MercuryDoctor())->setCompany($company);
        $this->edit($doctor, $externalCode, $veterinaryEmail);

        return $doctor;
    }

    public function edit(MercuryDoctor $doctor, string $externalCode, string $veterinaryEmail): MercuryDoctor
    {
        $doctor
            ->setExternalCode($externalCode)
            ->setVeterinaryEmail($veterinaryEmail)
            ->save();

        return $doctor;
    }

    public function delete(MercuryDoctor $doctor): void
    {
        $doctor->delete();
    }

    public function getList(Company $company, ListConfiguration $configuration)
    {
        $query = MercuryDoctorQuery::create()->filterByCompany($company);
        return $this->listConfigurationService->fetch($query, $configuration);
    }
}
