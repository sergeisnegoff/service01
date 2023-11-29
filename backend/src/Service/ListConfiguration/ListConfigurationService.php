<?php


namespace App\Service\ListConfiguration;


use Propel\Runtime\ActiveQuery\ModelCriteria;

class ListConfigurationService
{
    public function fetch(ModelCriteria $query, ListConfiguration $configuration)
    {
        $page = $configuration->getPage();
        $limit = $configuration->getLimit();

        if ($page && $page > 0) {
            return $query->paginate($page, $limit ?: 10);
        }

        if ($limit && $limit > 0) {
            $query->limit($limit);
        }

        return $query->find();
    }
}
