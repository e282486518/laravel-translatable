<?php

namespace e282486518\Translatable\Core\Show;

use Dcat\Admin\Show\Tools;
use Illuminate\Support\Collection;

class Panel extends \Dcat\Admin\Show\Panel
{
    /**
     * The view to be rendered.
     *
     * @var string
     */
    protected $view = 'translatable::show.panel';

    /**
     * Initialize view data.
     */
    protected function initVariables()
    {
        $this->variables = [
            'fields' => new Collection(),
            'tools'  => new Tools($this),
            'rows'   => $this->parent->rows(),
            'style'  => 'default',
            'title'  => trans('admin.detail'),
            'istrans' => $this->isTranslatable()  // 新增view变量
        ];
    }

    /**
     * ---------------------------------------
     * 判断
     * ---------------------------------------
     */
    public function isTranslatable(): bool {
        if ($this->parent->repository()) {
            $_model = $this->parent->repository()->model(); // 模型存在时, 取模型
            if (!empty($_model->translatable)) {
                return true;
            }
        }
        return false;
    }
}
