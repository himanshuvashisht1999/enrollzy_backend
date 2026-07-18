<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Consultant;
use App\Models\ConsultantCategory;
use App\Models\ConsultantType;
use App\Models\ConsultantStatus;
use App\Models\ConsultantAccessLevel;
use App\Models\ConsultantLeadVisibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Exception;

class ConsultantController extends Controller
{
    public function index()
    {
        $consultants = Consultant::where('organization_id', auth()->user()->organization_id)->get();
        return view('admin.consultants.index', compact('consultants'));
    }

    public function create()
    {
        $organization_id = auth()->user()->organization_id;
        $categories = ConsultantCategory::where('parent_id', 0)
            ->where('organization_id', $organization_id)
            ->get();

        $types = ConsultantType::where('organization_id', $organization_id)->get();
        $statuses = ConsultantStatus::where('organization_id', $organization_id)->get();
        $access_levels = ConsultantAccessLevel::where('organization_id', $organization_id)->get();
        $lead_visibilities = ConsultantLeadVisibility::where('organization_id', $organization_id)->get();

        return view('admin.consultants.create', compact('categories', 'types', 'statuses', 'access_levels', 'lead_visibilities'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'phone' => 'required|unique:consultants,phone',
            'email' => 'required|email|unique:consultants,email',
            'password' => 'required|min:6',
            'pan_number' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->all();
            $data['organization_id'] = auth()->user()->organization_id;
            $data['password'] = Hash::make($request->password);
            $data['status'] = $request->status ?? 'active';

            // Convert checkbox "on" values to boolean
            $boolFields = [
                'generates_own_leads',
                'requires_company_leads',
                'runs_ads',
                'has_counseling_office',
                'walk_in_students',
                'can_handle_pan_india',
                'lead_assignment_allowed'
            ];
            foreach ($boolFields as $field) {
                $data[$field] = $request->has($field) ? 1 : 0;
            }

            $data['status_reason'] = $request->status_reason ?? null;

            // Handle Profile Image
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = time() . '-' . strtolower(preg_replace('/\s+/', '', $file->getClientOriginalName()));
                $file->move(public_path('consultant_images'), $fileName);
                $data['image'] = 'consultant_images/' . $fileName;
            }

            // Handle QR Code
            if ($request->hasFile('qr_code_upload')) {
                $file = $request->file('qr_code_upload');
                $fileName = 'qr-' . time() . '-' . strtolower(preg_replace('/\s+/', '', $file->getClientOriginalName()));
                $file->move(public_path('consultant_docs'), $fileName);
                $data['qr_code_upload'] = 'consultant_docs/' . $fileName;
            }

            // Handle other documents (Single Uploads)
            $docFields = [
                'cancelled_cheque_upload',
                'pan_card_upload',
                'aadhaar_upload',
                'pan_upload',
                'gst_certificate_upload',
                'business_registration_upload',
                'visiting_card_upload',
                'msme_upload',
                'mou_upload'
            ];

            foreach ($docFields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $fileName = $field . '-' . time() . '-' . strtolower(preg_replace('/\s+/', '', $file->getClientOriginalName()));
                    $file->move(public_path('consultant_docs'), $fileName);
                    $data[$field] = 'consultant_docs/' . $fileName;
                }
            }

            // Handle Multiple Office Photos
            if ($request->hasFile('office_photos')) {
                $photos = [];
                foreach ($request->file('office_photos') as $file) {
                    $fileName = 'office-' . time() . '-' . Str::random(5) . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('consultant_docs'), $fileName);
                    $photos[] = 'consultant_docs/' . $fileName;
                }
                $data['office_photos'] = $photos;
            }

            $consultant = Consultant::create($data);

            if ($request->has('categories')) {
                foreach ($request->categories as $cat) {
                    $leafCategoryId = $cat['sub_sub_category_id'] ?? $cat['sub_category_id'] ?? $cat['category_id'] ?? null;
                    if ($leafCategoryId) {
                        \App\Models\ConsultantCategoryPivot::create([
                            'consultant_id' => $consultant->id,
                            'category_id' => $leafCategoryId,
                        ]);
                    }
                }
            }

