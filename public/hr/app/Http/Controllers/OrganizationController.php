<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Organization::get();
            return DataTables::of($data)->addIndexColumn()
                ->addColumn('name', function ($row) {
                    $name = '<p class="text-sm font-weight-bold mb-0">' . $row->name . '</p>';
                    return $name;
                })
                ->addColumn('email', function ($row) {
                    $email = '<p class="text-sm font-weight-bold mb-0">' . $row->email . '</p>';
                    return $email;
                })
                ->addColumn('phone', function ($row) {
                    $phone = '<p class="text-sm font-weight-bold mb-0">' . $row->phone . '</p>';
                    return $phone;
                })
                ->addColumn('address', function ($row) {
                    $address = '<p class="text-sm font-weight-bold mb-0">' . $row->address . '</p>';
                    return $address;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (auth()->user()->can('staff-read') || auth()->user()->can('staff-edit')) {
                        $btn .= '<div class="d-flex"><a href="' . route('admin.organization.edit', $row->id) . '" class="btn btn-sm">
                          <i class="fa fa-pen text-primary"></i></a>';
                    }
                    return $btn;
                })
                ->rawColumns(['name', 'email', 'phone', 'address', 'action'])
                ->make(true);
        }
        return view('organizations.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('organizations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:organizations',
        ]);
    
        $org = Organization::create($request->all());

        $createStaff = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'username' => $request->email,
            'pay_based' => 'hourly',
            'salary' => '00',
            'password' => Hash::make($request->phone),
            'address' => $request->address,
            'role' => 'admin',
            'status' => 'active',
            'organization_id' => $org->id
        ];
            $user = Admin::create($createStaff);


            $user->roles()->detach();
            $user->assignRole('admin');
    
        return redirect()->route('admin.organization.index')->with('success', 'Organization added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organization $organization)
    {
        return view('organizations.edit', compact('organization'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, Organization $organization)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:organizations,email,' . $organization->id,
        ]);

        $organization->update($request->all());
        // $admin_data = Admin::where('email',$request->email)->first();
        // dd($admin_data);
        // $admin_data = Admin::where('organization_id',$organization->id)->update([
        //     'name' => $request->name,
        //     // 'email' => $request->email,
        //     'phone' => $request->phone,
        //     'username' => $request->email,
        //     'password' => Hash::make($request->phone),
        // ]);

        return redirect()->route('admin.organization.index')->with('success', 'Organization added successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
