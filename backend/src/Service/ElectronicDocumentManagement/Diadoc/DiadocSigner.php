<?php

declare(strict_types=1);

namespace App\Service\ElectronicDocumentManagement\Diadoc;

use AgentSIB\Diadoc\Model\SignerProviderInterface;

class DiadocSigner implements SignerProviderInterface
{
    public function encrypt($plainData)
    {
        // TODO: Implement encrypt() method.
    }

    public function decrypt($encryptedData)
    {
        // TODO: Implement decrypt() method.
    }

    public function sign($data)
    {
        // TODO: Implement sign() method.
    }

    public function checkSign($data, $sign)
    {
        // TODO: Implement checkSign() method.
    }
}
