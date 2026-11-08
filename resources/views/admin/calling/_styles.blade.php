{{-- The command center's look (design: enrollzy-calling-command-center), scoped to .cc so the CRM's own styles are untouched. --}}
@once
@push('css')
<style>
.cc{--bg:#f4f6fb;--ink:#0f172a;--muted:#64748b;--line:#e5e9f2;--soft:#f8fafc;--pri:#2563eb;--pri-soft:#e8efff;--ok:#16a34a;--ok-soft:#e7f7ed;--warn:#d97706;--warn-soft:#fff4e0;--bad:#dc2626;--bad-soft:#fdecec;--vio:#7c3aed;--vio-soft:#f1eafe;--sh:0 1px 2px rgba(15,23,42,.05),0 4px 14px rgba(15,23,42,.05);color:var(--ink);font-size:14px;line-height:1.45}
.cc a{color:var(--pri);text-decoration:none}.cc a:hover{text-decoration:underline}
.cc .top{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:16px}
.cc .top h1{font-size:20px;margin:0;font-weight:700}.cc .top .sub{color:var(--muted);font-size:12.5px}
.cc .grow{flex:1}
.cc .clock{font-variant-numeric:tabular-nums;font-weight:600;background:#fff;border:1px solid var(--line);border-radius:8px;padding:6px 10px;font-size:13px}
.cc .bell{position:relative;border:1px solid var(--line);background:#fff;border-radius:8px;padding:6px 10px;color:var(--ink)!important}
.cc .bell b{position:absolute;top:-7px;right:-7px;background:var(--bad);color:#fff;border-radius:999px;font-size:10px;padding:1px 6px}
.cc .grid{display:grid;gap:14px}
.cc .g2{grid-template-columns:repeat(2,minmax(0,1fr))}.cc .g3{grid-template-columns:repeat(3,minmax(0,1fr))}.cc .g4{grid-template-columns:repeat(4,minmax(0,1fr))}.cc .g5{grid-template-columns:repeat(5,minmax(0,1fr))}.cc .g6{grid-template-columns:repeat(6,minmax(0,1fr))}
@media (max-width:1300px){.cc .g6{grid-template-columns:repeat(3,minmax(0,1fr))}.cc .g5{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media (max-width:1100px){.cc .g3,.cc .g4{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (max-width:820px){.cc .g2,.cc .g3,.cc .g4,.cc .g5,.cc .g6{grid-template-columns:minmax(0,1fr)}}
.cc .mb14{margin-bottom:14px}
.cc .card{background:#fff;border:1px solid var(--line);border-radius:12px;box-shadow:var(--sh)}
.cc .card.p{padding:16px}
.cc .card h3{margin:0;font-size:14.5px;font-weight:700;display:flex;align-items:center;gap:8px}
.cc .card .hd{display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid var(--line);flex-wrap:wrap}
.cc .card .bd{padding:14px 16px}
.cc .muted{color:var(--muted)}.cc .sm{font-size:12px}.cc .xs{font-size:11px}
.cc .kpi{border-top:3px solid var(--pri)}.cc .kpi.ok{border-top-color:var(--ok)}.cc .kpi.warn{border-top-color:var(--warn)}.cc .kpi.bad{border-top-color:var(--bad)}.cc .kpi.vio{border-top-color:var(--vio)}
.cc .kpi .l{font-size:11px;letter-spacing:.05em;text-transform:uppercase;color:var(--muted);font-weight:600}
.cc .kpi .v{font-size:26px;font-weight:700;margin-top:4px;font-variant-numeric:tabular-nums;line-height:1.2}
.cc .kpi .s{font-size:12px;color:var(--muted)}
.cc .big{font-size:24px;font-weight:700;font-variant-numeric:tabular-nums}
.cc .btn{border:1px solid var(--line);background:#fff;border-radius:8px;padding:7px 12px;font-weight:500;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;color:var(--ink)!important;font-size:13.5px;cursor:pointer}
.cc .btn:hover{background:var(--soft);text-decoration:none}
.cc .btn.pri{background:var(--pri);border-color:var(--pri);color:#fff!important}.cc .btn.pri:hover{background:#1d4ed8}
.cc .btn.ok{background:var(--ok);border-color:var(--ok);color:#fff!important}.cc .btn.bad{background:var(--bad);border-color:var(--bad);color:#fff!important}
.cc .btn.sm{padding:4px 9px;font-size:12px}.cc .btn.ghost{border-color:transparent;background:transparent}
.cc .pill{display:inline-flex;align-items:center;gap:5px;padding:2px 9px;border-radius:999px;font-size:11.5px;font-weight:600;white-space:nowrap}
.cc .pill .dot{width:7px;height:7px;border-radius:50%;background:currentColor}
.cc .st-calling,.cc .st-ok{background:var(--ok-soft);color:var(--ok)}.cc .st-idle,.cc .st-warn{background:var(--warn-soft);color:var(--warn)}.cc .st-break{background:var(--vio-soft);color:var(--vio)}.cc .st-offline{background:#eef0f4;color:#64748b}.cc .st-info{background:var(--pri-soft);color:var(--pri)}.cc .st-bad{background:var(--bad-soft);color:var(--bad)}
.cc .pr-urgent{background:var(--bad-soft);color:var(--bad)}.cc .pr-important{background:var(--warn-soft);color:var(--warn)}.cc .pr-normal{background:#eef0f4;color:#475569}
.cc .blink .dot{animation:ccbl 1.2s infinite}@keyframes ccbl{50%{opacity:.25}}
.cc table{width:100%;border-collapse:collapse;margin:0}
.cc th,.cc td{text-align:left;padding:9px 10px;border-bottom:1px solid var(--line);vertical-align:middle}
.cc th{font-size:11px;letter-spacing:.05em;text-transform:uppercase;color:var(--muted);font-weight:600;background:var(--soft)}
.cc tbody tr:hover{background:#fafbff}.cc tr.total td{background:var(--soft);font-weight:700}
.cc td.n,.cc th.n{text-align:right;font-variant-numeric:tabular-nums}
.cc .tbl{overflow:auto}
.cc .av{width:38px;height:38px;border-radius:50%;display:grid;place-items:center;color:#fff;font-weight:700;flex:none;font-size:14px}
.cc .av.sm{width:28px;height:28px;font-size:11.5px}
.cc .agent{padding:14px;display:flex;flex-direction:column;gap:12px}
.cc .agent .head{display:flex;gap:10px;align-items:center}
.cc .agent .nm{font-weight:700}
.cc .agent .cur{background:var(--soft);border:1px dashed var(--line);border-radius:10px;padding:9px 11px;min-height:54px}
.cc .mini{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;text-align:center}
.cc .mini div{background:var(--soft);border-radius:8px;padding:6px 2px}
.cc .mini b{display:block;font-size:16px;font-variant-numeric:tabular-nums}.cc .mini span{font-size:10.5px;color:var(--muted)}
.cc .bar{height:8px;background:#edf0f6;border-radius:99px;overflow:hidden}
.cc .bar i{display:block;height:100%;background:var(--pri);border-radius:99px}
.cc .bar.ok i{background:var(--ok)}.cc .bar.warn i{background:var(--warn)}.cc .bar.bad i{background:var(--bad)}.cc .bar.lg{height:12px}
.cc .row{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin:0;--bs-gutter-x:0;--bs-gutter-y:0}.cc .sp{justify-content:space-between}
.cc .row>*{width:auto;max-width:none;margin-top:0;flex-shrink:1}   /* undo Bootstrap's .row column sizing */
.cc .feed{max-height:420px;overflow:auto}
.cc .feed .it{display:flex;gap:10px;padding:9px 0;border-bottom:1px solid var(--line);font-size:13px}.cc .feed .it:last-child{border-bottom:0}
.cc .feed .t{color:var(--muted);font-size:11.5px;min-width:52px;font-variant-numeric:tabular-nums}
.cc .alert{display:flex;gap:10px;padding:10px 12px;border-radius:10px;margin-bottom:8px;font-size:13px;align-items:center}
.cc .alert.warn{background:var(--warn-soft);color:#92400e}.cc .alert.bad{background:var(--bad-soft);color:#991b1b}.cc .alert.info{background:var(--pri-soft);color:#1e40af}.cc .alert.ok{background:var(--ok-soft);color:#166534}
.cc .empty{color:var(--muted);text-align:center;padding:22px 8px;font-size:13px}
.cc .target{background:linear-gradient(180deg,#f5f8ff,#fff);border-color:#dbe6ff}
.cc .notice{border:1px solid var(--line);border-radius:10px;padding:10px 12px;margin-bottom:10px;background:#fff}
.cc .notice.unread{border-left:3px solid var(--warn);background:#fffaf0}.cc .notice.pinned{border-color:#cdd9f5}
.cc .notice .body{margin:5px 0;white-space:pre-wrap;font-size:13px}
.cc .yes{color:var(--ok);font-weight:600}.cc .no{color:var(--bad);font-weight:600}.cc .part{color:var(--warn);font-weight:600}
.cc .legend{display:flex;gap:12px;flex-wrap:wrap;font-size:12px;color:var(--muted)}.cc .legend i{display:inline-block;width:10px;height:10px;border-radius:3px;margin-right:5px;vertical-align:-1px}
.cc .funnel .fr{display:grid;grid-template-columns:170px 1fr 60px;gap:10px;align-items:center;padding:5px 0;font-size:13px}
.cc .funnel .fb{height:22px;border-radius:6px;background:linear-gradient(90deg,#3b82f6,#7c3aed)}
.cc .seg{display:inline-flex;border:1px solid var(--line);border-radius:8px;overflow:hidden;background:#fff}
.cc .seg a{padding:6px 11px;font-size:12.5px;color:var(--muted)!important;border-right:1px solid var(--line)}.cc .seg a:last-child{border-right:0}
.cc .seg a.on{background:var(--pri);color:#fff!important}.cc .seg a:hover{text-decoration:none}
</style>
@endpush
@endonce
