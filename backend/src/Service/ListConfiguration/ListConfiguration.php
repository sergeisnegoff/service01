<?php


namespace App\Service\ListConfiguration;


use Symfony\Component\HttpFoundation\Request;

class ListConfiguration
{
    protected int $page = 0;
    protected int $limit = 0;

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): self
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public static function fromRequest(Request $request)
    {
        $instance = new static();
        $instance
            ->setPage((int) $request->query->get('page', 0))
            ->setLimit((int) $request->query->get('limit', 0));

        return $instance;
    }
}
