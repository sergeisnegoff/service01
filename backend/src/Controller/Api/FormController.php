<?php


namespace App\Controller\Api;


use App\Service\Form\FormService;
use Creonit\RestBundle\Annotation\Parameter\PathParameter;
use Creonit\RestBundle\Annotation\Parameter\RequestParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/forms")
 */
class FormController extends AbstractController
{
    /**
     * Получить темы обращений
     *
     * @Route("/subject/reports", methods={"GET"})
     */
    public function getFormSubjects(RestHandler $handler, FormService $formService)
    {
        return $handler->response($formService->getFormReportSubjects());
    }

    /**
     * Сохранить заполненную форму обратной связи
     *
     * @PathParameter("id", type="string", description="Идентификатор формы обратной связи")
     * @RequestParameter("field_1", type="string", description="Поля формы")
     *
     * @Route("/{id}", methods={"POST"})
     */
    public function fillForms(RestHandler $handler, FormService $formService, $id)
    {
        $request = $handler->getRequest();

        if (!$request->isXmlHttpRequest()) {
            $handler->error->send('Форма не найдена', 1, 404);
        }

        $handler->checkFound($form = $formService->getForm($id));

        $data = array_merge($request->request->all(), $request->files->all());
        $data['url'] = $request->headers->get('referer');

        foreach ($form->getFields() as $field) {
            $constraints = $formService->getConstraints($field);

            if ($constraints) {
                if (array_key_exists($field->getFieldName(), $data)) {
                    $value = $data[$field->getFieldName()];
                } else {
                    $value = '';
                }
                if (!$handler->isValid($value, $constraints)) {
                    $handler->error->set('request/' . $field->getFieldName(), 'Ошибка заполнения');
                }
            }
        }
        $handler->error->send();

        $result = $formService->saveResult($form, $data);

        $handler->data->set(['success_text' => $form->getSuccessText()]);

        return $handler->response();
    }
}
