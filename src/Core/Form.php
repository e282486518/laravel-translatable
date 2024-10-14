<?php

namespace e282486518\Translatable\Core;

use Closure;
use Dcat\Admin\Contracts\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use e282486518\Translatable\Helpers;
use e282486518\Translatable\Core\Form\Field;

class Form extends \Dcat\Admin\Form
{

    /**
     * Create a new form instance.
     *
     * @param  Repository|Model|\Illuminate\Database\Eloquent\Builder|string  $model
     * @param  \Closure  $callback
     * @param  Request  $request
     */
    public function __construct($repository = null, ?Closure $callback = null, Request $request = null)
    {
        parent::__construct($repository, $callback, $request);
        $this->builder = new Form\Builder($this);

        // 自定义字段类
        Form::extend('text', Field\Text::class);
        Form::extend('button', Field\Button::class);
        Form::extend('cascadeGroup', Field\CascadeGroup::class);
        Form::extend('dateRange', Field\DateRange::class);
        Form::extend('display', Field\Display::class);
        Form::extend('divide', Field\Divide::class);
        Form::extend('editor', Field\Editor::class);
        Form::extend('embeds', Field\Embeds::class);
        Form::extend('file', Field\File::class);
        Form::extend('hasMany', Field\HasMany::class);
        Form::extend('hidden', Field\Hidden::class);
        Form::extend('html', Field\Html::class);
        Form::extend('id', Field\Id::class);
        Form::extend('keyValue', Field\KeyValue::class);
        Form::extend('listField', Field\ListField::class);
        Form::extend('map', Field\Map::class);
        Form::extend('markdown', Field\Markdown::class);
        Form::extend('radio', Field\Radio::class);
        Form::extend('range', Field\Range::class);
        Form::extend('select', Field\Select::class);
        Form::extend('selectTable', Field\SelectTable::class);
        Form::extend('slider', Field\Slider::class);
        Form::extend('switchField', Field\SwitchField::class);
        Form::extend('tree', Field\Tree::class);
        Form::extend('textarea', Field\Textarea::class);
        Form::extend('tags', Field\Tags::class);
    }

    /**
     * Prepare input data for update.
     * 支持 {"title": {"cn": "1", "en": "2"}, "desc": {"cn": "3", "en": "4"}, "status": 1} 格式
     * 也就是form的 title[cn]=1, title[en]=2, desc[cn]=3, desc[en]=4, status=1
     *
     * @param  array  $updates
     * @return array
     */
    public function prepareUpdate(array $updates): array {
        $prepared = [];

        /** @var Field $field */
        foreach ($this->builder->fields() as $field) {
            $columns = $field->column(); // title[cn]

            [$columns, $lng] = Helpers::getColumnAndlng($columns); // 新增行, 处理掉[]和其内的内容

            // If column not in input array data, then continue.
            if (! Arr::has($updates, $columns) || Arr::has($prepared, $columns)) {
                continue;
            }

            $value = $this->getDataByColumn($updates, $columns);

            $value = $field->prepare($value);

            if (is_array($columns)) {
                foreach ($columns as $name => $column) {
                    Arr::set($prepared, $column, $value[$name]);
                }
            } elseif (is_string($columns)) {
                Arr::set($prepared, $columns, $value);
            }
        }

        return $prepared;
    }
}
