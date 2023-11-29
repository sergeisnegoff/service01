<?php


namespace App\Controller\Api;

use Creonit\RestBundle\Annotation\Parameter as Rest;
use Creonit\RestBundle\Handler\RestHandler;
use Creonit\StorageBundle\Storage\Storage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @Route("/storage")
 */
class StorageController extends AbstractController
{
    /**
     * Получить детальную информацию о контейнере контента
     *
     * @Rest\PathParameter("key", type="string", description="Ключ элемента")
     * @Rest\QueryParameter("locale", type="int", description="Локаль")
     * @Rest\QueryParameter("context", type="int", description="Контекст элемента")
     *
     * @Route("/{key}", methods={"GET"})
     * @Cache(smaxage="60")
     */
    public function getContent(RestHandler $handler, Storage $storage, Request $request, $key)
    {
        $handler->validate([
            'query' => [
                'locale' => [new Type(['type' => 'string'])],
                'context' => [new Type(['type' => 'string'])],
            ]
        ]);

        $handler->data->set(
            $storage->get(
                $key,
                $request->query->get('locale'),
                $request->query->get('context')
            )
        );

        return $handler->response();
    }
}
