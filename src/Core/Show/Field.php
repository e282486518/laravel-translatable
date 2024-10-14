<?php

namespace e282486518\Translatable\Core\Show;

use e282486518\Translatable\Helpers;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

class Field extends \Dcat\Admin\Show\Field
{

    /**
     * @param  string  $val
     * @return $this
     */
    public function prepend($val)
    {
        $name = $this->name;
        [$name, $lng] = Helpers::getColumnAndlng($name); // 获取: 字段+语言

        return $this->as(function ($v) use (&$val, $name) {
            if ($val instanceof \Closure) {
                $val = $val->call($this, $v, Arr::get($this, $name));
            }

            if (is_array($v)) {
                array_unshift($v, $val);

                return $v;
            } elseif ($v instanceof Collection) {
                return $v->prepend($val);
            }

            return $val.$v;
        });
    }

    /**
     * @param  string  $val
     * @return $this
     */
    public function append($val)
    {
        $name = $this->name;
        [$name, $lng] = Helpers::getColumnAndlng($name); // 获取: 字段+语言

        return $this->as(function ($v) use (&$val, $name) {
            if ($val instanceof \Closure) {
                $val = $val->call($this, $v, Arr::get($this, $name));
            }

            if (is_array($v)) {
                array_push($v, $val);

                return $v;
            } elseif ($v instanceof Collection) {
                return $v->push($val);
            }

            return $v.$val;
        });
    }

    /**
     * @param  Fluent|\Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function fill($model)
    {
        $name = $this->name;
        [$name, $lng] = Helpers::getColumnAndlng($name); // 获取: 字段+语言

        $colval = Arr::get($model->toArray(), $name);
        if (is_array($colval) && Arr::has($colval, $lng)) {
            $colval = Arr::get($colval, $lng);
        }

        $this->value($colval);
    }

}
