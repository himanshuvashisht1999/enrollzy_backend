<?php

namespace App\Http\Controllers\Admin\Calling;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\NoticeAck;
use App\Support\Calling\CommandCenter;
use App\Support\Calling\Notices;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Notice Board: announcements for the calling team, with acknowledgement and read receipts (App\Support\Calling\Notices). */
class NoticeController extends Controller
{
    public function __construct(private Notices $notices, private CommandCenter $cc)
    {
    }

    public function index(Request $request)
    {
        $u = $request->user();
        abort_unless($this->cc->role($u), 403, 'The Notice Board is for the calling team.');
        return view('admin.calling.notices', [
            'list' => $this->notices->visible($u, $request->boolean('expired')), 'n' => $this->notices,
            'audiences' => $this->notices->audiences($u), 'canPost' => $this->notices->canPost($u), 'showExpired' => $request->boolean('expired'),
        ]);
    }

    public function store(Request $request)
    {
        $u = $request->user();
        abort_unless($this->notices->canPost($u), 403);
        $data = $request->validate([
            'audience' => ['required', Rule::in(array_keys($this->notices->audiences($u)))],
            'title' => 'required|string|max:200', 'body' => 'required|string|max:5000',
            'priority' => ['required', Rule::in(array_keys(Notice::PRIORITIES))],
            'expires_on' => 'nullable|date|after_or_equal:today',
        ], [], ['body' => 'message']);
        [$type, $id] = array_pad(explode(':', $data['audience']), 2, null);
        Notice::create(['posted_by' => $u->id, 'audience' => $type, 'audience_id' => $id ? (int) $id : null,
            'title' => $data['title'], 'body' => $data['body'], 'priority' => $data['priority'],
            'pinned' => $request->boolean('pinned'), 'require_ack' => $request->boolean('require_ack'), 'expires_on' => $data['expires_on'] ?? null]);

        return redirect()->route('admin.calling.notices.index')->with('success', 'Notice posted.');
    }

    public function ack(Request $request, Notice $notice)
    {
        $u = $request->user();
        abort_unless(in_array($u->id, $this->notices->recipients($notice), true), 403);
        NoticeAck::firstOrCreate(['notice_id' => $notice->id, 'staff_id' => $u->id], ['acked_at' => now()]);
        return $request->expectsJson() ? response()->json(['ok' => true, 'left' => $this->notices->unacked($u)->count()]) : back()->with('success', 'Acknowledged.');
    }

    /** who has acknowledged (poster and admins) */
    public function receipts(Request $request, Notice $notice)
    {
        abort_unless($this->notices->canManage($request->user(), $notice), 403);
        $acks = $notice->acks()->pluck('acked_at', 'staff_id');
        return view('admin.calling._receipts', ['notice' => $notice, 'rows' => collect($this->notices->recipients($notice))
            ->map(fn ($id) => ['p' => $this->cc->person($id), 'at' => $acks[$id] ?? null])->sortBy(fn ($r) => $r['at'] ? 1 : 0)->values()]);
    }

    public function destroy(Request $request, Notice $notice)
    {
        abort_unless($this->notices->canManage($request->user(), $notice), 403);
        $notice->delete();
        return back()->with('success', 'Notice removed.');
    }
}
