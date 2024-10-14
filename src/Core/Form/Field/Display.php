<?php

namespace e282486518\Translatable\Core\Form\Field;

use Closure;
use e282486518\Translatable\Core\Form\Field;

class Display extends Field
{
    protected $callback;

    public function with(Closure $callback)
    {
        $this->callback = $callback;
    }

    public function render()
    {
        if ($this->callback instanceof Closure) {
            $this->value = $this->callback->call($this->values(), $this->value());
        }

        return parent::render();
    }
}
