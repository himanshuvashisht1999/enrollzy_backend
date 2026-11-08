{{-- Institute section: the tab buttons for partials/institute_brand_fields (create and edit) --}}
@foreach(['contact' => 'Contact & Social', 'network' => 'Courses & Network', 'offer' => 'Scholarship & Franchise', 'trust' => 'Trust & Media'] as $id => $label)
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="inst-{{ $id }}-tab" data-bs-toggle="tab" data-bs-target="#inst-{{ $id }}" type="button" role="tab" aria-controls="inst-{{ $id }}" aria-selected="false">{{ $label }}</button>
    </li>
@endforeach
