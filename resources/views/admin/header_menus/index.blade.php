@extends('admin.layouts.master')
@section('title', 'Header Menus')
@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <h4 class="mb-0">Main Header Menus</h4>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('admin.header-menus.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Menu</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th width="50">Sort</th>
                        <th>Menu Title</th>
                        <th>URL</th>
                        <th>Type / Level</th>
                        <th width="100">Status</th>
                        <th width="150" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($menus as $menu)
                        <tr>
                            <td>{{ $menu->sort_order }}</td>
                            <td class="fw-bold">{{ $menu->title }}</td>
                            <td>{{ $menu->url ?? '-' }}</td>
                            <td>Main Menu</td>
                            <td>
                                <span class="badge bg-{{ $menu->status ? 'success' : 'danger' }}">
                                    {{ $menu->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.header-menus.edit', $menu->id) }}" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.header-menus.destroy', $menu->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this menu?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @foreach($menu->children as $child)
                            <tr>
                                <td>{{ $child->sort_order }}</td>
                                <td class="ps-4">↳ {{ $child->title }}</td>
                                <td>{{ $child->url ?? '-' }}</td>
                                <td>Column / Sub Menu</td>
                                <td>
                                    <span class="badge bg-{{ $child->status ? 'success' : 'danger' }}">
                                        {{ $child->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.header-menus.edit', $child->id) }}" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.header-menus.destroy', $child->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @foreach($child->children as $grandchild)
                                <tr>
                                    <td>{{ $grandchild->sort_order }}</td>
                                    <td class="ps-5 text-muted">&nbsp;&nbsp;&nbsp;&nbsp;↳ {{ $grandchild->title }}</td>
                                    <td>{{ $grandchild->url ?? '-' }}</td>
                                    <td>Link</td>
                                    <td>
                                        <span class="badge bg-{{ $grandchild->status ? 'success' : 'danger' }}">
                                            {{ $grandchild->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.header-menus.edit', $grandchild->id) }}" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('admin.header-menus.destroy', $grandchild->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No menus found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
