<?php
namespace Illuminate\Support\Traits;
trait Macroable
{
    public static function macro($name, $macro = null) {}
    public static function hasMacro($name)
    {
        return false;
    }
    public function __call($method, $parameters)
    {
    }
    public static function __callStatic($method, $parameters)
    {
    }
}
