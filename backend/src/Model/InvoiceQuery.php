<?php

namespace App\Model;

use App\Model\Base\InvoiceQuery as BaseInvoiceQuery;
use Propel\Runtime\ActiveQuery\Criteria;

/**
 * Skeleton subclass for performing query and update operations on the 'invoice' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class InvoiceQuery extends BaseInvoiceQuery
{
    public function filterByPrice($price, $comparison = Criteria::GREATER_EQUAL)
    {
        return $this
            ->where("(
                SELECT SUM(ip.price * ip.quantity)
                FROM invoice_product ip
                WHERE ip.invoice_id = invoice.id
            ) {$comparison} {$price}");
    }
}
