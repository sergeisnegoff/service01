<?php


namespace App\Controller\Api;


use App\Model\Invoice;
use App\Normalizer\ProductCategoryNormalizer;
use App\Normalizer\ProductNormalizer;
use App\Service\Company\ActiveCompanyStorage;
use App\Service\Company\CompanyRepository;
use App\Service\Company\CompanyService;
use App\Service\DataObject\DataObjectBuilder;
use App\Service\ListConfiguration\ListConfiguration;
use App\Service\Product\Exception\ProductDataFillException;
use App\Service\Product\ProductData\ProductData;
use App\Service\Product\ProductList\ProductListContext;
use App\Service\Product\ProductService;
use App\Service\Supplier\SupplierService;
use App\Validator\Constraints\NotBlank;
use Creonit\RestBundle\Annotation\Parameter\PathParameter;
use Creonit\RestBundle\Annotation\Parameter\QueryParameter;
use Creonit\RestBundle\Annotation\Parameter\RequestParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @Route("/products")
 */
class ProductController extends AbstractController
{
    /**
     * Список товаров
     *
     * @QueryParameter("search", type="string", description="Поиск")
     * @QueryParameter("companyId", type="integer", description="ID организации")
     * @QueryParameter("categoriesId", type="array", description="Массив ID категорий")
     *
     * @QueryParameter("sortField", type="string", description="Поле сортировки")
     * @QueryParameter("sortDirection", type="string", description="Направление сортировки")
     *
     * @QueryParameter("page", type="integer", description="Страница")
     * @QueryParameter("limit", type="integer", description="Лимит")
     *
     * @Route("", methods={"GET"})
     */
    public function getProducts(
        RestHandler $handler,
        ProductService $productService,
        ListConfiguration $configuration,
        CompanyRepository $companyRepository
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        if (!$company = $companyRepository->findPk($request->query->getInt('companyId'))) {
            $handler->error->send('Организация не найдена');
        }

        $context = new ProductListContext();
        $context->setSearch($request->query->get('search', ''));
        $context->setCompany($company);
        $context->setCategoriesId((array)$request->query->get('categoriesId', []));
        $context->setSortField($request->query->get('sortField', ''));
        $context->setSortDirection($request->query->get('sortDirection', ''));

        return $handler->response($productService->getProductList($context, $configuration));
    }

    /**
     * @Route("/delete", methods={"POST"})
     */
    public function deleteFilterProducts(RestHandler $handler, ProductService $productService, ActiveCompanyStorage $companyStorage)
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $filter = $request->request->get('filters');
        $productService->deleteProducts($companyStorage->getCompany(), $filter);

