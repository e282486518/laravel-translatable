<?php

namespace e282486518\Translatable;

use Dcat\Admin\Extend\Setting as Form;

class Setting extends Form
{
    public function form()
    {
        $this->text('key1')->required();
    }
}
