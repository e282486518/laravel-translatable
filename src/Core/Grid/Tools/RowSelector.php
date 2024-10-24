<?php

namespace e282486518\Translatable\Core\Grid\Tools;

use Illuminate\Support\Arr;

class RowSelector extends \Dcat\Admin\Grid\Tools\RowSelector
{

    protected function getTitle($row, $id)
    {
        //dump($row->toArray(), $id, $this->titleColumn);
        if ($key = $this->titleColumn) {
            $_arr = $row->toArray();
            $label = Arr::get($_arr, $key);
            if ($label !== null && $label !== '') {
                $_locale = config('app.locale');
                if (is_array($label) && Arr::has($label, $_locale)) {
                    $label = Arr::get($label, $_locale);
                    if ($label !== null && $label !== '') {
                        return $label;
                    }
                }
                return $label;
            }

            return $id;
        }

        $label = $row->name ?: $row->title;

        return $label ?: ($row->username ?: $id);
    }
}
