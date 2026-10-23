{{-- One settings field of a homepage section (see App\Support\HomepageSectionSchema).
     $f = field definition, $name = input name, $value = saved value (null = never saved), $depth = repeater nesting. --}}
@php
    $id = 'f' . substr(md5($name), 0, 10);
    $depth = $depth ?? 0;
@endphp
@switch($f['type'])
    @case('toggle')
        <div class="form-check form-switch mb-3">
            <input type="hidden" name="{{ $name }}" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="{{ $id }}" name="{{ $name }}" value="1" @checked($value === null ? ($f['default'] ?? false) : (bool) $value)>
            <label class="form-check-label fw-semibold" for="{{ $id }}">{{ $f['label'] }}</label>
        </div>
        @break

    @case('list')
        <div class="mb-3">
            <label class="form-label fw-bold" for="{{ $id }}">{{ $f['label'] }}</label>
            <textarea class="form-control" id="{{ $id }}" name="{{ $name }}" rows="{{ max(3, count((array) $value) + 1) }}">{{ implode("\n", (array) $value) }}</textarea>
            @if(!empty($f['help']))<div class="form-text">{{ $f['help'] }}</div>@endif
        </div>
        @break

    @case('number')
        <div class="mb-3" style="max-width:220px">
            <label class="form-label fw-bold" for="{{ $id }}">{{ $f['label'] }}</label>
            <input class="form-control" type="number" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" min="{{ $f['min'] ?? 0 }}" @isset($f['max']) max="{{ $f['max'] }}" @endisset>
            @if(!empty($f['help']))<div class="form-text">{{ $f['help'] }}</div>@endif
        </div>
        @break

    @case('icon')
        <div class="mb-2">
            <label class="form-label small fw-bold mb-1" for="{{ $id }}">{{ $f['label'] }}</label>
            <select class="form-select form-select-sm" id="{{ $id }}" name="{{ $name }}">
                @foreach(\App\Support\HomepageSectionSchema::ICONS as $k => $label)<option value="{{ $k }}" @selected($value === $k)>{{ $label }}</option>@endforeach
            </select>
        </div>
        @break

    @case('select')
        <div class="{{ $depth ? 'mb-2' : 'mb-3' }}">
            <label class="form-label {{ $depth ? 'small fw-bold mb-1' : 'fw-bold' }}" for="{{ $id }}">{{ $f['label'] }}</label>
            <select class="form-select {{ $depth ? 'form-select-sm' : '' }}" id="{{ $id }}" name="{{ $name }}">
                @foreach($f['options'] as $k => $label)<option value="{{ $k }}" @selected((string) $value === (string) $k)>{{ $label }}</option>@endforeach
            </select>
            @if(!empty($f['help']))<div class="form-text">{{ $f['help'] }}</div>@endif
        </div>
        @break

    @case('repeater')
        @php($rows = array_values(is_array($value) ? $value : []))
        <div class="mb-4 hs-repeater" data-depth="{{ $depth }}">
            <label class="form-label fw-bold d-block">{{ $f['label'] }}</label>
            @if(!empty($f['help']))<div class="form-text mt-0 mb-2">{{ $f['help'] }}</div>@endif
            <div class="hs-rows">
                @foreach($rows as $i => $row)
                    @include('admin.homepage-sections._row', ['fields' => $f['fields'], 'prefix' => $name . '[' . $i . ']', 'row' => is_array($row) ? $row : [], 'depth' => $depth])
                @endforeach
            </div>
            <template class="hs-template">
                @include('admin.homepage-sections._row', ['fields' => $f['fields'], 'prefix' => $name . '[__R' . $depth . '__]', 'row' => [], 'depth' => $depth])
            </template>
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill hs-add"><i class="fas fa-plus me-1"></i> {{ $f['add'] ?? 'Add' }}</button>
        </div>
        @break

    @default
        <div class="{{ $depth ? 'mb-2' : 'mb-3' }}">
            <label class="form-label {{ $depth ? 'small fw-bold mb-1' : 'fw-bold' }}" for="{{ $id }}">{{ $f['label'] }}</label>
            <input class="form-control {{ $depth ? 'form-control-sm' : '' }}" type="text" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $f['placeholder'] ?? '' }}">
            @if(!empty($f['help']))<div class="form-text">{{ $f['help'] }}</div>@endif
        </div>
@endswitch
