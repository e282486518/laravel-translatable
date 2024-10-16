<?php

namespace e282486518\Translatable\Core\Form\Field;

class Password extends Text
{
    public function render()
    {
        $this->prepend('<i class="feather icon-eye"></i>')
            ->defaultAttribute('type', 'password');

        return parent::render();
    }
}
