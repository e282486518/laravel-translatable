<?php

namespace e282486518\Translatable\Core;

class Show extends \Dcat\Admin\Show
{

    /**
     * Add a model field to show.
     *
     * @param  string  $name
     * @param  string  $label
     * @return Show\Field
     */
    protected function addField($name, $label = '')
    {
        $field = new Show\Field($name, $label);

        $field->setParent($this);

        $this->overwriteExistingField($name);

        $this->fields->push($field);

        return $field;
    }

}
