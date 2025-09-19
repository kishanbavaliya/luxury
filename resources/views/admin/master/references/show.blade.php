@extends('admin.layouts.app')

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <h1>Reference: {{ $reference->name }}</h1>
    <p><strong>Short name:</strong> {{ $reference->short_name }}</p>

    <a href="{{ route('references.index') }}" class="btn btn-secondary">Back</a>
    <a href="{{ route('references.edit', $reference) }}" class="btn btn-warning">Edit</a>
</div>
@endsection
