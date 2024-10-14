<?php

namespace e282486518\Translatable\Core\Grid;

use Closure;
use e282486518\Translatable\Helpers;

class Row extends \Dcat\Admin\Grid\Row
{

    /**
     * Get or set value of column in this row.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return $this|mixed
     */
    public function column($name, $value = null)
    {
        if (is_null($value)) {
            $attr = Helpers::getArrValueByLocale($this->data, $name);//dump($attr);
            return $this->output($attr);
        }

        if ($value instanceof Closure) {
            $value = $value->call($this, $this->column($name));
        }

        $this->data[$name] = $value;

        return $this;
    }


}
