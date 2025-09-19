@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Reference</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('references.update', $reference) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $reference->name) }}" required>
        </div>
        <div class="form-group">
            <label for="short_name">Short Name</label>
            <input type="text" name="short_name" id="short_name" class="form-control" value="{{ old('short_name', $reference->short_name) }}">
        </div>
        <button class="btn btn-primary mt-2">Save</button>
        <a href="{{ route('references.index') }}" class="btn btn-secondary mt-2">Cancel</a>
    </form>
</div>
@endsection
