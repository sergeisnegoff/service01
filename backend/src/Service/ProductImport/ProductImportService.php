<?php


namespace App\Service\ProductImport;


use App\Model\Company;
use App\Model\Map\ProductTableMap;
use App\Model\Product;
use App\Model\ProductCategory;
use App\Model\ProductImport;
use App\Model\ProductImportField;
use App\Model\ProductImportQuery;
use App\Model\ProductQuery;
use App\Service\ExcelImport\AbstractExcelImport;
use App\Service\Product\ProductService;
use App\Service\Supplier\SupplierService;
use App\Service\Unit\UnitService;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Propel;

class ProductImportService extends AbstractExcelImport
{
    const MAX_CONNECTIONS = 300;

    public static array $uniqFields = [
        'nomenclature',
        'article',
        'barcode',
    ];

    public static array $updateFields = [
        'nomenclature',
        'unit',
        'barcode',
    ];

    /**
     * @var UnitService
     */
    private UnitService $unitService;
    /**
     * @var SupplierService
     */
    private SupplierService $supplierService;
    /**
     * @var ProductService
     */
    private ProductService $productService;

    public function __construct(
        UnitService $unitService,
        SupplierService $supplierService,
        ProductService $productService
    )
    {
        $this->unitService = $unitService;
        $this->supplierService = $supplierService;
        $this->productService = $productService;
    }

    protected ?ProductImport $productImport = null;

    public function initProductImport(Company $company, ProductCategory $category): ProductImport
    {
        if (is_null($this->productImport)) {
            $productImport = new ProductImport();
            $productImport
                ->setCompany($company)
                ->setProductCategory($category)
                ->save();

            $this->productImport = $productImport;
        }

        return $this->productImport;
    }

    protected function processRow(Worksheet $worksheet, int $rowNumber): void
    {
        if (is_null($this->productImport)) {
            throw new \Exception('Before calling the function, do initProductImport()');
        }

        $isHeader = $rowNumber === 1;
        $columns = $this->getColumns($worksheet);

        foreach ($columns as $column) {
            $importField = new ProductImportField();
            $importField
                ->setProductImport($this->productImport)
                ->setValue($worksheet->getCellByColumnAndRow($column, $rowNumber)->getValue())
                ->setRow($rowNumber)
                ->setCol($column)
                ->setHeader($isHeader)
                ->save();

            $this->incrementCountRequests();
        }
    }

    public function getProductImport($id): ?ProductImport
    {
        return ProductImportQuery::create()->findPk($id);
    }

    public function saveMapping(ProductImport $import, array $mapping): ProductImport
    {
        $import
            ->setMapping($mapping)
            ->save();

        return $import;
    }

    protected function findProduct(Company $company, $field, $value): ?Product
    {
        $tableMap = ProductTableMap::getTableMap();
        $phpNameField = ucfirst($field);

        if (!$tableMap->hasColumnByPhpName($phpNameField)) {
            return null;
        }

        return ProductQuery::create()->filterByCompany($company)->findOneBy($phpNameField, $value);
    }

    protected function makeProduct(ProductImport $import): Product
    {
        return (new Product())
            ->setCompany($import->getCompany())
            ->setProductCategory($import->getProductCategory())
        ;
    }

    public function processImport(ProductImport $import, ProductImportData $data): void
    {
        $connection = Propel::getConnection();

        $rows = $import->getRows();
        $uniqId = $data->getUniqId();

        $i = 1;

        $importedProducts = [];

        foreach ($rows as $row) {
            try {
                if (!$connection->inTransaction()) {
                    $connection->beginTransaction();
                }

                $uniqField = $this->getUniqField($uniqId, $row, $import);

                if (!$uniqField) {
                    continue;
                }

                $importedProducts[] = $this->processProductRow($import, $data, $uniqField, $row);

                if ($i >= self::MAX_CONNECTIONS || $i == count($rows)) {
                    $connection->commit();
                }

                $i++;

            } catch (\Exception $exception) {
                if ($connection->inTransaction()) {
                    $connection->rollBack();
                }

                throw $exception;
            }
        }

        if ($data->isDeleteOther() && array_filter($importedProducts)) {
            ProductQuery::create()
                ->filterByCompany($import->getCompany())
                ->filterByProductCategory($import->getProductCategory())
                ->filterById(array_map(fn(Product $product) => $product->getId(), array_filter($importedProducts)), Criteria::NOT_IN)
                ->delete();
        }
    }

    protected function processProductRow(ProductImport $import, ProductImportData $data, array $uniqData, array $row): ?Product
    {
        $product = null;

        if (!$data->isInsert()) {
            $product = $this->findProduct($import->getCompany(), $uniqData['field'], $uniqData['value']);
        }

        $mapping = array_filter($import->getMapping());
        $updateFields = $data->getUpdateFields();
        $mappingFunctions = $this->getMappingFunctions($import);

        foreach ($mapping as $key) {
            $explodedKey = explode('_', $key);

            $code = $explodedKey[0];
            $col = $explodedKey[1];

            $value = $row[$col - 1] ?? null;
            $functions = $mappingFunctions[$code] ?? null;

            if (!$functions) {
                return null;
            }

            if (!$product && ($data->isInsert() || $data->isDeleteOther())) {
                $product = $this->makeProduct($import);
            }

            $setter = $functions['setter'];
            $finder = $functions['finder'] ?? null;

            if ($finder) {
                $value = call_user_func($finder, $value);
            }

            if (!$value) {
                return null;
            }

            if ($data->isInsert() || $data->isDeleteOther()) {
                call_user_func([$product, $setter], $value);

            } else if (in_array($code, $updateFields)) {
                call_user_func([$product, $setter], $value);
            }
        }

        if ($product) {
            $product->save();
        }

        return $product;
    }

    protected function getUniqField(string $uniqId, array $row, ProductImport $import): ?array
    {
        foreach ($import->getMapping() as $key) {
            $mapKeyParams = $this->getMapKeyParams($key);

            if ($mapKeyParams['code'] === $uniqId) {
                return [
                    'field' => $uniqId,
                    'value' => $row[$mapKeyParams['col'] - 1],
                ];
            }
        }

        return null;
    }

    protected function getMapKeyParams(string $key): array
    {
        $explodedKey = explode('_', $key);

        return [
            'code' => $explodedKey[0],
            'col' => $explodedKey[1],
        ];
    }

    protected function getMappingFunctions(ProductImport $import): array
    {
        $functions = [
            'nomenclature' => [
                'setter' => 'setNomenclature',
            ],
            'unit' => [
                'setter' => 'setUnit',
                'finder' => [$this->unitService, 'findUnit'],
            ],
            'article' => [
                'setter' => 'setArticle',
            ],
            'barcode' => [
                'setter' => 'setBarcode',
            ],
            'vat' => [
                'setter' => 'setVat',
            ],
//            'manufacturer' => [
//                'setter' => 'setProductManufacturer',
//                'finder' => [$this->supplierService, 'findUnit'],
//            ],
//            'brand' => [
//                'setter' => 'setProductBrand',
//                'finder' => [$this->supplierService, 'findUnit'],
//            ],
        ];

        return $functions;
    }
}
