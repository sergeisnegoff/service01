<?php

declare(strict_types=1);

namespace App\Service\Invoice;

use App\Model\Company;
use App\Model\Invoice;
use App\Model\InvoiceExchange;
use App\Model\InvoicePack;
use App\Model\InvoicePackQuery;
use App\Model\Map\InvoicePackTableMap;
use Propel\Runtime\ActiveQuery\Criteria;

class InvoicePackService
{
    public const DEFAULT_NUMBER = 0;

    public function getInvoicePack(Invoice $invoice): ?InvoicePack
    {
        return InvoicePackQuery::create()->filterByInvoice($invoice)->findOne();
    }

    public function createPack(Invoice $invoice): InvoicePack
    {
        $pack = new InvoicePack();
        $pack
            ->setCompany($invoice->getCompanyRelatedByBuyerId())
            ->setInvoice($invoice)
            ->setNumber(self::DEFAULT_NUMBER)
            ->save();

        return $pack;
    }

    public function exchangePack(InvoiceExchange $exchange, InvoicePack $pack): InvoicePack
    {
        if ($pack->getNumber() !== self::DEFAULT_NUMBER) {
            return $pack;
        }

        $pack->setNumber($exchange->getNumber())->save();

        return $pack;
    }

    public function resetPack(InvoicePack $pack): InvoicePack
    {
        $pack->setNumber(self::DEFAULT_NUMBER)->save();
        return $pack;
    }

    public function clearPacks(Company $company, int $number): void
    {
        InvoicePackQuery::create()
            ->filterByCompany($company)
            ->filterByNumber(self::DEFAULT_NUMBER, Criteria::NOT_EQUAL)
            ->filterByNumber($number, Criteria::LESS_EQUAL)
            ->delete();
    }

    public function getInvoices(Company $company): array
    {
        $exchange = $this->createExchange($company);

        $query = InvoicePackQuery::create()->orderByNumber()->distinct();

        if ($company->isSupplierCompany()) {
            $query
                ->useInvoiceQuery()
                    ->filterByCompanyRelatedBySupplierId($company)
                ->endUse();

        } else {
            $query
                ->useInvoiceQuery()
                    ->filterByCompanyRelatedByBuyerId($company)
                ->endUse();
        }

        $packs = $query->find();

        $invoices = $packs->getColumnValues('Invoice');

        $packs = $packs->getData();

        array_walk($packs, fn(InvoicePack $pack) => $this->exchangePack($exchange, $pack));

        return [
            'numberMessage' => $exchange->getNumber(),
            'invoices' => $invoices,
        ];
    }

    private function createExchange(Company $company): InvoiceExchange
    {
        $exchange = new InvoiceExchange();
        $exchange->setCompany($company);
        $exchange->setNumber($company->countInvoiceExchanges());
        $exchange->save();

        return $exchange;
    }
}
