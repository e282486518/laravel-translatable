@if($rows)
    <div class="ml-2 mb-2 mr-2" style="margin-top: -0.5rem">
        @foreach($rows as $row)
            {!! $row->render() !!}
        @endforeach

        @foreach($fields as $field)
            @if($field instanceof Dcat\Admin\Form\Field\Hidden)
                {!! $field->render() !!}
            @endif
        @endforeach
    </div>
@elseif($layout->hasColumns())
    {!! $layout->build() !!}
@else
    @if($istrans && config('translatable.locale_array')) {{-- 开启多语言 and 模型中有多语言字段 --}}
        @if(config('translatable.locale_form') == 'tab')
            <!-- Tab 显示多语言 -->
            <div>
                <ul class="nav nav-tabs pl-1" style="margin-top: -1rem">
                    @foreach(config('translatable.locale_array') as $lang => $label)
                        <li class="nav-item">
                            <a class="nav-link {{ $lang == 'zh_CN' ? 'active' : '' }}" href="#{{ $lang }}" data-toggle="tab">
                                {!! $label !!} &nbsp;<i class="feather icon-alert-circle has-tab-error text-danger d-none"></i>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <div class="tab-content fields-group mt-2 pt-1 pb-1">
                    @foreach(config('translatable.locale_array') as $lang => $label)
                        <div class="tab-pane {{ $lang == config('app.locale') ? 'active' : '' }}" id="{{ $lang }}">
                            @foreach($fields as $field)
                                @if($lang == config('app.locale') || $field->getTranslatable()) {{-- 只在第一个tab中显示非多语言字段 --}}
                                {!! $field->setLocale($lang)->render() !!}
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            @foreach($fields as $field)
                @foreach(config('translatable.locale_array') as $lang => $label)
                    @if($lang == config('app.locale') || $field->getTranslatable())
                    {!! $field->setLocale($lang)->render() !!}
                    @endif
                @endforeach
            @endforeach
        @endif
    @else
        @foreach($fields as $field)
            {!! $field->render() !!}
        @endforeach
    @endif
@endif
