<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExamCategory;

class ExamCategoryController extends Controller
{
    public function index()
    {
        $categories = ExamCategory::orderBy("title", "asc")->get();
        return view("admin.exam_category.index", compact("categories"));
    }

    public function create()
    {
        return view("admin.exam_category.create");
    }

    public function store(Request $request)
    {
        $request->validate([
            "title" => "required|string|max:255",
            "status" => "boolean"
        ]);

        ExamCategory::create($request->all());

        return redirect()->route("admin.exam-categories.index")->with("success", "Exam Category created successfully.");
    }

    public function edit(ExamCategory $examCategory)
    {
        return view("admin.exam_category.edit", compact("examCategory"));
    }

    public function update(Request $request, ExamCategory $examCategory)
    {
        $request->validate([
            "title" => "required|string|max:255",
            "status" => "boolean"
        ]);

        $examCategory->update($request->all());

        return redirect()->route("admin.exam-categories.index")->with("success", "Exam Category updated successfully.");
    }

    public function destroy(ExamCategory $examCategory)
    {
        $examCategory->delete();
        return redirect()->route("admin.exam-categories.index")->with("success", "Exam Category deleted successfully.");
    }
}
