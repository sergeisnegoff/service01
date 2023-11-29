<?php


namespace App\Service\Product;


use App\Model\Company;
use App\Model\Invoice;
use App\Model\Map\ProductTableMap;
use App\Model\Product;
use App\Model\ProductCategory;
use App\Model\ProductCategoryQuery;
use App\Model\ProductQuery;
use App\Service\DataObject\DataObjectBuilder;
use App\Service\ElectronicDocumentManagement\Diadoc\DiadocImportService;
use App\Service\ElectronicDocumentManagement\Docrobot\DocrobotImportService;
use App\Service\Iiko\IikoImportService;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\ListConfiguration\ListConfigurationService;
use App\Service\Product\ProductData\ProductData;
use App\Service\Product\ProductList\ProductListContext;
use App\Service\StoreHouse\StoreHouseImportService;
use App\Service\Supplier\SupplierService;
use App\Service\Unit\UnitService;
use App\Validator\Constraints\NotBlank;

use Creonit\RestBundle\Handler\RestHandler;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Map\TableMap;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Type;

class ProductService
{
    public const DEFAULT_CATEGORY_TITLE = 'По умолчанию';

    /**
     * @var ListConfigurationService
     */
    private ListConfigurationService $listConfigurationService;
    private SupplierService $supplierService;
    private DataObjectBuilder $dataObjectBuilder;
    private UnitService $unitService;

    public function __construct(
        ListConfigurationService $listConfigurationService,
        SupplierService $supplierService,
        DataObjectBuilder $dataObjectBuilder,
        UnitService $unitService
    ) {
        $this->listConfigurationService = $listConfigurationService;
        $this->supplierService = $supplierService;
        $this->dataObjectBuilder = $dataObjectBuilder;
        $this->unitService = $unitService;
    }

    public function getProductsFromExport(Company $company, array $ids, bool $all = false): ObjectCollection
    {
        if (!$all) {
            return ProductQuery::create()
                ->filterById($ids)
                ->filterByCompany($company)
                ->find();
        }

        return ProductQuery::create()
            ->filterByCompany($company)
            ->filterById($ids, Criteria::NOT_IN)
            ->find();
    }

    public function getProductByCode(Company $company, $code): ?Product
    {
        return ProductQuery::create()->filterByCompany($company)->findOneByExternalCode($code);
    }

    public function getProduct($id): ?Product
    {
        return ProductQuery::create()->findPk($id);
    }

    public function getProductByCompany(Company $company, $id): ?Product
    {
        return ProductQuery::create()->filterByCompany($company)->findPk($id);
    }

    public function createProducts(Company $company, array $products): array
    {
        $data = [];

        foreach ($products as $product) {
            if ($error = $this->validateProductArray($company, $product)) {
                $data[] = $error;
                continue;
            }

            $id = $product['id'] ?? null;
            $code = $product['cod'] ?? '';
            $article = $product['article'] ?? '';
            $barcode = $product['barcode'] ?? '';
            $categoryId = $product['categoryId'] ?? null;
            $categoryCod = $product['categoryCod'] ?? null;
            $brandId = $product['brandId'] ?? null;
            $brandCod = $product['brandCod'] ?? null;
            $manufacturerId = $product['manufacturerId'] ?? null;
            $manufacturerCod = $product['manufacturerCod'] ?? null;

            $category = null;
            $brand = null;
            $manufacturer = null;

            $productData = new ProductData();
            $productData
                ->setArticle($article)
                ->setBarcode($barcode)
                ->setCompany($company)
                ->setNomenclature($product['title'])
                ->setVat($product['vat'] ?? 0);

            if ($categoryId) {
                $category = $this->supplierService->getProductCategoryCompany($company, $categoryId);

            } else if ($categoryCod) {
                $category = $this->supplierService->getProductCategoryByCode($company, $categoryCod);

            } else {
                $category = $this->getProductFallbackCategory($company);
            }

            if ($brandId) {
                $brand = $this->supplierService->getProductBrandCompany($company, $brandId);

            } else if ($brandCod) {
                $brand = $this->supplierService->getProductBrandByCode($company, $brandCod);
            }

            if ($manufacturerId) {
                $manufacturer = $this->supplierService->getProductManufacturerCompany($company, $manufacturerId);

            } else if ($manufacturerCod) {
                $manufacturer = $this->supplierService->getProductManufacturerByCode($company, $manufacturerCod);
            }

            if (!$category) {
                $productData->setCategory($this->getProductFallbackCategory($company));
            }

            $productData
                ->setCode($code)
                ->setUnit($this->unitService->getUnit($product['unitId'] ?? null))
                ->setCategory($category)
                ->setBrand($brand)
                ->setManufacturer($manufacturer);

            if ($code) {
                $existProduct = $this->getProductByCode($company, $code);

            } else {
                $existProduct = $this->getProductByCompany($company, $id);
            }

            if (!$existProduct) {
                $data[] = $this->createProduct($productData);
            } else {
                $data[] = $this->editProduct($existProduct, $productData);
            }
        }

        return $data;
    }

