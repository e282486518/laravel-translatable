<?php

namespace e282486518\Translatable\Core;

trait HasFormTranslatable
{

    /**
     * @var string 多语言Form展示方式: tab/line
     */
    protected $localeForm;
    
    // 设置
    public function setLocaleForm($lab) {
        if (in_array($lab, ['tab', 'line'])) {
            $this->localeForm = $lab;
        }
        return $this;
    }
    // 获取
    public function getLocaleForm() {
        return $this->localeForm ?? config('translatable.locale_form');
    }

}
