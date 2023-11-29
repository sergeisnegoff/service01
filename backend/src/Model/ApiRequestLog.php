<?php

namespace App\Model;

use App\Model\Base\ApiRequestLog as BaseApiRequestLog;

/**
 * Skeleton subclass for representing a row from the 'api_request_log' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class ApiRequestLog extends BaseApiRequestLog
{
    const
        METHOD_GET = 'GET',
        METHOD_POST = 'POST';

    public static array $methods = [
        self::METHOD_GET,
        self::METHOD_POST,
    ];

    public function getRowClass(): string
    {
        if (in_array($this->status_code, [400, 401, 403, 404])) {
            return 'warning';

        } else if ($this->status_code === 500) {
            return 'danger';
        }

        return 'success';
    }

    public function getRequestData($normalize = false): string
    {
        if ($normalize) {
            return $this->normalizeData($this->request_data);
        }

        return parent::getRequestData();
    }

    public function getResponseData($normalize = false): string
    {
        if ($normalize) {
            return $this->normalizeData($this->response_data);
        }

        return parent::getResponseData();
    }

    protected function normalizeData(string $data): string
    {
        $items = json_decode($data, true);

        if (!$items) {
            return '';
        }

        $out = ['{'];

        foreach ($items as $code => $value) {
            if (is_array($value)) {
                $value = $this->normalizeData(json_encode($value));
            }

            $out[] = sprintf('%s => %s', $code, $value);
        }

        $out[] = '}';

        return implode('<br>', $out);
    }
}
