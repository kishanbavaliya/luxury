@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>References</h1>
    <a href="{{ route('references.create') }}" class="btn btn-primary mb-3">Create Reference</a>

    @if($references->count())
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Short Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($references as $reference)
                    <tr>
                        <td>{{ $loop->iteration + ($references->currentPage() - 1) * $references->perPage() }}</td>
                        <td>{{ $reference->name }}</td>
                        <td>{{ $reference->short_name }}</td>
                        <td>
                            <a href="{{ route('references.show', $reference) }}" class="btn btn-sm btn-secondary">View</a>
                            <a href="{{ route('references.edit', $reference) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('references.destroy', $reference) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this reference?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $references->links() }}

    @else
        <p>No references found.</p>
    @endif
</div>
@endsection
