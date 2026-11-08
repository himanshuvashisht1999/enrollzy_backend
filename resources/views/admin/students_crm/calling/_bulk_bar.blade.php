{{-- Admin: bulk reassign the ticked rows of this tab (Team Assigned Leads, Completed Calls). Targets are the staff who call
     (admins have no calling queue, so leads are never sent to one). --}}
<form class="bulk-form d-flex flex-wrap align-items-center gap-2 px-3 py-2 border-bottom" style="background:#f8fafc">
    @csrf
    <span class="fw-bold text-dark"><i class="fas fa-random text-primary me-1"></i><span class="bulk-count">0</span> selected</span>
    <span class="small text-muted">Tick rows, or the header box for all shown. {{ $hint }}</span>
    <div class="ms-auto d-flex gap-2 align-items-center">
        <select name="staff_id" class="form-select form-select-sm" style="min-width:210px" required>
            <option value="">Reassign to…</option>
            @foreach($assignableStaffs as $staff)
                <option value="{{ $staff->id }}">{{ $staff->name }}{{ $staff->role ? ' · ' . $staff->role : '' }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold bulk-submit" disabled><i class="fas fa-user-edit me-1"></i>Reassign selected</button>
    </div>
</form>
