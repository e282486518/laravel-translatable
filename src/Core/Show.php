<?php

namespace e282486518\Translatable\Core;

use e282486518\Translatable\Core\Show\Field;

class Show extends \Dcat\Admin\Show
{

    /**
     * Add a model field to show.
     *
     * @param  string  $name
     * @param  string  $label
     * @return Field
     */
    protected function addField($name, $label = '')
    {
        $field = new Field($name, $label);

        $field->setParent($this);

        $this->overwriteExistingField($name);

        $this->fields->push($field);

        return $field;
    }

}
