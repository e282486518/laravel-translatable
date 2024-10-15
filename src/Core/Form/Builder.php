<?php

namespace e282486518\Translatable\Core\Form;

class Builder extends \Dcat\Admin\Form\Builder
{
    /**
     * View for this form.
     *
     * @var string
     */
    protected $view = 'translatable::form.container';

    /**
     * ---------------------------------------
     * 判断
     * ---------------------------------------
     */
    public function isTranslatable(): bool {
        if ($this->form->repository()) {
            $_model = $this->form->repository()->model(); // 模型存在时, 取模型
            if (!empty($_model->translatable)) {
                return true;
            }
        }
//        foreach ($this->fields() as $value) {
//            if ($value->getTranslatable()) {
//                return true;
//            }
//        }
        return false;
    }


}
