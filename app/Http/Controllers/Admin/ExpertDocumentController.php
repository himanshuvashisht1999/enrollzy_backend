<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use App\Models\ExpertDocument;
use App\Support\Experts\Docs;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Expert documents (qualification, certificates, awards, ID...): the expert's Documents page in the CRM, and the
 * checks staff make on any document (of an expert or of an application). Files open only for staff signed in here.
 */
class ExpertDocumentController extends Controller
{
    public function index(Expert $expert)
    {
        $docs = ExpertDocument::with('reviewer')->where('expert_id', $expert->id)->orderByRaw("FIELD(status, 'pending') DESC")->orderByDesc('id')->get();
        return view('admin.experts.documents', compact('expert', 'docs'));
    }

    /** staff add a document they have checked themselves: it is verified at once */
    public function store(Request $request, Expert $expert)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(ExpertDocument::TYPES))],
            'title' => 'required|string|max:200', 'issued_by' => 'nullable|string|max:200', 'year' => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'file' => 'required|file|mimes:' . Docs::MIMES . '|max:' . Docs::MAX_KB,
        ], ['file.mimes' => 'Upload a PDF or an image (JPG, PNG, WEBP).', 'file.max' => 'The file must be 5 MB or smaller.']);
        $file = $request->file('file');
        ExpertDocument::create([
            'expert_id' => $expert->id, 'type' => $data['type'], 'title' => $data['title'], 'issued_by' => $data['issued_by'] ?? null, 'year' => $data['year'] ?? null,
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 250), 'mime' => $file->getMimeType(), 'size' => $file->getSize(),
            'path' => Docs::store($file, 'experts/' . $expert->id),
            'status' => 'verified', 'reviewed_by' => auth('admin')->id(), 'reviewed_at' => now(), 'uploaded_by' => 'staff',
            'show_on_profile' => $request->boolean('show_on_profile') && $data['type'] !== 'id_proof',
        ]);
        return back()->with('success', 'Document added and marked verified.');
    }

    /** verify or reject a document (a rejected one needs a reason the expert sees), and whether it shows on the profile */
    public function review(Request $request, ExpertDocument $document)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(ExpertDocument::STATUSES))],
            'review_note' => [Rule::requiredIf($request->status === 'rejected'), 'nullable', 'string', 'max:500'],
        ], ['review_note.required' => 'Say what is wrong with the document, so the expert can fix it.']);
        $document->update([
            'status' => $data['status'], 'review_note' => $data['review_note'] ?? null,
            'reviewed_by' => $data['status'] === 'pending' ? null : auth('admin')->id(), 'reviewed_at' => $data['status'] === 'pending' ? null : now(),
            'show_on_profile' => $request->boolean('show_on_profile') && $document->type !== 'id_proof',
        ]);
        return back()->with('success', '"' . $document->title . '": ' . ExpertDocument::STATUSES[$data['status']] . '.');
    }

    public function destroy(ExpertDocument $document)
    {
        abort_unless($document->expert_id, 422, 'Documents of an application are managed by the applicant.');
        Docs::delete($document->path);
        $document->delete();
        return back()->with('success', 'Document deleted.');
    }

    public function file(ExpertDocument $document)
    {
        return Docs::response($document->path, $document->original_name);
    }
}
