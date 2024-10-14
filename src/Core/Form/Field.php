<?php

namespace e282486518\Translatable\Core\Form;

use e282486518\Translatable\Helpers;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Field extends \Dcat\Admin\Form\Field
{

    /**
     * @param  array  $data
     * @param  string  $column
     * @param  mixed  $default
     * @return mixed
     */
    protected function getValueFromData($data, $column = null, $default = null)
    {
        $column = $column ?: $this->normalizeColumn();

        [$column, $lng] = Helpers::getColumnAndlng($column);

        if (Arr::has($data, $column)) {
            $ret = Arr::get($data, $column, $default);
            if (is_array($ret) && $lng != '') {
                if (isset($ret[$lng])) {
                    // 有语言key
                    return $ret[$lng];
                } else {
                    // 无语言key
                    return "";
                }
            }
            return $ret;
        }

        $ret = Arr::get($data, Str::snake($column), $default);
        if (is_array($ret) && $lng != '' && isset($ret[$lng])) {
            return $ret[$lng];
        }
        return $ret;
    }

    /**
     * Add html attributes to elements.
     *
     * @param  array|string  $attribute
     * @param  mixed  $value
     * @return $this
     */
    public function attribute($attribute, $value = null)
    {
        if (is_array($attribute)) {
            $this->attributes = array_merge($this->attributes, $attribute);
        } else {
            if (is_array($value)) {
                $this->attributes[$attribute] =  json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                $this->attributes[$attribute] = (string) $value;
            }
        }

        return $this;
    }

}
