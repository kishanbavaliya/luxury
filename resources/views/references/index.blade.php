@extends('layouts.app')

@section('content')
<div class="container">
    <h1>References</h1>
    <a href="{{ route('references.create') }}" class="btn btn-primary mb-3">Create Reference</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Short Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($references as $ref)
            <tr>
                <td>{{ $ref->id }}</td>
                <td><a href="{{ route('references.show', $ref) }}">{{ $ref->name }}</a></td>
                <td>{{ $ref->short_name }}</td>
                <td>
                    <a href="{{ route('references.edit', $ref) }}" class="btn btn-sm btn-secondary">Edit</a>
                    <form action="{{ route('references.destroy', $ref) }}" method="POST" style="display:inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $references->links() }}
</div>
@endsection
