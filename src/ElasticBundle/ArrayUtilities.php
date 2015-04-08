<?php

namespace Nimble\ElasticBundle;

class ArrayUtilities 
{
    /**
     * @param array $arr
     * @return array
     */
    public static function deepKeySort(array $arr)
    {
        foreach (array_keys($arr) as $key) {
            if (!is_array($arr[$key])) {
                continue;
            }

            $arr[$key] = self::deepKeySort($arr[$key]);
        }

        ksort($arr, SORT_STRING);

        return $arr;
    }

    /**
     * @param array $arrA
     * @param array $arrB
     * @return bool
     */
    public static function deepCompare(array $arrA = null, array $arrB = null)
    {
        if (null === $arrA) {
            if (null === $arrB) {
                return true;
            }

            return false;
        }

        echo "\n\n".json_encode(self::deepKeySort($arrA)). "\n\n\n";
        echo json_encode(self::deepKeySort($arrB));

        return json_encode(self::deepKeySort($arrA)) === json_encode(self::deepKeySort($arrB));
    }
}