        return $handler->response();
    }

    /**
     * Список категорий
     *
     * @QueryParameter("companyId", type="integer", description="ID организации")
     *
     * @Route("/categories", methods={"GET"})
     */
    public function getProductCategories(
        RestHandler $handler,
        SupplierService $supplierService,
        CompanyRepository $companyRepository
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        if (!$company = $companyRepository->findPk($request->query->getInt('companyId'))) {
            $handler->error->send('Организация не найдена');
        }

        return $handler->response($supplierService->getProductCategoryList($company));
    }

    /**
     * Добавить товар
     *
     * @RequestParameter("brandId", type="string", description="ID бренда")
     * @RequestParameter("unitId", type="string", description="ID единицы измерения")
     * @RequestParameter("manufacturerId", type="string", description="ID производителя")
     * @RequestParameter("categoryId", type="string", description="ID категории")
     * @RequestParameter("nomenclature", type="string", description="Номенклатура")
     * @RequestParameter("code", type="string", description="Внешний код")
     * @RequestParameter("article", type="string", description="Артикул")
     * @RequestParameter("price", type="string", description="Цена")
     * @RequestParameter("barcode", type="string", description="Штрихкод")
     * @RequestParameter("quant", type="string", description="Квант")
     * @RequestParameter("vat", type="string", description="НДС")
     *
     * @RequestParameter("findByCode", type="boolean", description="Поиск по внешнему коду")
     *
     * @Route("", methods={"POST"})
     */
    public function createProducts(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        ProductService $productService,
        DataObjectBuilder $dataObjectBuilder
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $company = $companyStorage->getCompany();

        $products = $request->request->get('products', []);

        if (!$products) {
            $request->request->set('price', (float) $request->request->get('price'));

            if (!$request->request->get('categoryId')) {
                $request->request->set('categoryId', $productService->getProductFallbackCategory($company)->getId());
            }

            $productService->validateProduct($handler, $request->request);

            /** @var ProductData $data */
            $data = $dataObjectBuilder->build(ProductData::class, $request->request->all());
            $data->setCompany($company);

            try {
                $data->fillEntities($request->request);

            } catch (ProductDataFillException $exception) {
                $handler->error->set(sprintf('request/%s', $exception->getField()), $exception->getMessage())->send();
            }

            $product = $request->request->get('code') ? $productService->getProductByCode($company, $request->request->get('code')) : null;

            if (!$product) {
                $product = $productService->createProduct($data);

            } else {
                $productService->editProduct($product, $data);
            }

        } else {
            try {
                $handler->data->addGroup(ProductNormalizer::GROUP_MASS_ADDITION);
                $product = $productService->createProducts($company, $products);

            } catch (ProductDataFillException $exception) {
                $handler->error->set(sprintf('request/%s', $exception->getField()), $exception->getMessage())->send();
            }
        }

        return $handler->response($product);
    }

    /**
     * Список категорий
     *
     * @Route("/self/categories", methods={"GET"})
     */
    public function getSelfProductCategories(
        RestHandler $handler,
        SupplierService $supplierService,
        ActiveCompanyStorage $companyStorage
    ) {
        $handler->checkAuthorization();

        return $handler->response($supplierService->getProductCategoryList($companyStorage->getCompany()));
    }

    /**
     * Добавить категорию
     *
     * @RequestParameter("parentId", type="string", description="ID родительской категории")
     * @RequestParameter("title", type="string", description="Название")
     * @RequestParameter("code", type="string", description="Внешний код")
     *
     * @RequestParameter("categories", type="array", description="Массив категорий")
     *
     * @Route("/self/categories", methods={"POST"})
     */
    public function createSelfProductCategories(
        RestHandler $handler,
        SupplierService $supplierService,
        ActiveCompanyStorage $companyStorage
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $categories = $request->request->get('categories');

        if (!$categories) {
            $handler->validate([
                'request' => [
                    'title' => [new NotBlank()],
                ],
            ]);

            $title = $request->request->get('title', '');
            $code = $request->request->get('code', '');
            $parent = $supplierService->getProductCategory($request->request->get('parentId'));
            $category = $supplierService->getProductCategory($code);

            if (!$category) {
                $category = $supplierService->createProductCategory($title, $companyStorage->getCompany(), $code, $parent);

            } else {
                $supplierService->editProductCategory($category, $title, $code);
            }
        } else {
            $handler->data->addGroup(ProductCategoryNormalizer::GROUP_MASS_ADDITION);
            $category = $supplierService->createProductCategories($companyStorage->getCompany(), $categories);
        }

        return $handler->response($category);
    }

    /**
     * Изменить категорию
     *
     * @PathParameter("id", type="integer", description="ID")
     * @RequestParameter("title", type="string", description="Название")
     *
     * @Route("/self/categories/{id}", methods={"PUT"})
     */
    public function editSelfProductCategories(
        RestHandler $handler,
        SupplierService $supplierService,
        $id
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $user = $this->getUser();

        $category = $supplierService->getProductCategory($id);

        if (!$category || !$category->isEqualOwner($user)) {
            $handler->error->notFound();
        }

        $handler->validate([
            'request' => [
                'title' => [new NotBlank()],
            ],
        ]);

        return $handler->response(
            $supplierService->editProductCategory($category, $request->request->get('title', ''))
        );
    }

    /**
     * Удалить категорию
     *
     * @PathParameter("id", type="integer", description="ID")
     *
     * @Route("/self/categories/{id}", methods={"DELETE"})
     */
    public function deleteSelfProductCategories(
        RestHandler $handler,
        SupplierService $supplierService,
        $id
    ) {
        $handler->checkAuthorization();
        $user = $this->getUser();

        $category = $supplierService->getProductCategory($id);

        if (!$category || !$category->isEqualOwner($user)) {
            $handler->error->notFound();
        }

        $supplierService->deleteProductCategory($category);

        return $handler->response(['success' => true]);
    }

    /**
     * Список брендов
     *
     * @QueryParameter("sort", type="string", description="Поле_порядок (title_asc / title_desc)")
     * @QueryParameter("page", type="integer", description="Страница")
     * @QueryParameter("limit", type="integer", description="Лимит")
     *
     * @Route("/self/brands", methods={"GET"})
     */
    public function getSelfProductBrands(
        RestHandler $handler,
        SupplierService $supplierService,
        ActiveCompanyStorage $companyStorage,
        ListConfiguration $listConfiguration
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        return $handler->response(
            $supplierService->getProductBrandList(
                $companyStorage->getCompany(),
                $listConfiguration,
                $request->query->get('sort', '')
            )
        );
    }

    /**
     * Добавить бренд
     *
     * @RequestParameter("title", type="string", description="Название")
     * @RequestParameter("cod", type="string", description="Внешний код")
     * @RequestParameter("id", type="string", description="ID бренда")
     *
     * @RequestParameter("brands", type="array", description="Массив брендов")
     *
     * @Route("/self/brands", methods={"POST"})
     */
    public function createSelfProductBrands(
        RestHandler $handler,
        SupplierService $supplierService,
        ActiveCompanyStorage $companyStorage
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $brands = $request->request->get('brands', []);

        if (!$brands) {
            $handler->validate([
                'request' => [
                    'title' => [new NotBlank()],
                ],
            ]);

            $title = $request->request->get('title', '');
            $code = $request->request->get('code', '');
            $brand = $code ? $supplierService->getProductBrand($code) : null;

            if (!$brand) {
                $brand = $supplierService->createProductBrand($companyStorage->getCompany(), $title, $code);

            } else {
                $supplierService->editProductBrand($brand, $title, $code);
            }

        } else {
            $brand = $supplierService->createProductBrands($companyStorage->getCompany(), $brands);
        }

        return $handler->response($brand);
    }

    /**
     * Изменить бренд
     *
     * @PathParameter("id", type="integer", description="ID бренда")
     * @RequestParameter("title", type="string", description="Название")
     *
     * @Route("/self/brands/{id}", methods={"PUT"})
     */
    public function editSelfProductBrands(
        RestHandler $handler,
        SupplierService $supplierService,
        $id
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $user = $this->getUser();

        $brand = $supplierService->getProductBrand($id);

        if (!$brand || !$brand->isEqualOwner($user)) {
            $handler->error->notFound();
        }

        $handler->validate([
            'request' => [
                'title' => [new NotBlank()],
            ],
        ]);

        return $handler->response($supplierService->editProductBrand($brand, $request->request->get('title', '')));
    }

    /**
     * Удалить бренд
     *
     * @PathParameter("id", type="integer", description="ID бренда")
     *
     * @Route("/self/brands/{id}", methods={"DELETE"})
     */
    public function deleteSelfProductBrands(
        RestHandler $handler,
        SupplierService $supplierService,
        $id
    ) {
        $handler->checkAuthorization();
        $user = $this->getUser();

        $brand = $supplierService->getProductBrand($id);

        if (!$brand || !$brand->isEqualOwner($user)) {
            $handler->error->notFound();
        }

        $supplierService->deleteProductBrand($brand);

        return $handler->response(['success' => true]);
    }

    /**
     * Список своих производителей
     *
     * @QueryParameter("sort", type="string", description="Поле_порядок (title_asc / title_desc)")
     * @QueryParameter("page", type="integer", description="Страница")
     * @QueryParameter("limit", type="integer", description="Лимит")
     *
     * @Route("/self/manufacturers", methods={"GET"})
     */
    public function getSelfProductManufacturers(
        RestHandler $handler,
        SupplierService $supplierService,
        ActiveCompanyStorage $companyStorage,
        ListConfiguration $listConfiguration
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        return $handler->response(
            $supplierService->getProductManufacturerList(
                $companyStorage->getCompany(),
                $listConfiguration,
                $request->query->get('sort', '')
            )
        );
    }

    /**
     * Добавить производителя
     *
     * @RequestParameter("title", type="string", description="Название")
     * @RequestParameter("code", type="string", description="Внешний код")
     *
     * @RequestParameter("manufacturers", type="array", description="Массив производителей")
     *
     * @Route("/self/manufacturers", methods={"POST"})
     */
    public function createSelfProductManufacturers(
        RestHandler $handler,
        SupplierService $supplierService,
        ActiveCompanyStorage $companyStorage
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $manufacturers = $request->request->get('manufacturers');

        if (!$manufacturers) {
            $handler->validate([
                'request' => [
                    'title' => [new NotBlank()],
                ],
            ]);

            $title = $request->request->get('title', '');
            $code = $request->request->get('code', '');
            $manufacturer = $code ? $supplierService->getProductManufacturerByCode($code) : null;

            if (!$manufacturer) {
                $manufacturer = $supplierService->createProductManufacturer($companyStorage->getCompany(), $title, $code);

            } else {
                $supplierService->editProductManufacturer($manufacturer, $title, $code);
            }

        } else {
            $manufacturer = $supplierService->createProductManufacturers($companyStorage->getCompany(), $manufacturers);
        }

        return $handler->response($manufacturer);
    }

    /**
     * Изменить производителя
     *
     * @PathParameter("id", type="integer", description="ID производителя")
     * @RequestParameter("title", type="string", description="Название")
     *
     * @Route("/self/manufacturers/{id}", methods={"PUT"})
     */
    public function editSelfProductManufacturers(
        RestHandler $handler,
        SupplierService $supplierService,
        $id
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $user = $this->getUser();

        $manufacturer = $supplierService->getProductManufacturer($id);

        if (!$manufacturer || !$manufacturer->isEqualOwner($user)) {
            $handler->error->notFound();
        }

        $handler->validate([
            'request' => [
                'title' => [new NotBlank()],
            ],
        ]);

        return $handler->response($supplierService->editProductManufacturer($manufacturer,
            $request->request->get('title', '')));
    }

    /**
     * Удалить производителя
     *
     * @PathParameter("id", type="integer", description="ID производителя")
     *
     * @Route("/self/manufacturers/{id}", methods={"DELETE"})
     */
    public function deleteSelfProductManufacturers(
        RestHandler $handler,
        SupplierService $supplierService,
        $id
    ) {
        $handler->checkAuthorization();
        $user = $this->getUser();

        $manufacturer = $supplierService->getProductManufacturer($id);

        if (!$manufacturer || !$manufacturer->isEqualOwner($user)) {
            $handler->error->notFound();
        }

        $supplierService->deleteProductManufacturer($manufacturer);

        return $handler->response(['success' => true]);
    }

    /**
     * Изменить товар
     *
     * @PathParameter("id", type="integer", description="ID товара")
     *
     * @RequestParameter("list", type="boolean", description="Изменение в списке")
     * @RequestParameter("brandId", type="integer", description="ID бренда")
     * @RequestParameter("manufacturerId", type="integer", description="ID производителя")
     * @RequestParameter("categoryId", type="integer", description="ID категории")
     * @RequestParameter("nomenclature", type="integer", description="Номенклатура")
     * @RequestParameter("article", type="integer", description="Артикул")
     * @RequestParameter("price", type="integer", description="Цена")
     * @RequestParameter("barcode", type="integer", description="Штрихкод")
     * @RequestParameter("quant", type="integer", description="Квант")
     * @RequestParameter("vat", type="integer", description="НДС")
     *
     * @Route("/{id}", methods={"PUT"})
     */
    public function editProducts(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        ProductService $productService,
        DataObjectBuilder $dataObjectBuilder,
        $id
    ) {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $user = $this->getUser();

        $product = $productService->getProduct($id);

        if (!$product || !$product->isEqualOwner($user)) {
            $handler->error->notFound();
        }

        $company = $companyStorage->getCompany();
        $productService->validateProduct($handler, $request->request);

        /** @var ProductData $data */
        $data = $dataObjectBuilder->build(ProductData::class, $request->request->all());
        $data->setCompany($company);

        try {
            $data->fillEntities($request->request);

        } catch (ProductDataFillException $exception) {
            $handler->error->set(sprintf('request/%s', $exception->getField()), $exception->getMessage())->send();
        }

        return $handler->response(
            $productService->editProduct($product, $data)
        );
    }

    /**
     * Удалить товар
     *
     * @PathParameter("id", type="integer", description="ID товара")
     *
     * @Route("/{id}", methods={"DELETE"})
     */
    public function deleteProducts(
        RestHandler $handler,
        ProductService $productService,
        $id
    ) {
        $handler->checkAuthorization();
        $user = $this->getUser();
        $product = $productService->getProduct($id);

        if (!$product || !$product->isEqualOwner($user)) {
            $handler->error->notFound();
        }

        $productService->deleteProduct($product);

        return $handler->response(['success' => true]);
    }
}
