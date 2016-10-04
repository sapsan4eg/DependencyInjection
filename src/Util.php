<?php

namespace Sixx\DependencyInjection;

class Util
{
    public static function removeFirstSlash($string)
    {
        if (0 === strpos($string, '\\')) {
            $string = substr($string, 1);
        }

        return $string;
    }
}
