<?php

if(!function_exists('__t'))
{
    function __t(string $key, array $params = [])
    {
        return __('design-pattern-implementor::'.$key, $params);
    }
}