@extends('admin.layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="h4 mb-0 fw-bold">Footer Setup</h2>
        </div>
    </div>



    <ul class="nav nav-tabs mb-4" id="footerSetupTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab">Footer Layout & Details</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="menus-tab" data-bs-toggle="tab" data-bs-target="#menus" type="button" role="tab">Footer Link Columns</button>
        </li>
    </ul>

    <div class="tab-content" id="footerSetupTabContent">
        <!-- Settings Tab -->
        <div class="tab-pane fade show active" id="settings" role="tabpanel">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="{{ route('admin.footer-setup.update-settings') }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Footer Description</label>
                                <textarea name="footer_description" class="form-control" rows="3">{{ old('footer_description', $setting->footer_description) }}</textarea>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3 fw-bold">Social Links</h5>
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Facebook URL</label>
                                <input type="url" name="facebook_url" class="form-control" value="{{ old('facebook_url', $setting->facebook_url) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Twitter URL</label>
                                <input type="url" name="twitter_url" class="form-control" value="{{ old('twitter_url', $setting->twitter_url) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Instagram URL</label>
                                <input type="url" name="instagram_url" class="form-control" value="{{ old('instagram_url', $setting->instagram_url) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">LinkedIn URL</label>
                                <input type="url" name="linkedin_url" class="form-control" value="{{ old('linkedin_url', $setting->linkedin_url) }}">
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3 fw-bold">Contact Info & Copyright</h5>
                        <div class="row mb-3">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Address</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address', $setting->address) }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Contact Email</label>
                                <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $setting->contact_email) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Contact Phone</label>
                                <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $setting->contact_phone) }}">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Bottom Bar Text (Copyright)</label>
                                <input type="text" name="footer_text" class="form-control" value="{{ old('footer_text', $setting->footer_text) }}">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3 px-4"><i class="fas fa-save me-2"></i>Save Details</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Menus Tab -->
        <div class="tab-pane fade" id="menus" role="tabpanel">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold">Manage Footer Columns & Links</h5>
                    <a href="{{ route('admin.footer-setup.create') }}" class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-plus me-1"></i> Add Footer Menu
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Menu Title</th>
                                    <th>URL</th>
                                    <th>Sort Order</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($menus as $menu)
                                    <!-- Parent Menu (Column) -->
                                    <tr class="table-light border-top">
                                        <td class="ps-4">
                                            <span class="fw-bold"><i class="fas fa-columns text-primary me-2"></i>{{ $menu->title }}</span>
                                        </td>
                                        <td>{{ $menu->url ?? '-' }}</td>
                                        <td>{{ $menu->sort_order }}</td>
                                        <td>
                                            @if($menu->status)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('admin.footer-setup.edit', $menu->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('admin.footer-setup.destroy', $menu->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <!-- Children Links -->
                                    @foreach($menu->children as $child)
                                        <tr>
                                            <td class="ps-5">
                                                <span class="text-muted"><i class="fas fa-level-up-alt fa-rotate-90 me-2"></i>{{ $child->title }}</span>
                                            </td>
                                            <td><a href="{{ $child->url }}" target="_blank" class="text-decoration-none">{{ $child->url }}</a></td>
                                            <td>{{ $child->sort_order }}</td>
                                            <td>
                                                @if($child->status)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="{{ route('admin.footer-setup.edit', $child->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                                <form action="{{ route('admin.footer-setup.destroy', $child->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No footer menus found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
