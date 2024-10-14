<?php

namespace e282486518\Translatable\Core;

use e282486518\Translatable\Core\Grid\Row;
use Illuminate\Support\Collection;

class Grid extends \Dcat\Admin\Grid
{

    /**
     * Build the grid rows.
     *
     * @param  Collection  $data
     * @return void
     */
    protected function buildRows($data)
    {
        $this->rows = $data->map(function ($row) {
            return new Row($this, $row);
        });

        foreach ($this->rowsCallbacks as $callback) {
            $callback($this->rows);
        }
    }

}
