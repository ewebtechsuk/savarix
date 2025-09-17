<?php

namespace Framework\Http;

class Request
{
    private string $method;
    private string $uri;

    public function __construct(string $method, string $uri)
    {
        $this->method = strtoupper($method);
        $this->uri = $uri;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }
}
