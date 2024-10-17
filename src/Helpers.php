<?php

namespace e282486518\Translatable;

use Illuminate\Support\Arr;

class Helpers
{
    /**
     * ---------------------------------------
     * 获取列名和语言变量 "abc[cn]" => ["abc", "cn"]
     *
     * @param string $column
     * @return array
     * @author hlf <phphome@qq.com> 2024/10/11
     */
    public static function getColumnAndlng($column): array {
        $lng = '';
        if (is_array($column)) {
            return [$column, $lng];
        }
        if (preg_match('/(\w+)\[(\w+)\]/', $column, $matches)) {
            $column = $matches[1];
            $lng = $matches[2];
        }
        return [$column, $lng];
    }

    public static function getArrValueByLocale($arr, $name){
        $attr = Arr::get($arr, $name);//dump($attr);
        $locale = config('app.locale');
        if (is_array($attr) && Arr::has($attr, $locale)) {
            $attr = Arr::get($attr, $locale);
        }
        return $attr;
    }

}

