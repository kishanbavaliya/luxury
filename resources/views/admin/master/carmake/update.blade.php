@extends('admin.layouts.app')
@section('title', 'Main page')

@section('content')
{{-- {{session()->get('errors')}} --}}

    <!-- Start Page content -->
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-sm-12">
                    <div class="box">

                        <div class="box-header with-border">
                            <a href="{{ url('carmake') }}">
                                <button class="btn btn-danger btn-sm pull-right" type="submit">
                                    <i class="mdi mdi-keyboard-backspace mr-2"></i>
                                    @lang('view_pages.back')
                                </button>
                            </a>
                        </div>

                        <div class="col-sm-12">

                            <form method="post" class="form-horizontal" action="{{ url('carmake/update',$item->id) }}">
                                @csrf

                        <div class="row">
                             <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="">@lang('view_pages.transport_type') <span class="text-danger">*</span></label>
                                            <select name="transport_type" id="transport_type" class="form-control" required>
                                                <option value="" selected disabled>@lang('view_pages.select')</option>
                                                <option value="taxi" {{ old('transport_type', $item->transport_type) == 'taxi' ? 'selected' : '' }}>@lang('view_pages.taxi')</option>
                                                <option value="delivery" {{ old('transport_type',$item->transport_type) == 'delivery' ? 'selected' : '' }}>@lang('view_pages.delivery')</option>
                                                <option value="both" {{ old('transport_type',$item->transport_type) == 'both' ? 'selected' : '' }}>@lang('view_pages.both')</option>
                                            </select>
                                            <span class="text-danger">{{ $errors->first('transport_type') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="name">@lang('view_pages.name') <span class="text-danger">*</span></label>
                                            <input class="form-control" type="text" id="name" name="name"
                                                value="{{ old('name',$item->name) }}" required
                                                placeholder="@lang('view_pages.enter') @lang('view_pages.name')">
                                            <span class="text-danger">{{ $errors->first('name') }}</span>
                                        </div>
                                    </div>

                             <div class="col-6">
                                        <div class="form-group">
                                            <label for="">@lang('view_pages.vehicle_make_for') <span
                                                    class="text-danger">*</span></label>
                                            <select name="vehicle_make_for" id="vehicle_make_for" class="form-control"
                                                    required>
                                                <option value="" selected disabled>@lang('view_pages.select')</option>
                                                <option
                                                    value="taxi" {{ old('vehicle_make_for', $item->vehicle_make_for) == 'taxi' ? 'selected' : '' }}>@lang('view_pages.taxi')</option>
                                                    <option
                                                    value="motor_bike" {{ old('vehicle_make_for', $item->vehicle_make_for) == 'motor_bike' ? 'selected' : '' }}>@lang('view_pages.motor_bike')</option>
                                                <option
                                                    value="truck" {{ old('vehicle_make_for', $item->vehicle_make_for) == 'truck' ? 'selected' : '' }}>@lang('view_pages.truck')</option>
                                            </select>
                                            <span class="text-danger">{{ $errors->first('vehicle_make_for') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="no_of_people">@lang('view_pages.no_of_people') <span class="text-danger">*</span></label>
                                            <input class="form-control" type="number" id="no_of_people" name="no_of_people" min="0"
                                                value="{{ old('no_of_people',$item->no_of_people) }}" required
                                                placeholder="@lang('view_pages.enter') @lang('view_pages.no_of_people')">
                                            <span class="text-danger">{{ $errors->first('no_of_people') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="no_of_bags">@lang('view_pages.no_of_bags') <span class="text-danger">*</span></label>
                                            <input class="form-control" type="number" id="no_of_bags" name="no_of_bags" min="0"
                                                value="{{ old('no_of_bags',$item->no_of_bags) }}" required
                                                placeholder="@lang('view_pages.enter') @lang('view_pages.no_of_bags')">
                                            <span class="text-danger">{{ $errors->first('no_of_bags') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="no_of_doors">@lang('view_pages.no_of_doors') <span class="text-danger">*</span></label>
                                            <input class="form-control" type="number" id="no_of_doors" name="no_of_doors" min="0"
                                                value="{{ old('no_of_doors',$item->no_of_doors) }}" required
                                                placeholder="@lang('view_pages.enter') @lang('view_pages.no_of_doors')">
                                            <span class="text-danger">{{ $errors->first('no_of_doors') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="">@lang('view_pages.transmission') <span class="text-danger">*</span></label>
                                            <select name="transmission" id="transmission" class="form-control" required>
                                                <option value="" selected disabled>@lang('view_pages.select')</option>
                                                <option value="manual" {{ old('transmission', $item->transmission) == 'manual' ? 'selected' : '' }}>@lang('view_pages.manual')</option>
                                                <option value="automatic" {{ old('transmission', $item->transmission) == 'automatic' ? 'selected' : '' }}>@lang('view_pages.automatic')</option>
                                            </select>
                                            <span class="text-danger">{{ $errors->first('transmission') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-12">
                                        <button class="btn btn-primary btn-sm pull-right m-5" type="submit">
                                            @lang('view_pages.update')
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- container -->
</div>
    <!-- content -->
@endsection
