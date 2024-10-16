<?php

namespace e282486518\Translatable\Core;

trait HasFieldTranslatable
{

    /**
     * @var string 设置当前字段的语言
     */
    protected string $locale;

    /**
     * @var bool 是否是多语言字段
     */
    protected bool $locale_fields = false;

    // ==== 设置当前语言 =======================
    public function getLocale(): string {
        return $this->locale ?? config('app.locale');
    }

    public function setLocale(string $locale) {
        $this->locale = $locale;
        return $this;
    }

    // ==== 设置该字段是否支持多语言 ===============
    public function getTranslatable(): bool {
        return $this->locale_fields;
    }

    public function setTranslatable(bool $fields) {
        $this->locale_fields = $fields;
        return $this;
    }

}
