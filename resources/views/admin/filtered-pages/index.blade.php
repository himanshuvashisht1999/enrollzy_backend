@extends('admin.layouts.master')
@section('title', 'Filtered Pages')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Filtered Pages</h6>
        <a href="{{ route('admin.filtered-pages.create') }}" class="btn btn-primary btn-sm">Add New</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Slug</th>
                        <th>Category</th>
                        <th>State / City</th>
                        <th>Frontend Link</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($filteredPages as $page)
                    <tr>
                        <td>{{ $page->id }}</td>
                        <td>{{ $page->slug }}</td>
                        <td>{{ $page->category }}</td>
                        <td>{{ $page->state ?? 'All' }} / {{ $page->city ?? 'All' }}</td>
                        <td>
                            @php
                                $frontendUrl = 'http://127.0.0.1:8000/filtered/' . $page->slug;
                            @endphp
                            <a href="{{ $frontendUrl }}" target="_blank" class="btn btn-info btn-sm">
                                <i class="fas fa-external-link-alt"></i> View
                            </a>
                            <button class="btn btn-secondary btn-sm" onclick="copyToClipboard('{{ $frontendUrl }}')">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </td>
                        <td>
                            <a href="{{ route('admin.filtered-pages.edit', $page) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('admin.filtered-pages.destroy', $page) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyToClipboard(text) {
    var tempInput = document.createElement("input");
    tempInput.style = "position: absolute; left: -1000px; top: -1000px";
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand("copy");
    document.body.removeChild(tempInput);
    alert("URL copied to clipboard: " + text);
}
</script>
@endsection
