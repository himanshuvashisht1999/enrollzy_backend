<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommunityReply;

class CommunityReplyController extends Controller
{
    public function index(Request $request)
    {
        $query = CommunityReply::with(['user', 'question'])->latest();

        if ($request->filled('search')) {
            $query->where('content', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $replies = $query->paginate(15);

        return view('admin.community.replies.index', compact('replies'));
    }

    public function edit(CommunityReply $community_reply)
    {
        return view('admin.community.replies.edit', [
            'reply' => $community_reply
        ]);
    }

    public function update(Request $request, CommunityReply $community_reply)
    {
        $request->validate([
            'content' => 'required|string',
            'status' => 'required|in:pending,approved,rejected',
            'is_active' => 'required|boolean',
        ]);

        $community_reply->update($request->all());

        return redirect()->route('admin.community-replies.index')->with('success', 'Reply updated successfully.');
    }

    public function toggleActive(CommunityReply $reply)
    {
        $reply->is_active = !$reply->is_active;
        $reply->save();

        $status = $reply->is_active ? 'enabled' : 'disabled';
        return back()->with('success', "Reply {$status}.");
    }

    public function destroy(CommunityReply $community_reply)
    {
        $community_reply->delete();
        return redirect()->route('admin.community-replies.index')->with('success', 'Reply deleted successfully.');
    }
}
