<?php

namespace e282486518\Translatable\Core\Form\Field;

use e282486518\Translatable\Core\Form\Field;

class Nullable extends Field
{
    public function __construct()
    {
    }

    public function __call($method, $parameters)
    {
        return $this;
    }

    public function render()
    {
    }
}
