<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CommunityQuestion;
use App\Models\CommunityCategory;

class CommunityQuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = CommunityQuestion::with(['user', 'category'])->latest();

        if ($request->filled('search')) {
            $query->where('question_text', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_verified', $request->status == 'verified' ? 1 : 0);
        }

        $questions = $query->paginate(15);
        $categories = CommunityCategory::all();

        return view('admin.community.questions.index', compact('questions', 'categories'));
    }

    public function edit(CommunityQuestion $community_question)
    {
        $categories = CommunityCategory::all();
        return view('admin.community.questions.edit', [
            'question' => $community_question,
            'categories' => $categories
        ]);
    }

    public function update(Request $request, CommunityQuestion $community_question)
    {
        $request->validate([
            'question_text' => 'required|string',
            'category_id' => 'required|exists:community_categories,id',
            'is_verified' => 'required|boolean',
        ]);

        $community_question->update($request->all());

        return redirect()->route('admin.community-questions.index')->with('success', 'Question updated successfully.');
    }

    public function toggleVerify(CommunityQuestion $question)
    {
        $question->is_verified = !$question->is_verified;
        $question->save();

        $status = $question->is_verified ? 'verified' : 'unverified';
        return back()->with('success', "Question marked as {$status}.");
    }

    public function destroy(CommunityQuestion $community_question)
    {
        $community_question->delete();
        return redirect()->route('admin.community-questions.index')->with('success', 'Question deleted successfully.');
    }
}
