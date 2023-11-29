<?php


namespace App\Controller\Api;


use App\Model\ProductImport;
use App\Service\Company\ActiveCompanyStorage;
use App\Service\DataObject\DataObjectBuilder;
use App\Service\ProductImport\ProductImportData;
use App\Service\ProductImport\ProductImportService;
use App\Service\Supplier\SupplierService;
use App\Validator\Constraints\NotBlank;
use Creonit\RestBundle\Annotation\Parameter\PathParameter;
use Creonit\RestBundle\Annotation\Parameter\RequestParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @Route("/productsImport")
 */
class ProductImportController extends AbstractController
{
    /**
     * Получить варианты сопоставления
     *
     * @Route("/mappingFields", methods={"GET"})
     */
    public function getMappingFields(RestHandler $handler, AuthorizationCheckerInterface $authorizationChecker)
    {
        $handler->checkAuthorization();
        $user = $this->getUser();
        $fields = ProductImport::$mappingFields;

        if ($authorizationChecker->isGranted('supplier', $user)) {
//            $fields = array_merge($fields, ProductImport::$mappingSupplierFields);
        }

        return $handler->response($fields);
    }

    /**
     * Первый шаг
     *
     * @RequestParameter("file", type="binary", description="FILE xls / xlsx")
     * @RequestParameter("categoryId", type="integer", description="ID категории")
     *
     * @Route("/parse", methods={"POST"})
     */
    public function parseFile(
        RestHandler $handler,
        ActiveCompanyStorage $companyStorage,
        SupplierService $supplierService,
        ProductImportService $productImportService
    )
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();

        $category = $supplierService->getProductCategory($request->request->get('categoryId'));

        if (!$category) {
            $handler->error->set('request/categoryId', 'Категория не найдена')->send();
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        if (!$file || !in_array($file->getClientOriginalExtension(), ['xls', 'xlsx'])) {
            $handler->error->set('request/file', 'Загрузите xls или xlsx файл')->send();
        }

        $productImport = $productImportService->initProductImport($companyStorage->getCompany(), $category);
        $productImportService->process($file);

        return $handler->response($productImport);
    }

    /**
     * Записать сопоставление
     *
     * @PathParameter("id", type="integer", description="ID импорта")
     * @RequestParameter("map", type="string", description="Массив полей сопоставления [0 => 'nomenclature_0', 1 => 'article_1']")
     *
     * @Route("/{id}/mapping", methods={"PUT"})
     */
    public function saveMapping(
        RestHandler $handler,
        ProductImportService $productImportService,
        $id
    )
    {
        $handler->checkAuthorization();
        $import = $productImportService->getProductImport($id);

        if (!$import) {
            $handler->error->notFound();
        }

        $handler->validate([
            'request' => [
                'map' => [new NotBlank(), new Type(['type' => 'array'])],
            ],
        ]);

//        $map = [
//            'nomenclature_1',
//            'article_3',
//            'barcode_5',
//            'unit_4'
//        ];

        $map = $handler->getRequest()->request->get('map', []);
        $productImportService->saveMapping($import, $map);

        return $handler->response($import);
    }

    /**
     * Импорт
     *
     * @PathParameter("id", type="integer", description="ID импорта")
     *
     * @RequestParameter("uniqId", type="string", description="Уникальный идентификатор")
     * @RequestParameter("insert", type="boolean", description="Создать новые элементы содержащиеся в файле")
     * @RequestParameter("deleteOther", type="boolean", description="Удалить остальные элементы, которые не присутствуют в данном файле в выбранной категории")
     * @RequestParameter("updateNomenclature", type="boolean", description="Обновить номенклатуру")
     * @RequestParameter("updateUnit", type="boolean", description="Обновить ед. изм.")
     * @RequestParameter("updateBarcode", type="boolean", description="Обновить штрихкод")
     *
     * @Route("/{id}", methods={"POST"})
     */
    public function processImport(
        RestHandler $handler,
        ProductImportService $productImportService,
        DataObjectBuilder $dataObjectBuilder,
        $id
    )
    {
        $handler->checkAuthorization();
        $request = $handler->getRequest();
        $import = $productImportService->getProductImport($id);

        $handler->validate([
            'request' => [
                'uniqId' => [new NotBlank(), new Choice(['choices' => ProductImportService::$uniqFields, 'message' => sprintf('Доступны для выбора %s', implode(',', ProductImportService::$uniqFields))])],
            ],
        ]);

        if (!$import) {
            $handler->error->notFound();
        }

        if (!$import->getMapping()) {
            $handler->error->send('Вы не выполнили сопоставление');
        }

        $data = new ProductImportData();
        $data
            ->setInsert($request->request->getBoolean('insert'))
            ->setDeleteOther($request->request->getBoolean('deleteOther'))
            ->setUpdateNomenclature($request->request->getBoolean('updateNomenclature'))
            ->setUpdateUnit($request->request->getBoolean('updateUnit'))
            ->setUpdateBarcode($request->request->getBoolean('updateBarcode'))
            ->setUniqId($request->request->get('uniqId'));

        try {
            $productImportService->processImport($import, $data);

        } catch (\Exception $exception) {
            $handler->error->send('При обработке файла произошла ошибка');
        }

        return $handler->response(['success' => true]);
    }

    /**
     * Получить информацию о файле
     *
     * @PathParameter("id", type="integer", description="ID импорта")
     *
     * @Route("/{id}", methods={"GET"})
     */
    public function getImport(RestHandler $handler, ProductImportService $productImportService, $id)
    {
        $handler->checkAuthorization();
        return $handler->response($productImportService->getProductImport($id));
    }
}
