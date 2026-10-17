<?php

namespace App\Http\Controllers\Admin\ExamModule;

use App\Http\Controllers\Controller;
use App\Models\ExamModule\ConductingBody;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConductingBodyController extends Controller
{
    public function index()
    {
        $bodies = ConductingBody::withCount('exams')->orderBy('name')->get();
        return view('admin.exam-module.bodies.index', compact('bodies'));
    }

    public function create()
    {
        return view('admin.exam-module.bodies.form', ['body' => new ConductingBody(['status' => true])]);
    }

    public function store(Request $request)
    {
        ConductingBody::create($this->validated($request));
        return redirect()->route('admin.exam-module.conducting-bodies.index')->with('success', 'Conducting body created.');
    }

    public function edit(ConductingBody $conductingBody)
    {
        return view('admin.exam-module.bodies.form', ['body' => $conductingBody]);
    }

    public function update(Request $request, ConductingBody $conductingBody)
    {
        $conductingBody->update($this->validated($request, $conductingBody));
        return redirect()->route('admin.exam-module.conducting-bodies.index')->with('success', 'Conducting body updated.');
    }

    public function destroy(ConductingBody $conductingBody)
    {
        if ($conductingBody->exams()->exists()) {
            return back()->with('error', 'This body is used by exams and cannot be deleted. Mark it inactive instead.');
        }
        $conductingBody->delete();
        return back()->with('success', 'Conducting body deleted.');
    }

    private function validated(Request $request, ?ConductingBody $body = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('exm_conducting_bodies', 'name')->ignore($body?->id)],
            'short_name' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
        ]);
        $data['status'] = $request->boolean('status');
        return $data;
    }
}
