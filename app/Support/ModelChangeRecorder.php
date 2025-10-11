<?php

namespace App\Support;

class ModelChangeRecorder
{
    /** @var array<int, array> */
    protected array $originalAttributes = [];

    public function record(object $model): void
    {
        $this->originalAttributes[$this->key($model)] = method_exists($model, 'getOriginal')
            ? $model->getOriginal()
            : [];
    }

    public function pull(object $model): array
    {
        $key = $this->key($model);
        $original = $this->originalAttributes[$key] ?? [];
        unset($this->originalAttributes[$key]);

        return $original;
    }

    protected function key(object $model): int
    {
        return spl_object_id($model);
    }
}

