<?php

namespace e282486518\Translatable\Core\Form\Field;

use e282486518\Translatable\Core\Form\Field;

class Divide extends Field
{
    public function __construct($label = null)
    {
        $this->label = $label;
    }

    public function render()
    {
        if (! $this->label) {
            return '<hr/>';
        }

        return <<<HTML
<div class="mt-2 text-center mb-2 form-divider">
  <span>{$this->label}</span>
</div>
HTML;
    }
}
