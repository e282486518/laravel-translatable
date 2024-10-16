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
    {//dump($this->attributes, $this->value(), $this->formatAttributes(), $this->column, $this->form->model());
        // 设置label多语言
        $this->label = str_replace('_', ' ', admin_trans_field($this->column, $this->getLocale()));

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