    public function createProduct(ProductData $productData): Product
    {
        $product = new Product();
        $product
            ->setCompany($productData->getCompany())
            ->setProductCategory($productData->getCategory())
            ->setProductBrand($productData->getBrand())
            ->setProductManufacturer($productData->getManufacturer())
            ->setNomenclature($productData->getNomenclature())
            ->setArticle($productData->getArticle())
            ->setPrice($productData->getPrice())
            ->setBarcode($productData->getBarcode())
            ->setQuant($productData->getQuant())
            ->setUnit($productData->getUnit())
            ->setVat($productData->getVat())
            ->setExternalCode($productData->getCode())
            ->save();

        return $product;
    }

    public function validateProduct(RestHandler $handler, ParameterBag $bag): void
    {
        $validateData = [
            'nomenclature' => [new NotBlank()],
            'categoryId' => [new NotBlank()],
        ];

        if ($bag->get('price')) {
            $validateData['price'] = [new Type(['type' => 'numeric'])];
        }

        if ($bag->get('vat')) {
            $validateData['vat'] = [new Choice(['choices' => Invoice::$vatVariants])];
        }

        $handler->validate(['request' => $validateData]);
    }

    public function editProduct(Product $product, ProductData $productData): Product
    {
        $product
            ->setNomenclature($productData->getNomenclature())
            ->setArticle($productData->getArticle())
            ->setPrice($productData->getPrice())
            ->setBarcode($productData->getBarcode())
            ->setQuant($productData->getQuant())
            ->setUnit($productData->getUnit())
            ->setVat($productData->getVat())
        ;

        if ($productData->getCategory()) {
            $product->setProductCategory($productData->getCategory());
        }

        if ($productData->getManufacturer()) {
            $product->setProductManufacturer($productData->getManufacturer());
        }

        if ($productData->getBrand()) {
            $product->setProductBrand($productData->getBrand());
        }

        $product->save();

        return $product;
    }

    public function deleteProduct(Product $product)
    {
        $product->delete();
    }

    public function getProductList(ProductListContext $context, ListConfiguration $configuration)
    {
        $query = ProductQuery::create()->filterByCompany($context->getCompany());

        if ($categoriesId = $context->getCategoriesId()) {
            $query
                ->distinct()
                ->filterByCategoryId($categoriesId)->_or()
                ->useProductCategoryQuery()
                    ->filterByParentId($categoriesId)
                ->endUse();
        }

        if ($search = $context->getSearch()) {
            $query
                ->filterByNomenclature("%{$search}%", Criteria::LIKE)
                ->_or()
                ->filterByArticle("%{$search}%", Criteria::LIKE)
            ;
        }

        if ($sortField = $context->getSortField()) {
            $tableMap = ProductTableMap::getTableMap();

            if ($tableMap->hasColumnByPhpName(ucfirst($sortField))) {
                $query->orderBy($tableMap->getColumnByPhpName(ucfirst($sortField))->getName(), $context->getSortDirection());

            } else if ($sortField === 'unit') {
                $query
                    ->useUnitQuery()
                        ->orderByTitle($context->getSortDirection())
                    ->endUse();
            }

        } else {
            $query->orderByNomenclature();
        }

        return $this->listConfigurationService->fetch($query, $configuration);
    }

    public function getProductsFromIikoImport(Company $company): array
    {
        return ProductQuery::create()
            ->filterByExternalCode('', Criteria::NOT_EQUAL)
            ->filterByCompany($company)
            ->find()
            ->toKeyIndex('ExternalCode');
    }

    public function fillFromArray(Product $product, array $data, $keyType = TableMap::TYPE_PHPNAME): Product
    {
        $product->fromArray($data, $keyType);

        return $product;
    }

    public function getProductCategoryFromDiadoc(): ProductCategory
    {
        $category = ProductCategoryQuery::create()->filterByTitle(DiadocImportService::CATEGORY_TITLE)->findOne();

        if (!$category) {
            $category = $this->supplierService->createProductCategory(DiadocImportService::CATEGORY_TITLE);
        }

        return $category;
    }

    public function getProductCategoryFromIiko(Company $company): ProductCategory
    {
        $category = ProductCategoryQuery::create()->filterByTitle(IikoImportService::CATEGORY_TITLE)->findOne();

        if (!$category) {
            $category = $this->supplierService->createProductCategory(IikoImportService::CATEGORY_TITLE, $company);
        }

        return $category;
    }

