<?php

namespace App\Model;

use App\Model\Base\Notification as BaseNotification;

/**
 * Skeleton subclass for representing a row from the 'notification' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Notification extends BaseNotification
{
    const
        CODE_MODERATION_PASSED = 'moderationPassed',
        CODE_MODERATION_FAILED = 'moderationFailed',
        CODE_SUPPLIER_NEW_REQUEST = 'supplierNewRequest',
        CODE_NEW_ORGANIZATION_SHOP = 'newOrganizationShop',
        CODE_INVOICE_CANCEL = 'invoiceCancel',
        CODE_INVOICE_ACCEPT = 'invoiceAccept',
        CODE_INVOICE_NOT_COMPLETELY = 'invoiceNotCompletely',
        CODE_INVOICE_NEW = 'newInvoice',
        CODE_JOIN_COMPANY = 'joinCompany'
    ;
}
