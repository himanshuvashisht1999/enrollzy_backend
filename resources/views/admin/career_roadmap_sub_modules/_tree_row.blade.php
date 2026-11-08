{{-- One roadmap or item in the tree: its order, title, type, links and actions. --}}
@php
    $links = array_filter([$r->exam_id ? 'Exam' : null, $r->course_id ? 'Course' : null, $r->scholarship_id ? 'Scholarship' : null, $r->career_id ? 'Career' : null]);
    $type = $top ? ($rtypes[$r->roadmap_type] ?? null) : ($itypes[$r->item_type] ?? null);
@endphp
<div class="crm-row" id="item-{{ $r->id }}">
    <input type="number" class="form-control form-control-sm ord" name="sort[{{ $r->id }}]" value="{{ $r->sort_order }}" aria-label="Order of {{ $r->title }}">
    <div class="ttl">
        @if($top)<b class="{{ $r->status ? '' : 'off' }}">{{ $r->title }}</b> <small class="text-muted ms-1">/{{ $class->slug }}/{{ $r->url_slug }}</small>
        @else<span class="{{ $r->status ? '' : 'off' }}">{{ $r->title }}</span>@endif
    </div>
    @if($type)<span class="crm-badge {{ $top ? 'rt' : '' }}">{{ $type }}</span>@else<span class="crm-badge warn">No type</span>@endif
    @foreach($links as $l)<span class="crm-badge ln"><i class="fas fa-link"></i> {{ $l }}</span>@endforeach
    <div class="acts">
        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.career-roadmap-sub-modules.edit', $r->id) }}" title="Edit"><i class="fas fa-pen"></i></a>
        <a class="btn btn-sm btn-outline-success" href="{{ route('admin.career-roadmap-sub-modules.create', ['parent_id' => $r->id]) }}" title="Add an item under this"><i class="fas fa-plus"></i></a>
    </div>
</div>
