<?php

namespace e282486518\Translatable\Core\Form;

use e282486518\Translatable\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Field extends \Dcat\Admin\Form\Field
{

    /**
     * @var string 设置当前字段的语言
     */
    protected string $locale;

    public function getLocale(): string {
        return $this->locale ?? config('app.locale');
    }

    public function setLocale($locale) {
        $this->locale = $locale;
    }

    public function isTranslatable(): bool {
        return $this->form->model() instanceof Model && in_array($this->column, $this->form->model()->translatable);
    }

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
            if ($this->isTranslatable()) {
                if ($name == 'name') {
                    // value=title[en]
                    $value .= '['.$this->getLocale().']';
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
    {//dump($this->attributes, $this->value(), $this->formatAttributes(), $this->column);
        return [
            'name'        => $this->getElementName(),
            'help'        => $this->help,
            'class'       => $this->getElementClassString(),
            'value'       => $this->value(),
            'label'       => $this->label,
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

}
