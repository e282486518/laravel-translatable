<div class="row" style="margin-bottom: 8px">
    @foreach($fields as $field)
        @foreach(config('translatable.locale_array') as $lang => $label)
        @if($lang == config('app.locale') || $field['element']->getTranslatable())
        <div class="col-md-{{ $field['width'] }}">
            {!! $field['element']->setLocale($lang)->render() !!}
        </div>
        @endif
        @endforeach
    @endforeach
</div>
