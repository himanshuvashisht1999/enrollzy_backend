{{-- Invoice form (create / edit): what an invoice to the chosen organisation or campus will print under "Billed To",
     from Billing → Organisation Billing (BillingOrganisationController::billedTo), with a link to add what is missing. --}}
<div class="row mb-4 d-none" id="billedToPreview">
    <div class="col-12">
        <div class="p-3 bg-light border rounded">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary">Billed To</span>
                        <h6 class="mb-0 text-primary fw-bold" id="btTitle"></h6>
                        <span class="badge bg-secondary-subtle text-secondary border" id="btSource" style="font-size:11px"></span>
                    </div>
                    <div class="text-muted small" id="btBody"></div>
                </div>
                <div class="text-end small" id="btTax"></div>
            </div>
            <div class="small mt-2" id="btEdit"></div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var seq = 0, base = @json(url('admin/billing/organisations'));
        var esc = function (s) { return $('<div>').text(s == null ? '' : s).html(); };
        function refresh() {
            var $box = $('#billedToPreview'), org = $('#organisation_id').val(), campus = $('#campus_id').val();
            if ($('input[name="client_type"]:checked').val() !== 'organisation' || !org) { $box.addClass('d-none'); return; }
            var mine = ++seq;
            fetch(base + '/' + org + '/billed-to' + (campus ? '?campus=' + encodeURIComponent(campus) : ''), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (mine !== seq) return; // an older answer, the choice has changed since
                    if (d.needs_campus) { $box.addClass('d-none'); return; }
                    $('#btTitle').text(d.title);
                    $('#btSource').text(d.source === 'campus' ? 'Campus details' : d.source === 'organisation' ? 'Organisation details' : 'No billing details');
                    var lines = [];
                    if (d.legal_name) lines.push('<span class="text-dark">' + esc(d.legal_name) + '</span>');
                    if (d.attn) lines.push('<strong>Attn:</strong> ' + esc(d.attn));
                    if (d.address || d.location) lines.push(esc([d.address, d.location].filter(Boolean).join(', ')));
                    var contact = [];
                    if (d.email) contact.push('<strong>Email:</strong> ' + esc(d.email));
                    if (d.phone) contact.push('<strong>Phone:</strong> ' + esc(d.phone));
                    if (contact.length) lines.push(contact.join(' | '));
                    $('#btBody').html(lines.join('<br>') || 'No address or contact saved');
                    $('#btTax').html([['gstin', 'GSTIN'], ['tan', 'TAN'], ['pan', 'PAN'], ['cin', 'CIN']].filter(function (t) { return d[t[0]]; })
                        .map(function (t) { return '<div><strong>' + t[1] + ':</strong> <span class="font-monospace">' + esc(d[t[0]]) + '</span></div>'; }).join(''));
                    $('#btEdit').html(d.source === 'none'
                        ? '<span class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>No GSTIN or billing address saved for this ' + (campus ? 'campus' : 'organisation') + '.</span> <a href="' + d.edit_url + '" target="_blank">Add billing details</a> (opens in new tab)'
                        : '<a href="' + d.edit_url + '" target="_blank" class="text-decoration-none"><i class="fas fa-edit me-1"></i>Edit billing details</a> (opens in new tab)');
                    $box.removeClass('d-none');
                })
                .catch(function () { if (mine === seq) $box.addClass('d-none'); });
        }
        $('#organisation_id, #campus_id').on('change', refresh);
        $('input[name="client_type"]').on('change', refresh);
        // back from the billing details tab: show what was just saved
        window.addEventListener('focus', function () { if (!$('#billedToPreview').hasClass('d-none') || $('#organisation_id').val()) refresh(); });
        refresh();
    });
</script>
