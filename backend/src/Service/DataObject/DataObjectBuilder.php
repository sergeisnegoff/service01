<?php


namespace App\Service\DataObject;


class DataObjectBuilder
{
    public function build(string $class, array $data): DataObjectInterface
    {
        $class = $this->getClass($class);

        foreach ($data as $key => $value) {
            $setter = sprintf('set%s', ucfirst($key));

            if (!method_exists($class, $setter)) {
                continue;
            }

            $class->$setter($value);
        }

        return $class;
    }

    protected function getClass(string $class): DataObjectInterface
    {
        $class = new $class();

        if (!$class instanceof DataObjectInterface) {
            throw new \Exception('Class not instanceof DataObjectInterface');
        }

        return $class;
    }

    protected function getClassVars($class): array
    {
        return call_user_func('get_object_vars', $class);
    }
}
