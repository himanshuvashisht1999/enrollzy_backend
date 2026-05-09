<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrendingSkill;
use Illuminate\Http\Request;

class TrendingSkillController extends Controller
{
    public function index()
    {
        $skills = TrendingSkill::orderBy('sort_order')->get();
        return view('admin.trending-skills.index', compact('skills'));
    }

    public function create()
    {
        return view('admin.trending-skills.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'status' => 'required|boolean',
            'sort_order' => 'required|integer',
        ]);

        TrendingSkill::create($request->all());

        return redirect()->route('admin.trending-skills.index')->with('success', 'Skill created successfully.');
    }

    public function edit(TrendingSkill $trendingSkill)
    {
        return view('admin.trending-skills.edit', compact('trendingSkill'));
    }

    public function update(Request $request, TrendingSkill $trendingSkill)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'status' => 'required|boolean',
            'sort_order' => 'required|integer',
        ]);

        $trendingSkill->update($request->all());

        return redirect()->route('admin.trending-skills.index')->with('success', 'Skill updated successfully.');
    }

    public function destroy(TrendingSkill $trendingSkill)
    {
        $trendingSkill->delete();
        return redirect()->route('admin.trending-skills.index')->with('success', 'Skill deleted successfully.');
    }
}
