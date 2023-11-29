<?php


namespace App\Controller\Api;


use App\Service\PageService;
use Creonit\RestBundle\Annotation\Parameter\PathParameter;
use Creonit\RestBundle\Annotation\Parameter\QueryParameter;
use Creonit\RestBundle\Handler\RestHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @Route("/pages")
 */
class PageController extends AbstractController
{
    /**
     * Получить информацию о странице по её URL
     *
     * @QueryParameter("path", type="string", description="URL страницы")
     *
     * @Route("", methods={"GET"})
     * @Cache(smaxage=60)
     */
    public function getPagesByUrl(RestHandler $handler, PageService $pageService, Request $request)
    {
        $handler->validate([
            'query' => [
                'path' => [new NotBlank()],
            ]
        ]);

        $page = $pageService->getPage($request->query->get('path'));
        $handler->checkFound($page);
        $handler->data->set($page);

        return $handler->response();
    }

    /**
     * Получить информацию о странице по её идентификатору
     *
     * @PathParameter("name", type="string", description="Идентификатор страницы")
     *
     * @Route("/{name}", methods={"GET"})
     * @Cache(smaxage="60")
     */
    public function getPageByName(RestHandler $handler, PageService $pageService, $name)
    {
        $page = $pageService->getPageByName($name);

        if (!$page) {
            $page = $pageService->getPageById($name);
        }

        $handler->checkFound($page);
        $handler->data->set($page);

        return $handler->response();
    }
}
