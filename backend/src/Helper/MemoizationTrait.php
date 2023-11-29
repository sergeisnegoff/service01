<?php


namespace App\Helper;


trait MemoizationTrait
{
    protected array $memoizationData = [];

    public function memoization($key, \Closure $closure)
    {
        $data = $this->memoizationData[$key] ?? null;

        if (!$data) {
            $this->memoizationData[$key] = $closure();
        }

        return $this->memoizationData[$key];
    }
}
