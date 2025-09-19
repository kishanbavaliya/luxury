@extends('admin.layouts.app')

@section('title', 'Document Gallery')

@section('content')
<div class="container mt-4">
    <h2 class="text-center mb-4">Document Gallery</h2>

    @if(!empty($result->driver_no_show_file))
        @php
            $documents = explode(',', $result->driver_no_show_file); // Convert string to array
        @endphp

        <div class="row justify-content-center">
            @foreach($documents as $document)
                <div class="col-md-3 col-sm-4 col-6">
                    <div class="card shadow-sm mb-4">
                        <a href="{{ asset('images/' . trim($document)) }}" target="_blank">
                            <img src="{{ asset('images/' . trim($document)) }}" class="card-img-top img-fluid" alt="Document Image" style="height: 200px; object-fit: cover;">
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Single "Approved" Button for All Images with Route -->
        <div class="text-center mt-3">
            <a href="{{ route('approve-no-show-driver-document', $result->id) }}" class="btn btn-success btn-lg">Approved</a>
        </div>
    @else
        <p class="text-center text-danger">No documents available.</p>
    @endif
</div>
@endsection