            return redirect()->route('admin.consultants.index')->with('success', 'Consultant registered successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $id = decrypt($id);
        $consultant = Consultant::where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $organization_id = auth()->user()->organization_id;
        $categories = ConsultantCategory::where('parent_id', 0)
            ->where('organization_id', $organization_id)
            ->get();

        $types = ConsultantType::where('organization_id', $organization_id)->get();
        $statuses = ConsultantStatus::where('organization_id', $organization_id)->get();
        $access_levels = ConsultantAccessLevel::where('organization_id', $organization_id)->get();
        $lead_visibilities = ConsultantLeadVisibility::where('organization_id', $organization_id)->get();

        return view('admin.consultants.edit', compact('consultant', 'categories', 'types', 'statuses', 'access_levels', 'lead_visibilities'));
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $consultant = Consultant::where('organization_id', auth()->user()->organization_id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'phone' => 'required|unique:consultants,phone,' . $consultant->id,
            'email' => 'required|email|unique:consultants,email,' . $consultant->id,
            'pan_number' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first())->withInput();
        }

        try {
            $data = $request->all();
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            } else {
                unset($data['password']);
            }

            // Convert checkbox "on" values to boolean
            $boolFields = [
                'generates_own_leads',
                'requires_company_leads',
                'runs_ads',
                'has_counseling_office',
                'walk_in_students',
                'can_handle_pan_india',
                'lead_assignment_allowed'
            ];
            foreach ($boolFields as $field) {
                $data[$field] = $request->has($field) ? 1 : 0;
            }

            $data['status_reason'] = $request->status_reason ?? null;

            // Handle Profile Image Replacement
            if ($request->hasFile('image')) {
                if ($consultant->image && file_exists(public_path($consultant->image))) {
                    @unlink(public_path($consultant->image));
                }
                $file = $request->file('image');
                $fileName = time() . '-' . strtolower(preg_replace('/\s+/', '', $file->getClientOriginalName()));
                $file->move(public_path('consultant_images'), $fileName);
                $data['image'] = 'consultant_images/' . $fileName;
            }

            // Handle Single Document Replacements
            $docFields = [
                'qr_code_upload',
                'cancelled_cheque_upload',
                'pan_card_upload',
                'aadhaar_upload',
                'pan_upload',
                'gst_certificate_upload',
                'business_registration_upload',
                'visiting_card_upload',
                'msme_upload',
                'mou_upload'
            ];

            foreach ($docFields as $field) {
                if ($request->hasFile($field)) {
                    if ($consultant->$field && file_exists(public_path($consultant->$field))) {
                        @unlink(public_path($consultant->$field));
                    }
                    $file = $request->file($field);
                    $fileName = $field . '-' . time() . '-' . strtolower(preg_replace('/\s+/', '', $file->getClientOriginalName()));
                    $file->move(public_path('consultant_docs'), $fileName);
                    $data[$field] = 'consultant_docs/' . $fileName;
                }
            }

            // Handle Multiple Office Photos replacement/addition
            if ($request->hasFile('office_photos')) {
                // Delete old photos if replacing (Optional: user might want to append, but usually edit replaces)
                if ($consultant->office_photos) {
                    foreach ($consultant->office_photos as $oldPhoto) {
                        if (file_exists(public_path($oldPhoto))) {
                            @unlink(public_path($oldPhoto));
                        }
                    }
                }
                $photos = [];
                foreach ($request->file('office_photos') as $file) {
                    $fileName = 'office-' . time() . '-' . Str::random(5) . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('consultant_docs'), $fileName);
                    $photos[] = 'consultant_docs/' . $fileName;
                }
                $data['office_photos'] = $photos;
            }

            $consultant->update($data);

            if ($request->has('categories')) {
                $consultant->categories()->delete();
                foreach ($request->categories as $cat) {
                    $leafCategoryId = $cat['sub_sub_category_id'] ?? $cat['sub_category_id'] ?? $cat['category_id'] ?? null;
                    if ($leafCategoryId) {
                        \App\Models\ConsultantCategoryPivot::create([
                            'consultant_id' => $consultant->id,
                            'category_id' => $leafCategoryId,
                        ]);
                    }
                }
            }

            return redirect()->route('admin.consultants.index')->with('success', 'Consultant updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function getSubCategories(Request $request)
    {
        $parentId = $request->parent_id;
        $categories = ConsultantCategory::where('parent_id', $parentId)
            ->where('organization_id', auth()->user()->organization_id)
            ->where('status', 'active')
            ->get(['id', 'name']);

        return response()->json([
            'status' => 1,
            'data' => $categories,
        ]);
    }
}
