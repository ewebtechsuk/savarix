<?php

namespace Framework\Http;

class Response
{
    private int $status;
    private array $headers = [];
    private string $body;

    public function __construct(string $body = '', int $status = 200, array $headers = [])
    {
        $this->body = $body;
        $this->status = $status;
        $this->headers = $headers;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function header(string $name, ?string $value = null)
    {
        if ($value === null) {
            return $this->headers[strtolower($name)] ?? null;
        }

        $this->headers[strtolower($name)] = $value;
        return $this;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function body(): string
    {
        return $this->body;
    }

    public static function view(string $content, int $status = 200): self
    {
        return new self($content, $status, ['content-type' => 'text/html; charset=utf-8']);
    }

    public static function redirect(string $location, int $status = 302): self
    {
        return new self('', $status, ['location' => $location]);
    }
}
