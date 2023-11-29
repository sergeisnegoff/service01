<?php

declare(strict_types=1);

namespace App\Service\ElectronicDocumentManagement\Docrobot;

class DocrobotClient extends AbstractDocrobotClient
{
    public function getTimeLine(string $mode = 'UPD'): array
    {
        $response = $this->post(self::METHOD_GET_TIME_LINE, [
            'mode' => $mode
        ]);

        return $this->normalizeResponseContent($response)['timeline'];
    }

    public function getEdiDocs(string $docType = 'Desadv'): array
    {
        $response = $this->post(self::METHOD_GET_EDI_DOCS, [
            'doc_type' => $docType,
        ]);

        return $this->normalizeResponseContent($response)['docs'];
    }

    public function getEdiDocBody(int $id): array
    {
        $response = $this->post(self::METHOD_GET_EDI_DOC_BODY, [
            'intDocID' => $id
        ]);

        return $this->normalizeResponseContent($response)['body'];
    }

    public function getBoth(string $id): array
    {
        $response = $this->post(self::METHOD_GET_BOTH, [
            'identifier' => $id,
        ]);

        return $this->normalizeResponseContent($response);
    }
}
