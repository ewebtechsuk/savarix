<?php
namespace GuzzleHttp;
if (!\function_exists(__NAMESPACE__.'\\uri_template')) {
    function uri_template(string $template, array $variables = []): string
    {
        foreach ($variables as $key => $value) {
            $template = \str_replace('{'.$key.'}', $value, $template);
        }
        return $template;
    }
}
