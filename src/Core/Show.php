<?php

namespace e282486518\Translatable\Core;

use e282486518\Translatable\Core\Show\Panel;

class Show extends \Dcat\Admin\Show
{

    /**
     * @var string
     */
    protected $view = 'translatable::show.container';

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

        // 设置该字段是否支持多语言
        if ($this->repository()) {
            $_model = $this->repository()->model(); // 模型存在时, 取模型
            if (isset($_model->translatable)) {
                $_fields = $_model->translatable?:[]; // 取多语言字段列表, 默认[]
                if (in_array($name, $_fields)) {
                    $field->setTranslatable(true);
                }
            }
        }

        $field->setParent($this);

        $this->overwriteExistingField($name);

        $this->fields->push($field);

        return $field;
    }

    /**
     * Initialize panel.
     */
    protected function initPanel()
    {
        $this->panel = new Panel($this);
    }

}
