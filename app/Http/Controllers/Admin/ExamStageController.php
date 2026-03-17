<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamStage;
use Illuminate\Http\Request;

class ExamStageController extends Controller
{
    public function index()
    {
        $items = ExamStage::orderBy('sort_order')->orderBy('title')->get();
        return view('admin.exam-stages.index', compact('items'));
    }

    public function create()
    {
        return view('admin.exam-stages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        ExamStage::create([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.exam-stages.index')->with('success', 'Exam stage created successfully.');
    }

    public function edit(ExamStage $examStage)
    {
        return view('admin.exam-stages.edit', compact('examStage'));
    }

    public function update(Request $request, ExamStage $examStage)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $examStage->update([
            'title' => $request->title,
            'status' => $request->has('status'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.exam-stages.index')->with('success', 'Exam stage updated successfully.');
    }

    public function destroy(ExamStage $examStage)
    {
        $examStage->delete();
        return redirect()->route('admin.exam-stages.index')->with('success', 'Exam stage deleted successfully.');
    }
}

