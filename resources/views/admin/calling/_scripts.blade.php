{{-- Command-center behaviour: the clock, read receipts in a modal, the acknowledgement prompt for important notices,
     and (with $refresh) reloading the page every minute so the live numbers stay current. --}}
@php $prompt = $unacked->filter(fn ($n) => $n->priority !== 'normal'); @endphp
@if($prompt->count() && empty($noPrompt))
<div class="modal fade" id="ccAckModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content border-0 rounded-4">
    <div class="modal-header border-0"><h5 class="modal-title fw-bold">📌 Please acknowledge ({{ $prompt->count() }})</h5></div>
    <div class="modal-body cc">@foreach($prompt as $n)@include('admin.calling._notice', ['n' => $n, 'nb' => $notices])@endforeach</div>
    <div class="modal-footer border-0"><button type="button" class="btn btn-light border" data-bs-dismiss="modal" id="ccAckLater">Remind me later</button></div>
</div></div></div>
@endif
<div class="modal fade" id="ccReceipts" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 rounded-4">
    <div class="modal-header border-0"><h5 class="modal-title fw-bold">Read receipts</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="ccReceiptsBody"></div>
</div></div></div>
@push('scripts')
<script>
    (function () {
        var clock = document.querySelector('[data-cc-clock]');
        if (clock) setInterval(function () { var d = new Date(); clock.textContent = [d.getHours(), d.getMinutes(), d.getSeconds()].map(function (n) { return String(n).padStart(2, '0'); }).join(':'); }, 1000);
        document.addEventListener('click', function (e) {
            var b = e.target.closest('[data-receipts]'); if (!b) return;
            var body = document.getElementById('ccReceiptsBody'); body.innerHTML = '<div class="text-muted small">Loading…</div>';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('ccReceipts')).show();
            fetch(b.getAttribute('data-receipts'), { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (r) { return r.text(); }).then(function (h) { body.innerHTML = h; });
        });
        // important notices: ask once per page view, unless "remind me later" was chosen in the last 2 hours
        var ack = document.getElementById('ccAckModal'), key = 'ccAckLater';
        if (ack) {
            var later = 0; try { later = +localStorage.getItem(key) || 0; } catch (e) {}
            if (Date.now() - later > 2 * 3600 * 1000) bootstrap.Modal.getOrCreateInstance(ack).show();
            document.getElementById('ccAckLater').addEventListener('click', function () { try { localStorage.setItem(key, Date.now()); } catch (e) {} });
        }
        @if(!empty($refresh))
        // live numbers: reload every minute while the tab is visible and nothing is in use (a dialog, an opened
        // form such as an escalation comment, a shown number, a field being typed in)
        setInterval(function () {
            var busy = document.querySelector('.modal.show, .cc details[open], .cc .esc-eye.on') || (document.activeElement && document.activeElement.matches('input, textarea, select'));
            if (!document.hidden && !busy) location.reload();
        }, 60000);
        @endif
    })();
</script>
@endpush
