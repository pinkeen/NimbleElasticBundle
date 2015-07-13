<?php

namespace Nimble\ElasticBundle;

class ClassUtils 
{
    /**
     * @param string $className
     * @param array $array
     * @return string|null
     */
    public static function findClassKey($className, array $array)
    {
        if (array_key_exists($className, $array)) {
            return $className;
        }

        $parentClassName = $className;

        while (false !== ($parentClassName = get_parent_class($parentClassName))) {
            if (array_key_exists($parentClassName, $array)) {
                return $parentClassName;
            }
        }

        return null;
    }
}