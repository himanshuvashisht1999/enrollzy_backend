{{-- Next to a lead's name in the calling lists: the star when the lead is marked important (call form → LeadFlags). --}}
@if(\App\Support\Calling\LeadFlags::isImportant($id))
    <span class="badge rounded-pill ms-1 align-middle" title="Marked important" style="background:#fff7e0;color:#a15c00;border:1px solid #f5d27a;font-size:.7rem;font-weight:600"><i class="fas fa-star me-1" style="color:#f5a524"></i>Important</span>
@endif
