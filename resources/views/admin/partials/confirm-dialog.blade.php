{{-- One in-page confirmation dialog for the whole admin, used instead of the browser's confirm() pop-up.
     Put data-confirm="Question?" on a <form> (or on the submit <button>, or on a link). Clicking shows this dialog; only
     "Yes" submits / follows. Optional: data-confirm-label="Yes, delete" and data-confirm-style="danger|warning|primary".
     Without data-confirm-style, questions about deleting or removing get a red button. --}}
<div class="modal fade" id="appConfirmDialog" tabindex="-1" aria-labelledby="appConfirmTitle" aria-hidden="true"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title" id="appConfirmTitle">Please confirm</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
    <div class="modal-body"><p class="mb-0" id="appConfirmText" style="white-space: pre-line"></p></div>
    <div class="modal-footer">
        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="appConfirmYes">Yes, continue</button>
    </div>
</div></div></div>
<script>
    (function () {
        let onYes = null;
        const el = () => document.getElementById('appConfirmDialog');
        function ask(source, text, run) {
            const danger = /\b(delete|remove|reset|cancel|undo|discard)\b/i.test(text);
            const style = source.dataset.confirmStyle || (danger ? 'danger' : 'primary');
            const yes = document.getElementById('appConfirmYes');
            yes.className = 'btn btn-' + style;
            yes.textContent = source.dataset.confirmLabel || (/\b(delete|remove)\b/i.test(text) ? 'Yes, delete' : 'Yes, continue');
            yes.dataset.confirmFor = text;
            document.getElementById('appConfirmText').textContent = text;
            onYes = run;
            bootstrap.Modal.getOrCreateInstance(el()).show();
        }
        // For scripts: appConfirm('Question?', function () { ...do it... }, { label: 'Yes, reset', style: 'warning' })
        window.appConfirm = function (text, run, opts) {
            opts = opts || {};
            ask({ dataset: { confirmLabel: opts.label || '', confirmStyle: opts.style || '' } }, text, run);
        };
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('appConfirmYes').addEventListener('click', function () {
                const run = onYes; onYes = null;
                bootstrap.Modal.getOrCreateInstance(el()).hide();
                if (run) run();
            });
        });
        // Forms: the question can sit on the form or on the button that submits it (including <button form="...">).
        document.addEventListener('submit', function (e) {
            const form = e.target, button = e.submitter;
            const text = (button && button.dataset.confirm) || form.dataset.confirm;
            if (!text || form.dataset.confirmed === '1') return;
            e.preventDefault();
            e.stopImmediatePropagation();
            ask(button && button.dataset.confirm ? button : form, text, function () {
                form.dataset.confirmed = '1';
                if (button && form.requestSubmit) form.requestSubmit(button); else form.submit();
                setTimeout(() => { delete form.dataset.confirmed; }, 0);
            });
        }, true);
        // Links (GET actions).
        document.addEventListener('click', function (e) {
            const link = e.target.closest('a[data-confirm]');
            if (!link) return;
            e.preventDefault();
            ask(link, link.dataset.confirm, function () { window.location.href = link.href; });
        }, true);
    })();
</script>
