<?php

namespace e282486518\Translatable\Core\Form;

use e282486518\Translatable\Core\HasFieldTranslatable;
use Illuminate\Support\Arr;

class Field extends \Dcat\Admin\Form\Field
{

    use HasFieldTranslatable;

    /**
     * @param  array  $data
     * @param  string  $column
     * @param  mixed  $default
     * @return mixed
     */
//    protected function getValueFromData($data, $column = null, $default = null)
//    {
//        $column = $column ?: $this->normalizeColumn();
//
//        [$column, $lng] = Helpers::getColumnAndlng($column);
//
//        if (Arr::has($data, $column)) {
//            $ret = Arr::get($data, $column, $default);
//            if (is_array($ret) && $lng != '') {
//                if (isset($ret[$lng])) {
//                    // 有语言key
//                    return $ret[$lng];
//                } else {
//                    // 无语言key
//                    return "";
//                }
//            }
//            return $ret;
//        }
//
//        $ret = Arr::get($data, Str::snake($column), $default);
//        if (is_array($ret) && $lng != '' && isset($ret[$lng])) {
//            return $ret[$lng];
//        }
//        return $ret;
//    }

    /**
     * @return string
     */
    protected function defaultPlaceholder(): string {
        return trans('admin.input', [], $this->getLocale()).' '.$this->label;
    }

    /**
     * Add html attributes to elements.
     *
     * @param  array|string  $attribute
     * @param  mixed  $value
     * @return $this
     */
    public function attribute($attribute, $value = null)
    {
        if (is_array($attribute)) {
            $this->attributes = array_merge($this->attributes, $attribute);
        } else {
            if (is_array($value)) {
                $this->attributes[$attribute] =  $value;
            } else {
                $this->attributes[$attribute] = (string) $value;
            }
        }

        return $this;
    }

    /**
     * Format the field attributes.
     *
     * @return string
     */
    protected function formatAttributes()
    {
        $html = [];

        foreach ($this->attributes as $name => $value) {
            // 判断当前字段的名称, 是否支持多语言
            if ($this->getTranslatable()) {
                if ($name == 'name') {
                    // value=title[en]
                    $value .= '['.$this->getLocale().']';
                }
                if ($name == 'placeholder') {
                    // value=title[en]
                    $value = $this->defaultPlaceholder();
                }
                if ($name == 'value' && is_array($value)) {
                    // value=$value[en]
                    $value = Arr::get($value, $this->getLocale(), json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                }
            }
            $html[] = $name.'="'.e($value).'"';
        }

        return implode(' ', $html);
    }

    /**
     * Get the view variables of this field.
     *
     * @return array
     */
    public function defaultVariables()
    {
        //dump($this->attributes, $this->value, $this->formatAttributes(), $this->column, $this->form->model());
        // 设置label多语言
        if (!is_array($this->column) && $this->getLocale() != config('app.locale')) {
            $this->label = str_replace('_', ' ', admin_trans_field($this->column, $this->getLocale()));
        }

        return [
            'name'        => $this->getElementName().$this->getLocaleName(),
            'help'        => $this->help,
            'class'       => $this->getElementClassString(),
            'value'       => $this->value(),
            'label'       => $this->label.$this->getLocaleLabel(),
            'viewClass'   => $this->getViewElementClasses(),
            'column'      => $this->column,
            'errorKey'    => $this->getErrorKey(),
            'attributes'  => $this->formatAttributes(),
            'placeholder' => $this->placeholder(),
            'disabled'    => $this->attributes['disabled'] ?? false,
            'formId'      => $this->getFormElementId(),
            'selector'    => $this->getElementClassSelector(),
            'options'     => $this->options,
        ];
    }

    /**
     * ---------------------------------------
     * 在 form 的 name 值后面加上 语言 属性, "name=abc" => "name=abc[zh_CN]"
     *
     * @return string
     * @author hlf <phphome@qq.com> 2024/10/18
     * ---------------------------------------
     */
    public function getLocaleName() {
        if ($this->getTranslatable()) {
            return '['.$this->getLocale().']';
        }
        return '';
    }

    /**
     * ---------------------------------------
     * 在 form 的 label 值后面加上 语言 属性, "姓名" => "姓名[cn]"
     *
     * @return string
     * @author hlf <phphome@qq.com> 2024/10/18
     * ---------------------------------------
     */
    public function getLocaleLabel() {
        if ($this->getTranslatable()) {
            $_locale = $this->extractLocaleInfo($this->getLocale());
            return '['.$_locale.']';
        }
        return '';
    }

    /**
     * ---------------------------------------
     * 取语言中的地图码
     * zh_CN => cn,  en => en
     *
     * @param $locale
     * @return string
     * @author hlf <phphome@qq.com> 2024/10/18
     * ---------------------------------------
     */
    function extractLocaleInfo($locale) {
        // 查找下划线的位置
        $underscorePosition = strpos($locale, '_');
        if ($underscorePosition === false) {
            // 如果没有下划线，则只有语言代码
            return strtolower($locale);
        } else {
            // 如果有下划线，则分割字符串获取语言代码和区域代码
            return strtolower(substr($locale, $underscorePosition + 1));
        }
    }

}
