<?php


namespace App\Service\Supplier;


use App\Helper\MemoizationTrait;
use App\Model\Company;
use App\Model\CompanyQuery;
use App\Model\CompanyVerificationRequest;
use App\Model\Map\ProductBrandTableMap;
use App\Model\Map\ProductManufacturerTableMap;
use App\Model\ProductBrand;
use App\Model\ProductBrandQuery;
use App\Model\ProductCategory;
use App\Model\ProductCategoryQuery;
use App\Model\ProductManufacturer;
use App\Model\ProductManufacturerQuery;
use App\Model\UserGroup;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\ListConfiguration\ListConfigurationService;
use App\Service\Supplier\SupplierList\SupplierListContext;
use Propel\Runtime\ActiveQuery\Criteria;

class SupplierService
{
    use MemoizationTrait;

    /**
     * @var ListConfigurationService
     */
    private ListConfigurationService $listConfigurationService;

    public function __construct(ListConfigurationService $listConfigurationService)
    {
        $this->listConfigurationService = $listConfigurationService;
    }

    public function findSupplierByDiadocCode(string $code): ?Company
    {
        return $this->memoization($code, function () use ($code) {
            return CompanyQuery::create()->findOneByDiadocExternalCode($code);
        });
    }

    public function findSupplierByDocrobotCode(string $code): ?Company
    {
        return $this->memoization($code, function () use ($code) {
            return CompanyQuery::create()->findOneByDocrobotExternalCode($code);
        });
    }

    public function getSupplierList(SupplierListContext $context, ListConfiguration $configuration)
    {
        $query = CompanyQuery::create()
            ->filterByType(Company::TYPE_SUPPLIER)
            ->filterByVisible(true)
            ->filterByVerificationStatus(CompanyVerificationRequest::STATUS_VERIFIED)
            ->distinct();

        if ($search = $context->getQuery()) {
            $query->filterByTitle('%' . $search . '%', Criteria::LIKE);
        }

        if ($context->getCompany() && $context->isFavorite()) {
            $query
                ->useCompanyFavoriteRelatedByFavoriteIdQuery()
                ->filterByCompanyRelatedByCompanyId($context->getCompany())
                ->endUse()
                ->distinct();
        }

        if ($context->getCompany() && $context->isMySuppliers()) {
            $query
                ->useBuyerJobRequestRelatedBySupplierIdQuery()
                ->filterByCompanyRelatedByBuyerId($context->getCompany())
                ->endUse()
                ->distinct();
        }

        return $this->listConfigurationService->fetch($query, $configuration);
    }

    public function getProductBrandByCode(Company $company, $id): ?ProductBrand
    {
        return ProductBrandQuery::create()->filterByCompany($company)->findOneByExternalCode($id);
    }

    public function getProductBrandCompany(Company $company, $id)
    {
        return ProductBrandQuery::create()->filterByCompany($company)->findPk($id);
    }

    public function getProductBrand($id): ?ProductBrand
    {
        $query = ProductBrandQuery::create();

        if (is_numeric($id)) {
            return $query->findPk($id) ?? $query->findOneByExternalCode($id);
        }

        return $query->findOneByExternalCode($id);
    }

    public function createProductBrands(Company $company, array $brands): array
    {
        $data = [];

        foreach ($brands as $brand) {
            $code = $brand['cod'] ?? '';
            $title = $brand['title'] ?? '';
            $id = $brand['id'] ?? null;

            if ($error = $this->validateBrand($company, $brand)) {
                $data[] = $error;
                continue;
            }

            if ($code) {
                $existBrand = $this->getProductBrandByCode($company, $code);

            } else {
                $existBrand = $this->getProductBrandCompany($company, $id);
            }

            if (!$existBrand) {
                $data[] = $this->createProductBrand($company, $title, $code);

            } else {
                $this->editProductBrand($existBrand, $title, $code);
                $data[] = $existBrand;
            }
        }

        return $data;
    }

    public function createProductBrand(Company $company, string $title, string $code = ''): ProductBrand
    {
        $brand = new ProductBrand();
        $brand
            ->setCompany($company)
            ->setTitle($title)
            ->setExternalCode($code)
            ->save();

        return $brand;
    }

    public function editProductBrand(ProductBrand $brand, string $title, string $code = ''): ProductBrand
    {
        $brand
            ->setTitle($title)
            ->setExternalCode($code)
            ->save();

        return $brand;
    }

    public function deleteProductBrand(ProductBrand $brand)
    {
        $brand->delete();
    }

    public function getProductManufacturerByCode(Company $company, $id): ?ProductManufacturer
    {
        return ProductManufacturerQuery::create()->filterByCompany($company)->findOneByExternalCode($id);
    }

    public function getProductManufacturerCompany(Company $company, $id): ?ProductManufacturer
    {
        return ProductManufacturerQuery::create()->filterByCompany($company)->findPk($id);
    }

