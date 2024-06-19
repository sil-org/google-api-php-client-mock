<?php

namespace SilMock\Google\Http;

class Batch
{
    public array $batch = [];

    public function add($result, string $key)
    {
        $this->batch[$key] = $result;
    }

    public function execute()
    {
        return $this->batch;
    }
}
