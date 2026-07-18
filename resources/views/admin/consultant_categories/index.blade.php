@extends('admin.layouts.master')

@section('title', 'Consultant Categories')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold">Add New Category</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.consultant-categories.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. University Admissions" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Parent Category (Optional)</label>
                            <select name="parent_id" class="form-select">
                                <option value="0">None (Main Category)</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @foreach($cat->children as $sub)
                                        <option value="{{ $sub->id }}">-- {{ $sub->name }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                            <small class="text-muted">Select a parent to create a Sub or Sub-Sub category.</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill">Add Category</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold">Consultant Categories Hierarchy</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Category Name</th>
                                    <th>Level</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $cat)
                                    <tr class="fw-bold text-primary">
                                        <td>{{ $cat->name }}</td>
                                        <td><span class="badge bg-soft-primary text-primary">Main</span></td>
                                        <td class="text-end">
                                            <form action="{{ route('admin.consultant-categories.destroy', $cat->id) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger" onclick="return confirm('Delete this category and its children?')"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @foreach($cat->children as $sub)
                                        <tr>
                                            <td class="ps-4 text-dark">-- {{ $sub->name }}</td>
                                            <td><span class="badge bg-soft-info text-info">Sub</span></td>
                                            <td class="text-end">
                                                <form action="{{ route('admin.consultant-categories.destroy', $sub->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-link text-danger" onclick="return confirm('Delete this sub-category?')"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                        @foreach($sub->children as $subSub)
                                            <tr>
                                                <td class="ps-5 text-muted small">--- {{ $subSub->name }}</td>
                                                <td><span class="badge bg-soft-secondary text-secondary">Sub-Sub</span></td>
                                                <td class="text-end">
                                                    <form action="{{ route('admin.consultant-categories.destroy', $subSub->id) }}" method="POST" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-link text-danger" onclick="return confirm('Delete this sub-sub-category?')"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No categories found.</td>
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