    public function getProductCategoryFromStoreHouse(Company $company): ProductCategory
    {
        $category = ProductCategoryQuery::create()->filterByTitle(StoreHouseImportService::CATEGORY_TITLE)->findOne();

        if (!$category) {
            $category = $this->supplierService->createProductCategory(StoreHouseImportService::CATEGORY_TITLE, $company);
        }

        return $category;
    }

    public function getProductCategoryFromDocrobot(): ProductCategory
    {
        $category = ProductCategoryQuery::create()->filterByTitle(DocrobotImportService::CATEGORY_TITLE)->findOne();

        if (!$category) {
            $category = $this->supplierService->createProductCategory(DocrobotImportService::CATEGORY_TITLE);
        }

        return $category;
    }

    public function getProductFallbackCategory(Company $company): ProductCategory
    {
        $category = ProductCategoryQuery::create()->filterByCompany($company)->filterByTitle(self::DEFAULT_CATEGORY_TITLE)->findOne();

        if (!$category) {
            $category = $this->supplierService->createProductCategory(self::DEFAULT_CATEGORY_TITLE, $company);
        }

        return $category;
    }

    public function getProductFromEdo(): array
    {
        return ProductQuery::create()
            ->filterByEdo(true)
            ->find()
            ->toKeyIndex('Article');
    }

    public function deleteProducts(Company $company, array $filters): void
    {
        $ids = $filters['id'] ?? null;
        $codes = $filters['cod'] ?? null;
        $categoryIds = $filters['categoryId'] ?? null;
        $categoryCodes = $filters['categoryCod'] ?? null;

        $productQuery = ProductQuery::create()->filterByCompany($company);

        if ($ids) {
            $productQuery->filterById($ids)->delete();
        }

        if ($codes) {
            $productQuery->filterByExternalCode($codes)->delete();
        }

        if ($categoryIds) {
            $products = $productQuery
                ->filterByCategoryId($categoryIds)->_or()
                ->useProductCategoryQuery()
                    ->filterByParentId($categoryIds)
                ->endUse()
                ->find();

            $products->delete();
        }

        if ($categoryCodes) {
            $products = $productQuery
                ->useProductCategoryQuery()
                    ->filterByExternalCode($categoryCodes)->_or()
                    ->useProductCategoryRelatedByParentIdQuery()
                        ->filterByExternalCode($categoryCodes)
                    ->endUse()
                ->endUse()
                ->find();

            $products->delete();
        }
    }

    private function validateProductArray(Company $company, array $product): ?array
    {
        $id = $product['id'] ?? null;
        $code = $product['cod'] ?? null;
        $title = $product['title'] ?? null;
        $categoryId = $product['categoryId'] ?? null;
        $categoryCod = $product['categoryCod'] ?? null;
        $brandId = $product['brandId'] ?? null;
        $brandCod = $product['brandCod'] ?? null;
        $manufacturerId = $product['manufacturerId'] ?? null;
        $manufacturerCod = $product['manufacturerCod'] ?? null;

        $error = [
            'id' => $id,
            'cod' => $code,
        ];

        if (!$code && $id && !$this->getProduct($id)) {
            $error['message'] = sprintf('Продукт c id %s не найден', $id);
            return $error;
        }

        if (!$title) {
            $error['message'] = 'Укажите название';
            return $error;
        }

        if ($categoryId && !$this->supplierService->getProductCategoryCompany($company, $categoryId)) {
            $error['message'] = sprintf('Категория с ID %d не найдена', $categoryId);
            return $error;

        } else if ($categoryCod && !$this->supplierService->getProductCategoryByCode($company, $categoryCod)) {
            $error['message'] = sprintf('Категория с кодом %s не найдена', $categoryCod);
            return $error;
        }

        if ($brandId && !$this->supplierService->getProductBrandCompany($company, $brandId)) {
            $error['message'] = sprintf('Бренд с ID %d не найден', $brandId);
            return $error;

        } else if ($brandCod && !$this->supplierService->getProductBrandByCode($company, $brandCod)) {
            $error['message'] = sprintf('Бренд с кодом %s не найден', $brandCod);
            return $error;
        }

        if ($manufacturerId && !$this->supplierService->getProductManufacturerCompany($company, $manufacturerId)) {
            $error['message'] = sprintf('Производитель с ID %d не найден', $manufacturerId);
            return $error;

        } else if ($manufacturerCod && !$this->supplierService->getProductManufacturerByCode($company, $manufacturerCod)) {
            $error['message'] = sprintf('Производитель с кодом %s не найден', $manufacturerCod);
            return $error;
        }

        return null;
    }
}
