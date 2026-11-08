{{-- Inside a call form (#callForm): escalate the lead to the caller's team lead, and mark it important. Saved with the
     call (CallingController::store → LeadFlags::afterCall). The lead's current flags load when its Call button opens the form. --}}
<div class="lf-box mx-3 my-2 p-3 rounded-3 border" style="background:#f8fafc">
    {{-- both important inputs stay disabled until the lead's flags load, so a slow load never clears the flag --}}
    <input type="hidden" name="lead_important" value="0" id="lf_important0" disabled>
    <div class="d-flex flex-wrap gap-4 align-items-center">
        <div class="form-check form-switch m-0">
            <input class="form-check-input" type="checkbox" role="switch" name="lead_important" value="1" id="lf_important" disabled>
            <label class="form-check-label small fw-bold" for="lf_important"><i class="fas fa-star text-warning me-1"></i>Mark as important</label>
        </div>
        <div class="form-check m-0">
            <input class="form-check-input" type="checkbox" name="lead_escalate" value="1" id="lf_escalate">
            <label class="form-check-label small fw-bold" for="lf_escalate"><i class="fas fa-level-up-alt text-danger me-1"></i>Escalate to my team lead{{ auth()->user()->manager_id ? ' (' . (\App\Models\Admin::find(auth()->user()->manager_id)->name ?? 'manager') . ')' : '' }}</label>
        </div>
    </div>
    <div class="lf-open small text-danger mt-2" style="display:none"></div>
    <div class="lf-reason mt-2" style="display:none">
        <textarea name="escalation_reason" id="lf_reason" class="form-control form-control-sm" rows="2" maxlength="500" placeholder="Why does the team lead need to step in? e.g. parent wants to talk to a senior, fee discount asked"></textarea>
    </div>
</div>

@once
@push('js')
<script>
(function () {
    var url = @json(route('admin.calling.lead-flags', ['customer' => '__ID__']));
    function reset() {
        $('#lf_important, #lf_important0').prop('disabled', true);
        $('#lf_important').prop('checked', false);
        $('#lf_escalate').prop('checked', false).prop('disabled', false);
        $('#lf_reason').val('').prop('required', false);
        $('.lf-reason').hide();
        $('.lf-open').hide().text('');
    }
    $(document).on('change', '#lf_escalate', function () {
        $('.lf-reason').toggle(this.checked);
        $('#lf_reason').prop('required', this.checked);
        if (this.checked) $('#lf_reason').trigger('focus');
    });
    $(document).on('click', '.open-calling-modal', function () {
        reset();
        var id = $(this).data('id');
        if (!id) return;
        $.getJSON(url.replace('__ID__', id), function (f) {
            if ($('#customer_id').val() != id) return;   // another lead was opened meanwhile
            $('#lf_important, #lf_important0').prop('disabled', false);
            $('#lf_important').prop('checked', !!f.important);
            if (f.escalation) {
                $('#lf_escalate').prop('disabled', true);
                $('.lf-open').text('Already escalated to ' + f.escalation.to + ' by ' + f.escalation.by + ' (' + f.escalation.at + '): ' + f.escalation.reason).show();
            }
        });
    });
})();
</script>
@endpush
@endonce