    public function getProductManufacturer($id): ?ProductManufacturer
    {
        $query = ProductManufacturerQuery::create();

        if (is_numeric($id)) {
            return $query->findPk($id);
        }

        return $query->findOneByExternalCode($id);
    }

    public function createProductManufacturers(Company $company, array $manufacturers): array
    {
        $data = [];

        foreach ($manufacturers as $manufacturer) {
            if ($error = $this->validateManufacturer($company, $manufacturer)) {
                $data[] = $error;
                continue;
            }

            $code = $manufacturer['cod'] ?? '';
            $id = $manufacturer['id'] ?? null;

            if ($code) {
                $existManufacturer = $this->getProductManufacturerByCode($company, $code);

            } else {
                $existManufacturer = $this->getProductManufacturerCompany($company, $id);
            }

            if (!$existManufacturer) {
                $data[] = $this->createProductManufacturer($company, $manufacturer['title'], $code);

            } else {
                $this->editProductManufacturer($existManufacturer, $manufacturer['title'], $code);
                $data[] = $existManufacturer;
            }
        }

        return $data;
    }

    public function createProductManufacturer(Company $company, string $title, string $code = ''): ProductManufacturer
    {
        $manufacturer = new ProductManufacturer();
        $manufacturer
            ->setCompany($company)
            ->setTitle($title)
            ->setExternalCode($code)
            ->save();

        return $manufacturer;
    }

    public function editProductManufacturer(ProductManufacturer $manufacturer, string $title, string $code = ''): ProductManufacturer
    {
        $manufacturer
            ->setTitle($title)
            ->setExternalCode($code)
            ->save();

        return $manufacturer;
    }

    public function deleteProductManufacturer(ProductManufacturer $manufacturer)
    {
        $manufacturer->delete();
    }

    public function getProductBrandList(Company $company, ListConfiguration $listConfiguration, string $sort = '')
    {
        $query = ProductBrandQuery::create()
            ->orderById(Criteria::DESC)
            ->filterByCompany($company);

        $explodedSort = explode('_', $sort);

        if ($explodedSort) {
            $tableMap = ProductBrandTableMap::getTableMap();

            if ($tableMap->hasColumnByPhpName(ucfirst($explodedSort[0]))) {
                $query->orderBy($explodedSort[0], $explodedSort[1] ?? 'ASC');
            }
        }

        if (!$listConfiguration->getPage() && !$listConfiguration->getLimit()) {
            return $query->find();
        }

        return $this->listConfigurationService->fetch($query, $listConfiguration);
    }

    public function getProductManufacturerList(Company $company, ListConfiguration $listConfiguration, string $sort = '')
    {
        $query = ProductManufacturerQuery::create()
            ->orderById(Criteria::DESC)
            ->filterByCompany($company);
        $explodedSort = explode('_', $sort);

        if ($explodedSort) {
            $tableMap = ProductManufacturerTableMap::getTableMap();

            if ($tableMap->hasColumnByPhpName(ucfirst($explodedSort[0]))) {
                $query->orderBy($explodedSort[0], $explodedSort[1] ?? 'ASC');
            }
        }

        return $this->listConfigurationService->fetch($query, $listConfiguration);
    }

    public function getProductCategoryList(Company $company)
    {
        $query = ProductCategoryQuery::create()->filterByParentId(null, Criteria::ISNULL)->filterByCompany($company);
        return $query->find();
    }

    public function getProductCategoryByCode(Company $company, string $code): ?ProductCategory
    {
        return ProductCategoryQuery::create()->filterByCompany($company)->findOneByExternalCode($code);
    }

    public function getProductCategoryCompany(Company $company, $id): ?ProductCategory
    {
        return ProductCategoryQuery::create()->filterByCompany($company)->findPk($id);
    }

    public function getProductCategory($id): ?ProductCategory
    {
        if (!$id) {
            return null;
        }

        $query = ProductCategoryQuery::create();

        if (is_numeric($id)) {
            return $query->findPk($id) ?? $query->findOneByExternalCode($id);
        }

        return $query->findOneByExternalCode($id);
    }

    public function createProductCategories(Company $company, array $categories): array
    {
        $data = [];

        foreach ($categories as $category) {
            if ($error = $this->validateCategory($company, $category)) {
                $data[] = $error;
                continue;
            }

            $code = $category['cod'] ?? '';
            $id = $category['id'] ?? null;

            if ($code) {
                $existCategory = $this->getProductCategoryByCode($company, $code);

            } else {
                $existCategory = $this->getProductCategoryCompany($company, $id);
            }

            $parentId = $category['parentId'] ?? null;
            $parentCode = $category['parentCod'] ?? null;
            $parent = null;

            if ($parentId) {
                $parent = $this->getProductCategoryCompany($company, $parentId);

            } else if ($parentCode) {
                $parent = $this->getProductCategoryByCode($company, $parentCode);
            }

            if (!$existCategory) {
                $productCategory = $this->createProductCategory($category['title'], $company, $code, $parent);

            } else {
                $productCategory = $this->editProductCategory($existCategory, $category['title'], $code, $parent);

            }

            $data[] = $productCategory;
        }

        return array_values($data);
    }

