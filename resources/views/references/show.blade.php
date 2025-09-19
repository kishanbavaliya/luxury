@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Reference Details</h1>
    <p><strong>ID:</strong> {{ $reference->id }}</p>
    <p><strong>Name:</strong> {{ $reference->name }}</p>
    <p><strong>Short Name:</strong> {{ $reference->short_name }}</p>
    <a href="{{ route('references.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
