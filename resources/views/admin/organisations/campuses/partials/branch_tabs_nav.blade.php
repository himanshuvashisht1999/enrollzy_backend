{{-- campus create / edit: the coaching branch tabs (organisation type 3), placed before Documents --}}
@if($organisation->organisation_type_id == \App\Support\Institutes\InstituteFields::TYPE)
    <li class="nav-item"><a class="nav-link" href="#branch-details" role="tab"><i class="fas fa-store text-primary me-1"></i> Branch Details</a></li>
    <li class="nav-item"><a class="nav-link" href="#branch-media" role="tab"><i class="fas fa-images text-success me-1"></i> Photos & Videos</a></li>
@endif