    public function createProductCategory(string $title, ?Company $company = null, string $code = '', ?ProductCategory $parent = null): ProductCategory
    {
        $category = new ProductCategory();
        $category
            ->setCompany($company)
            ->setTitle($title)
            ->setProductCategoryRelatedByParentId($parent)
            ->setExternalCode($code)
            ->save();

        return $category;
    }

    public function editProductCategory(ProductCategory $category, string $title, string $code = '', ?ProductCategory $parent = null): ProductCategory
    {
        $category
            ->setProductCategoryRelatedByParentId($parent)
            ->setTitle($title)
            ->setExternalCode($code)
            ->save();

        return $category;
    }

    public function getProductCategoriesFromIikoImport(Company $company): array
    {
        return ProductCategoryQuery::create()
            ->filterByExternalCode('', Criteria::NOT_EQUAL)
            ->filterByCompany($company)
            ->find()
            ->toKeyIndex('ExternalCode');
    }

    public function getProductCategoriesFromStoreHouseImport(Company $company): array
    {
        return ProductCategoryQuery::create()
            ->filterByExternalCode('', Criteria::NOT_EQUAL)
            ->filterByCompany($company)
            ->find()
            ->toKeyIndex('Title');
    }

    public function deleteProductCategory(ProductCategory $category)
    {
        $category->delete();
    }

    public function deleteBrands(Company $company, array $filters)
    {
        $ids = $filters['id'] ?? null;
        $codes = $filters['cod'] ?? null;

        $query = ProductBrandQuery::create()->filterByCompany($company);

        if ($ids) {
            $query->filterById($ids)->delete();
        }

        if ($codes) {
            $query->filterByExternalCode($codes)->delete();
        }
    }

    public function deleteProductCategories(Company $company, array $filters)
    {
        $ids = $filters['id'] ?? null;
        $codes = $filters['cod'] ?? null;

        $query = ProductCategoryQuery::create()->filterByCompany($company);

        if ($ids) {
            $query->filterById($ids)->delete();
        }

        if ($codes) {
            $query->filterByExternalCode($codes)->delete();
        }
    }

    public function deleteManufacturers(Company $company, array $filters)
    {
        $ids = $filters['id'] ?? null;
        $codes = $filters['cod'] ?? null;

        $query = ProductManufacturerQuery::create()->filterByCompany($company);

        if ($ids) {
            $query->filterById($ids)->delete();
        }

        if ($codes) {
            $query->filterByExternalCode($codes)->delete();
        }
    }

    private function validateBrand(Company $company, array $brand): ?array
    {
        $code = $brand['cod'] ?? null;
        $title = $brand['title'] ?? null;
        $id = $brand['id'] ?? null;

        $error = [
            'id' => $id,
            'cod' => $code,
        ];

        if (!$code && $id && !$this->getProductBrandCompany($company, $id)) {
            $error['message'] = 'Бренд не найден';
            return $error;
        }

        if (!$title) {
            $error['message'] = 'Укажите название';
            return $error;
        }

        return null;
    }

    private function validateManufacturer(Company $company, array $manufacturer): ?array
    {
        $code = $manufacturer['cod'] ?? null;
        $title = $manufacturer['title'] ?? null;
        $id = $manufacturer['id'] ?? null;

        $error = [
            'id' => $id,
            'cod' => $code,
        ];

        if (!$code && $id && !$this->getProductManufacturerCompany($company, $id)) {
            $error['message'] = 'Производитель не найден';
            return $error;
        }

        if (!$title) {
            $error['message'] = 'Укажите название';
            return $error;
        }

        return null;
    }

    private function validateCategory(Company $company, array $category): ?array
    {
        $code = $category['cod'] ?? null;
        $title = $category['title'] ?? null;
        $id = $category['id'] ?? null;
        $parentId = $category['parentId'] ?? null;
        $parentCode = $category['parentCod'] ?? null;

        $error = [
            'id' => $id,
            'cod' => $code,
        ];

        if (!$code && $id && !$this->getProductCategoryCompany($company, $id)) {
            $error['message'] = 'Категория не найдена';
            return $error;
        }

        if ($parentId && !$this->getProductCategoryCompany($company, $parentId)) {
            $error['message'] = sprintf('Родительская категория с ID %d не найдена', $parentId);
            return $error;
        }

        if ($parentCode && !$this->getProductCategoryByCode($company, $parentCode)) {
            $error['message'] = sprintf('Родительская категория с кодом %s не найдена', $parentCode);
            return $error;
        }

        if (!$title) {
            $error['message'] = 'Укажите название';
            return $error;
        }

        return null;
    }
}
