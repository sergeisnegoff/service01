<?php


namespace App\Service\ProductExport;


use App\Service\ExcelExport\AbstractExcelExport;
use App\Service\Product\ProductService;
use Symfony\Component\Filesystem\Filesystem;

class ProductExportService extends AbstractExcelExport
{
    const
        NOMENCLATURE = 'nomenclature',
        UNIT = 'unit',
        ARTICLE = 'article',
        BARCODE = 'barcode';

    public static array $exportFieldsCaptions = [
        self::NOMENCLATURE => 'Наименование',
        self::UNIT => 'Ед. изм',
        self::ARTICLE => 'Артикул',
        self::BARCODE => 'Штрихкод',
    ];

    /**
     * @var ProductService
     */
    private ProductService $productService;

    public function __construct(Filesystem $filesystem, $kernelProjectDir, ProductService $productService)
    {
        parent::__construct($filesystem, $kernelProjectDir);
        $this->productService = $productService;
    }

    public function export(ProductExportData $productExportData)
    {
        $header = $this->buildHeader($productExportData);
        $body = $this->buildBody($productExportData);

        return '/export/' . $this->generate($header, $body);
    }

    protected function getFieldFunctions()
    {
        return [
            self::NOMENCLATURE => 'getNomenclature',
            self::UNIT => 'getUnitCaption',
            self::ARTICLE => 'getArticle',
            self::BARCODE => 'getBarcode',
        ];
    }

    protected function buildHeader(ProductExportData $data)
    {
        $header = [];
        $fields = self::$exportFieldsCaptions;

        foreach ($data->getFields() as $field) {
            $findField = $fields[$field] ?? null;

            if (!$findField) {
                continue;
            }

            $header[] = $findField;
        }

        return $header;
    }

    protected function buildBody(ProductExportData $productExportData)
    {
        $products = $this->productService->getProductsFromExport($productExportData->getCompany(), $productExportData->getProductsId(), $productExportData->isAll());
        $functions = $this->getFieldFunctions();

        $data = [];
        $i = 1;

        foreach ($products as $product) {
            foreach ($productExportData->getFields() as $field) {
                $function = $functions[$field] ?? null;

                if (!$function || !method_exists($product, $function)) {
                    continue;
                }

                $data[$i][] = call_user_func([$product, $function]);
            }

            $i++;
        }

        return $data;
    }
}
