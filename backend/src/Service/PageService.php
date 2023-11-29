<?php


namespace App\Service;


use Creonit\PageBundle\Model\Page;
use Creonit\PageBundle\Model\PageQuery;
use Propel\Runtime\Collection\ObjectCollection;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Routing\RouterInterface;

class PageService
{
    use ContainerAwareTrait;

    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Получить страницу по URL
     * @param string $url
     * @return Page
     */
    public function getPage($url)
    {
        try {
            $route = $this->router->match($url);

        } catch (\Exception $exception) {
            return null;
        }

        if (preg_match('/^_page_(\d+)$/', $route['_route'], $match)) {
            $page = $this->getPageById($match[1]);

        } else {
            $page = $this->getPageByName($route['_route']);
        }

        return $page;
    }

    /**
     * Получить страницу по имени
     * @param string $name
     * @return Page
     */
    public function getPageByName($name)
    {
        return PageQuery::create()->findOneByName($name);
    }

    /**
     * Получить страницу по id
     * @param $id
     * @return Page
     */
    public function getPageById($id)
    {
        return PageQuery::create()->findPk($id);
    }

    /**
     * Получить дочерние страницы
     * @param Page $page
     * @param int $level
     * @return Page[]|ObjectCollection
     */
    public function getChildren(Page $page, $level = 1)
    {
        return $page->getChildrenQuery($level)->forList()->find();
    }
}
