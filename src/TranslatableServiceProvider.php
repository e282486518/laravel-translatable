<?php

namespace e282486518\Translatable;

use Dcat\Admin\Extend\ServiceProvider;
use Dcat\Admin\Admin;

class TranslatableServiceProvider extends ServiceProvider
{
	protected $js = [
        'js/index.js',
    ];
	protected $css = [
		'css/index.css',
	];

	public function register()
	{
		//
	}

	public function init()
	{
		parent::init();

		// 注册视图文件的命名空间, 访问视图时使用"translatable::abc.def"访问
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'translatable');

	}

	public function settingForm()
	{
		return new Setting($this);
	}
}
