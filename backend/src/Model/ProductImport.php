<?php

namespace App\Model;

use App\Model\Base\ProductImport as BaseProductImport;

/**
 * Skeleton subclass for representing a row from the 'product_import' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class ProductImport extends BaseProductImport
{
    public static array $mappingFields = [
        'nomenclature' => 'Наименование',
        'unit' => 'Единица измерения',
        'article' => 'Код товара',
        'vat' => 'НДС',
//        'manufacturer' => 'Производитель',
//        'brand' => 'Бренд',
    ];

    public static array $mappingSupplierFields = [
        'brand' => 'Бренд',
        'manufacturer' => 'Производитель',
    ];

    public function getHeader(): array
    {
        return array_map(fn(ProductImportField $field) => $field->getValue(), $this->getFields(true)->getData());
    }

    public function getRows(?int $countRows = null): array
    {
        $fields = $this->getFields();
        $data = [];

        foreach ($fields as $field) {
            if ($countRows && count($data) > $countRows) {
                break;
            }

            $data[$field->getRow()][] = $field->getValue();
        }

        if ($countRows) {
            $item = array_pop($data);

            if ($item[0]) {
                $data[] = $item;
            }
        }

        foreach ($data as $key => $row) {
            $data[$key] = array_filter($row);
        }

        array_walk($data, function (&$item) {
            $item = array_filter($item);
        });

        return array_values(array_filter($data));
    }

    public function getFields(bool $isHeader = false, ?int $row = null, ?int $col = null)
    {
        $query = ProductImportFieldQuery::create()
            ->filterByProductImport($this)
            ->filterByHeader($isHeader)
            ->orderByRow()
            ->orderByCol();

        if ($row) {
            $query->filterByRow($row);
        }

        if ($col) {
            $query->filterByCol($col);
        }

        return $query->find();
    }
}
