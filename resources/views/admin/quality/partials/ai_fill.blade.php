{{-- Create / Missing Info / Update of rankings, accreditations and approvals by Gemini or Claude. Included on the Quality record page. --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3 d-flex flex-wrap align-items-center gap-2">
        <div class="me-auto">
            <div class="fw-bold"><i class="fas fa-robot me-1 text-primary"></i> Fill by AI</div>
            <div class="small text-muted">@if($type === 'campus')Statutory approvals of this campus (AICTE, NMC, PCI, BCI...). Rankings and accreditations belong to the organisation and show here from it.@else Rankings, accreditations and statutory approvals of this {{ strtolower(\App\Support\Quality\Levels::LABELS[$type]) }}.@endif You review every row before it is saved; saved rows stay unverified.</div>
        </div>
    </div>
    <div class="card-body pt-0 pb-3 d-flex flex-wrap gap-3">
        {{-- per action: Gemini, Claude, or both in turn (the second AI only finishes what the first could not) --}}
        @foreach(\App\Services\Quality\QualityAiService::ACTIONS as $act => $actLabel)
            <div>
                <div class="small fw-bold text-muted mb-1">{{ $actLabel }}</div>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary qa-open" data-action="{{ $act }}" data-engine="gemini" title="{{ $actLabel }} by Gemini"><i class="fas fa-robot me-1"></i>Gemini</button>
                    <button type="button" class="btn text-white qa-open" style="background:#c96442;border-color:#c96442" data-action="{{ $act }}" data-engine="claude" title="{{ $actLabel }} by Claude"><i class="fas fa-asterisk me-1"></i>Claude</button>
                    <button type="button" class="btn btn-outline-secondary qa-open" data-action="{{ $act }}" data-engine="claude_gemini" title="Claude first; Gemini finishes what Claude could not">Claude + Gemini</button>
                    <button type="button" class="btn btn-outline-secondary qa-open" data-action="{{ $act }}" data-engine="gemini_claude" title="Gemini first; Claude finishes what Gemini could not">Gemini + Claude</button>
                </div>
            </div>
        @endforeach
    </div>
    {{-- what earlier runs found, kept for reference (CRM only, never on the website) so the same research is not run again --}}
    @php $qaLogs = \App\Services\Quality\QualityAiService::logs($type, (string) $entity->getKey()); @endphp
    <div class="card-body pt-0 pb-3">
        <div class="border-top pt-2">
            <a class="small fw-bold text-decoration-none" data-bs-toggle="collapse" href="#qaLogsBox" role="button">
                <i class="fas fa-history me-1"></i>Past AI results (for reference) <span class="badge bg-light text-dark border" id="qaLogsCount">{{ count($qaLogs) }}</span>
            </a>
            <div class="collapse {{ count($qaLogs) ? 'show' : '' }}" id="qaLogsBox">
                <div class="small text-muted mt-1">Saved automatically after every run, so you can read what the AI found without running it again. Shown here only, never on the website.</div>
                <div id="qaLogs" class="mt-2"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="qaModal" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" id="qaTitle">Fill by AI</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        {{-- step 1: settings and the master command --}}
        <div id="qaStepSetup">
            <div id="qaLastRun" class="alert alert-warning py-2 small d-none"></div>
            <div class="row g-3 mb-2">
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Official website</label>
                    <input type="url" class="form-control form-control-sm" id="qaUrl" value="{{ app(\App\Services\Quality\QualityAiService::class)->defaultUrl($entity, $type) }}" placeholder="https://...">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small d-block">Look for</label>
                    @foreach(\App\Services\Quality\QualityAiService::kindsFor($type) as $k => $kLabel)
                        <div class="form-check form-check-inline small"><input class="form-check-input qa-kind" type="checkbox" value="{{ $k }}" id="qaKind_{{ $k }}" checked><label class="form-check-label" for="qaKind_{{ $k }}">{{ $kLabel }}</label></div>
                    @endforeach
                    <div class="form-check form-switch small mt-1"><input class="form-check-input" type="checkbox" id="qaSearch"><label class="form-check-label" for="qaSearch">Google search <span class="text-muted">(off: no Google; the AI opens only the bodies' official websites - nirfindia.org, naac.gov.in, nbaind.org, aicte-india.org, ugc.gov.in...)</span></label></div>
                </div>
            </div>
            <label class="form-label fw-bold small mb-1">Master command <span class="text-muted fw-normal">(updates by itself when you change the settings above; you can edit it before running)</span></label>
            <textarea id="qaCommand" class="form-control font-monospace small bg-light" rows="14" placeholder="Building the command..."></textarea>
            <div class="small text-muted mt-1" id="qaCommandInfo"></div>
        </div>

        {{-- step 2: running --}}
        <div id="qaStepRunning" class="text-center py-5 d-none">
            <div class="spinner-border text-primary mb-3"></div>
            <div class="fw-bold" id="qaRunningText">Researching...</div>
            <div class="small text-muted">This can take 1 to 4 minutes. Please keep this window open.</div>
        </div>

        {{-- step 3: review --}}
        <div id="qaStepReview" class="d-none">
            <div class="alert alert-info py-2 small mb-3" id="qaSummary"></div>
            <div id="qaTables"></div>
            <div id="qaNotFound" class="small text-muted"></div>
            <div id="qaSaveResults" class="mt-3"></div>
        </div>
    </div>
    <div class="modal-footer">
        <span class="small text-muted me-auto" id="qaFooterNote"></span>
        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-outline-secondary d-none" id="qaBack"><i class="fas fa-arrow-left me-1"></i>Back to command</button>
        <button type="button" class="btn btn-primary" id="qaRun"><i class="fas fa-play me-1"></i>Run</button>
        <button type="button" class="btn btn-success d-none" id="qaSave"><i class="fas fa-save me-1"></i>Save ticked rows</button>
    </div>
</div></div></div>

@push('scripts')
<script>
(function () {
    const urls = {
        command: @json(route('admin.quality.ai.command', [$type, $entity->getKey()])),
        run: @json(route('admin.quality.ai.run', [$type, $entity->getKey()])),
        save: @json(route('admin.quality.ai.save', [$type, $entity->getKey()])),
        log: @json(route('admin.quality.ai.logs.store', [$type, $entity->getKey()])),
        logDelete: {!! json_encode(route('admin.quality.ai.logs.destroy', ['type' => $type, 'id' => $entity->getKey(), 'log' => '__ID__'])) !!},
    };
    const csrf = @json(csrf_token());
    const actionLabels = @json(\App\Services\Quality\QualityAiService::ACTIONS);
    const kindLabels = @json(\App\Services\Quality\QualityAiService::kindsFor($type));
    const $ = id => document.getElementById(id);
    const esc = s => String(s ?? '').replace(/[&<>"]/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;'}[c]));
    let state = {action: 'create', engine: 'gemini', proposals: null};
    const engineNames = {gemini: 'Gemini', claude: 'Claude', claude_gemini: 'Claude + Gemini', gemini_claude: 'Gemini + Claude'};
    const engineName = e => engineNames[e] || e;
    let modal = null;

    function show(step) {
        ['qaStepSetup', 'qaStepRunning', 'qaStepReview'].forEach(s => $(s).classList.toggle('d-none', s !== step));
        $('qaRun').classList.toggle('d-none', step !== 'qaStepSetup');
        $('qaSave').classList.toggle('d-none', step !== 'qaStepReview');
        $('qaBack').classList.toggle('d-none', step !== 'qaStepReview');
    }
    function settings() {
        return {
            action: state.action, engine: state.engine, url: $('qaUrl').value.trim() || null,
            kinds: [...document.querySelectorAll('.qa-kind:checked')].map(c => c.value),
            search_google: $('qaSearch').checked ? 1 : 0,
        };
    }
    async function post(url, body) {
        const r = await fetch(url, {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest'}, body: JSON.stringify(body)});
        let j = {};
        try { j = await r.json(); } catch (e) { j = {message: r.status === 524 || r.status === 504 ? 'The request took too long and the server stopped waiting. Try again, or untick some record types.' : 'Unexpected reply (' + r.status + ').'}; }
        if (!r.ok || j.success === false) throw new Error(j.message || (j.errors ? Object.values(j.errors).flat().join(' ') : 'Request failed (' + r.status + ').'));
        return j;
    }
    // The command follows the settings by itself. Once the admin edits it by hand, it is kept (and sent as edited) until the pop-up is reopened.
    let autoCommand = '', edited = false, buildNo = 0;
    async function buildCommand() {
        const s = settings();
        if (!s.kinds.length) { $('qaCommandInfo').textContent = 'Tick at least one record type.'; return; }
        const mine = ++buildNo;
        $('qaCommand').value = ''; $('qaCommand').placeholder = 'Building the command (reading the website)...';
        $('qaCommandInfo').textContent = '';
        try {
            const j = await post(urls.command, s);
            if (mine !== buildNo) return;   // a newer setting change is already building
            autoCommand = j.command; edited = false;
            $('qaCommand').value = j.command;
            if (j.url && !$('qaUrl').value) $('qaUrl').value = j.url;
            $('qaCommandInfo').textContent = j.chars.toLocaleString() + ' characters.';
        } catch (e) { if (mine === buildNo) $('qaCommandInfo').textContent = 'Could not build the command: ' + e.message; }
    }
    function settingsChanged() {
        if (edited) { $('qaCommandInfo').textContent = 'You edited the command, so it was not rebuilt for the new settings (reopen the pop-up to start fresh).'; return; }
        buildCommand();
    }
    $('qaCommand').addEventListener('input', () => { edited = $('qaCommand').value !== autoCommand; });
    $('qaUrl').addEventListener('change', settingsChanged);
    $('qaSearch').addEventListener('change', settingsChanged);
    document.querySelectorAll('.qa-kind').forEach(c => c.addEventListener('change', settingsChanged));

    // ---- past AI results (kept for reference; CRM only)
    let logs = @json($qaLogs);
    const stateText = {new: 'New', update: 'Update', same: 'Already recorded'};
    function renderLogs() {
        $('qaLogsCount').textContent = logs.length;
        $('qaLogs').innerHTML = logs.length ? logs.map(l => '<div class="border rounded-3 p-2 mb-2 small" data-log="' + l.id + '">'
            + '<div class="d-flex flex-wrap gap-2 align-items-center">'
            + '<strong>' + esc(l.when) + '</strong>'
            + '<span class="badge bg-primary-subtle text-primary">' + esc(l.action) + ' by ' + esc(l.engine) + '</span>'
            + (l.kinds.length ? '<span class="text-muted">' + esc(l.kinds.join(', ')) + '</span>' : '')
            + (l.by ? '<span class="text-muted">· run by ' + esc(l.by) + '</span>' : '')
            + '<button type="button" class="btn btn-link btn-sm text-danger p-0 ms-auto qa-log-del" data-id="' + l.id + '">Remove</button></div>'
            + (l.notes.length ? '<div class="mt-1"><span class="fw-bold">AI notes:</span><ul class="mb-1 ps-3">' + l.notes.map(n => '<li>' + esc(n) + '</li>').join('') + '</ul></div>' : '')
            + (l.rows.length ? '<div class="mt-1"><span class="fw-bold">Found (' + l.rows.length + '):</span><ul class="mb-1 ps-3">' + l.rows.map(r => '<li>' + esc(r.text || '')
                + (r.state ? ' <span class="badge bg-light text-dark border">' + esc(stateText[r.state] || r.state) + '</span>' : '')
                + (r.found_by ? ' <span class="text-muted">by ' + esc(r.found_by) + '</span>' : '')
                + (r.source_url ? ' <a href="' + esc(r.source_url) + '" target="_blank" rel="noopener">source</a>' : '')
                + (r.notes ? '<div class="text-muted">' + esc(r.notes) + '</div>' : '') + '</li>').join('') + '</ul></div>'
              : '<div class="mt-1 text-muted">No records found in this run.</div>')
            + (l.sources.length ? '<div class="text-muted">Checked: ' + l.sources.map(esc).join(', ') + '</div>' : '')
            + '</div>').join('') : '<div class="small text-muted">No AI runs saved for this record yet.</div>';
    }
    renderLogs();
    $('qaLogs').addEventListener('click', async e => {
        const b = e.target.closest('.qa-log-del');
        if (!b || !confirm('Remove this saved AI result from the page?')) return;
        try {
            await post(urls.logDelete.replace('__ID__', b.dataset.id), {});
            logs = logs.filter(l => String(l.id) !== b.dataset.id);
            renderLogs();
        } catch (err) { alert(err.message); }
    });
    // keeps a finished run (both AIs of a combination together) on the page
    async function saveLog(s, j) {
        const rows = [];
        Object.keys(kindLabels).forEach(kind => ((j.proposals || {})[kind] || []).forEach(r => rows.push({
            kind, text: kindLabels[kind] + ': ' + describe[kind](r).replace(/<[^>]+>/g, '').replace(/&amp;/g, '&'), notes: r.notes || null,
            state: r.problem ? 'Cannot save: ' + String(r.problem).slice(0, 40) : (r.state || null), found_by: r.found_by || null, source_url: r.source_url || null,
        })));
        try {
            const res = await post(urls.log, {action: state.action, engine: state.engine, kinds: s.kinds, steps: j.steps || [], notes: j.not_found || [], sources: j.sources_checked || [], rows});
            logs.unshift(res.log);
            renderLogs();
            $('qaSummary').insertAdjacentHTML('beforeend', '<div class="mt-1 text-success">This result is saved under "Past AI results" on the page, so you don\'t need to run it again.</div>');
        } catch (e) {
            $('qaSummary').insertAdjacentHTML('beforeend', '<div class="mt-1 text-danger">Could not keep this result on the page: ' + esc(e.message) + '</div>');
        }
    }

    document.querySelectorAll('.qa-open').forEach(b => b.addEventListener('click', () => {
        // a reminder of the last run before running again
        const last = logs[0];
        $('qaLastRun').classList.toggle('d-none', !last);
        if (last) $('qaLastRun').innerHTML = '<strong>Already run here on ' + esc(last.when) + '</strong> (' + esc(last.action) + ' by ' + esc(last.engine) + '): '
            + last.rows.length + ' record(s) found' + (last.notes.length ? '; note: ' + esc(last.notes[0].slice(0, 300).replace(/\.+$/, '')) + (last.notes.length > 1 ? ' (+' + (last.notes.length - 1) + ' more)' : '') : '')
            + '. See "Past AI results" on the page before running again.';
        state = {action: b.dataset.action, engine: b.dataset.engine, proposals: null};
        $('qaTitle').textContent = actionLabels[state.action] + ' by ' + engineName(state.engine);
        $('qaRun').innerHTML = '<i class="fas fa-play me-1"></i>Run ' + actionLabels[state.action] + ' by ' + engineName(state.engine);
        $('qaFooterNote').textContent = state.engine.includes('_')
            ? engineName(state.engine.split('_')[0]) + ' runs first; ' + engineName(state.engine.split('_')[1]) + ' runs only if something is left. ' + (state.engine.includes('claude') ? 'Claude uses your Claude API credit.' : '')
            : (state.engine === 'claude' ? 'Uses your Claude API credit.' : '');
        $('qaSaveResults').innerHTML = '';
        show('qaStepSetup');
        modal = modal || new bootstrap.Modal($('qaModal'));
        modal.show();
        // every opening starts from the defaults: search off, all record types
        $('qaSearch').checked = false;
        document.querySelectorAll('.qa-kind').forEach(c => { c.checked = true; });
        edited = false;
        buildCommand();
    }));

    $('qaRun').addEventListener('click', async () => {
        const s = settings();
        if (!s.kinds.length) { $('qaCommandInfo').textContent = 'Tick at least one record type.'; return; }
        // unedited: the server builds the command fresh from these settings; edited: the admin's text is used as is
        s.command = edited ? ($('qaCommand').value.trim() || null) : null;
        const what = s.kinds.map(k => kindLabels[k].toLowerCase()).join(', ');
        const [first, second] = state.engine.split('_');
        const usage = {input_tokens: 0, output_tokens: 0, web_searches: 0, pages_opened: 0};
        const addUsage = u => Object.keys(usage).forEach(k => { usage[k] += Number((u || {})[k] || 0); });
        const steps = [];
        show('qaStepRunning');
        let j = null;
        try {
            // step 1: the first (or only) AI
            $('qaRunningText').textContent = engineName(first) + ' is researching ' + what + '...';
            try {
                j = await post(urls.run, Object.assign({}, s, {engine: first}));
                addUsage(j.usage);
                steps.push(engineName(first) + ' finished.');
            } catch (e) {
                if (!second) throw e;
                steps.push(engineName(first) + ' failed (' + e.message.slice(0, 160) + ').');
            }
            // step 2 ("Claude + Gemini" / "Gemini + Claude"): only when the first AI failed or could not finish
            if (second) {
                if (!j) {
                    $('qaRunningText').textContent = engineName(first) + ' failed, so ' + engineName(second) + ' is doing the whole job...';
                    j = await post(urls.run, Object.assign({}, s, {engine: second}));
                    addUsage(j.usage);
                    steps.push(engineName(second) + ' did the whole job.');
                } else if ((j.followup_reasons || []).length) {
                    const why = j.followup_reasons.join('; ');
                    $('qaRunningText').textContent = engineName(first) + ' could not finish (' + why + '). ' + engineName(second) + ' is filling the gaps...';
                    try {
                        const j2 = await post(urls.run, Object.assign({}, s, {engine: second, previous: j.raw}));
                        addUsage(j2.usage);
                        steps.push(engineName(second) + ' filled the gaps (' + engineName(first) + ': ' + why + ').');
                        j = j2;
                    } catch (e) {
                        steps.push(engineName(second) + ' could not continue (' + e.message.slice(0, 160) + '); showing ' + engineName(first) + "'s result.");
                    }
                } else {
                    steps.push(engineName(second) + ' was not needed: ' + engineName(first) + ' finished everything.');
                }
            }
            j.usage = usage;
            j.steps = steps;
            state.proposals = j.proposals;
            render(j);
            show('qaStepReview');
            saveLog(s, j);
        } catch (e) {
            show('qaStepSetup');
            $('qaCommandInfo').innerHTML = '<span class="text-danger">' + esc(steps.concat([e.message]).join(' ')) + '</span>';
        }
    });
    $('qaBack').addEventListener('click', () => show('qaStepSetup'));

    const stateBadge = r => r.problem ? '<span class="badge bg-danger-subtle text-danger">Cannot save</span><div class="small text-danger">' + esc(r.problem) + '</div>'
        : r.state === 'new' ? '<span class="badge bg-success-subtle text-success">New</span>'
        : r.state === 'update' ? '<span class="badge bg-warning-subtle text-warning">Update #' + r.existing_id + '</span>'
        : '<span class="badge bg-secondary-subtle text-secondary">Already recorded</span>';
    const was = r => r.state === 'update' && r.existing ? '<div class="small text-muted">Now: ' + esc(Object.entries(r.existing).filter(([k, v]) => !['id', 'body', 'source_url', 'verification'].includes(k) && v !== null && v !== '').map(([k, v]) => k.replace(/_/g, ' ') + ' ' + v).join(', ')) + '</div>' : '';
    const describe = {
        rankings: r => '<strong>' + esc(r.body) + '</strong> · ' + esc(r.category) + ' · ' + esc(r.year) + ' · ' + (r.rank ? '#' + esc(r.rank) : esc(r.band || '-')) + (r.score !== null && r.score !== undefined ? ' · score ' + esc(r.score) : ''),
        accreditations: r => '<strong>' + esc(r.body) + '</strong>' + (r.grade ? ' · ' + esc(r.grade) : '') + (r.score ? ' · ' + esc(r.score) : '') + (r.cycle ? ' · cycle ' + esc(r.cycle) : '') + ' · ' + esc(r.valid_from || '?') + ' to ' + esc(r.valid_to || '?') + ' · ' + esc(r.status),
        approvals: r => '<strong>' + esc(r.body) + '</strong> · ' + esc(r.academic_year || '?') + (r.approval_number ? ' · ' + esc(r.approval_number) : '') + (r.sanctioned_intake !== null && r.sanctioned_intake !== undefined ? ' · intake ' + esc(r.sanctioned_intake) : '') + ' · ' + esc(r.status),
    };
    function render(j) {
        let html = '', total = 0, ticked = 0;
        Object.keys(kindLabels).forEach(kind => {
            const rows = (j.proposals || {})[kind] || [];
            if (!rows.length) return;
            html += '<h6 class="fw-bold mt-2">' + esc(kindLabels[kind]) + ' (' + rows.length + ')</h6><div class="table-responsive"><table class="table table-sm table-bordered align-middle small"><thead class="table-light"><tr><th style="width:34px"></th><th>Found</th><th style="width:170px">In the CRM</th><th style="width:90px">Confidence</th><th>Source</th></tr></thead><tbody>';
            rows.forEach((r, i) => {
                total++;
                const can = !r.problem && r.state !== 'same';
                const on = can && (r.state === 'new' || r.state === 'update');
                if (on) ticked++;
                html += '<tr><td class="text-center"><input type="checkbox" class="form-check-input qa-row" data-kind="' + kind + '" data-i="' + i + '" ' + (on ? 'checked' : '') + ' ' + (can ? '' : 'disabled') + '></td>'
                    + '<td>' + describe[kind](r) + (r.found_by ? ' <span class="badge bg-light text-dark border">by ' + esc(r.found_by) + '</span>' : '') + (r.notes ? '<div class="small text-muted">' + esc(r.notes) + '</div>' : '') + was(r) + '</td>'
                    + '<td>' + stateBadge(r) + '</td><td>' + esc(r.confidence || '-') + '</td>'
                    + '<td class="text-break">' + (r.source_url ? '<a href="' + esc(r.source_url) + '" target="_blank" rel="noopener">' + esc(r.source_url.replace(/^https?:\/\//, '').slice(0, 60)) + '</a>' : '<span class="text-danger">no source</span>') + '</td></tr>';
            });
            html += '</tbody></table></div>';
        });
        $('qaTables').innerHTML = html || '<div class="text-muted">Nothing was found.</div>';
        const u = j.usage || {};
        $('qaSummary').innerHTML = ((j.steps || []).length ? '<div class="mb-1">' + esc(j.steps.join(' ')) + '</div>' : '')
            + total + ' row(s) found, ' + ticked + ' ticked to save. Open the source links to check before saving.'
            + (u.input_tokens ? ' <span class="ms-2 badge bg-light text-dark border">Claude: ' + (u.input_tokens + u.output_tokens).toLocaleString() + ' tokens' + ', ' + (u.web_searches || 0) + ' Google searches, ' + (u.pages_opened || 0) + ' official pages opened</span>' : '');
        $('qaNotFound').innerHTML = (j.not_found || []).length ? '<strong>Not found:</strong><ul class="mb-0">' + j.not_found.map(n => '<li>' + esc(n) + '</li>').join('') + '</ul>' : '';
        $('qaSaveResults').innerHTML = '';
    }

    $('qaSave').addEventListener('click', async () => {
        const rows = [...document.querySelectorAll('.qa-row:checked')].map(c => {
            const r = state.proposals[c.dataset.kind][c.dataset.i];
            const label = (kindLabels[c.dataset.kind] || '') + ': ' + describe[c.dataset.kind](r).replace(/<[^>]+>/g, '');
            return {kind: c.dataset.kind, values: r.values, existing_id: r.state === 'update' ? r.existing_id : null, label};
        });
        if (!rows.length) { $('qaSaveResults').innerHTML = '<div class="text-danger small">Tick at least one row.</div>'; return; }
        $('qaSave').disabled = true;
        try {
            const j = await post(urls.save, {rows});
            $('qaSaveResults').innerHTML = '<div class="small"><strong>' + j.saved + ' saved.</strong><ul class="mb-0">' + j.results.map(r => '<li class="' + (r.result === 'skipped' ? 'text-danger' : 'text-success') + '">' + esc(r.label) + ': ' + esc(r.result) + (r.reason ? ' (' + esc(r.reason) + ')' : '') + '</li>').join('') + '</ul></div>';
            if (j.saved && j.results.every(r => r.result !== 'skipped')) setTimeout(() => location.reload(), 1500);
            else if (j.saved) $('qaSaveResults').insertAdjacentHTML('beforeend', '<button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="location.reload()">Reload the page</button>');
        } catch (e) {
            $('qaSaveResults').innerHTML = '<div class="text-danger small">' + esc(e.message) + '</div>';
        } finally { $('qaSave').disabled = false; }
    });
})();
</script>
@endpush
