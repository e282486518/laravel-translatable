@if($title || $tools)
<div class="box-header with-border" style="padding: .65rem 1rem">
    <h3 class="box-title" style="line-height:30px;">{!! $title !!}</h3>
    <div class="pull-right">{!! $tools !!}</div>
</div>
@endif
<div class="box-body">
    <div class="form-horizontal mt-1">
        @if($rows->isEmpty())
            @if($istrans && config('translatable.locale_array')) {{-- 开启多语言 and 模型中有多语言字段 --}}
                @foreach($fields as $field)
                    @foreach(config('translatable.locale_array') as $lang => $label)
                        @if($lang == config('app.locale') || $field->getTranslatable())
                            {!! $field->setLocale($lang)->render() !!}
                        @endif
                    @endforeach
                @endforeach
            @else
                @foreach($fields as $field)
                    {!! $field->render() !!}
                @endforeach
            @endif
        @else
            <div>
                @foreach($rows as $row)
                    {!! $row->render() !!}
                @endforeach
            </div>
        @endif
        <div class="clearfix"></div>
    </div>
</div>
