<!-- <table class="table table-hover">
    <thead>
        <tr>
            <th> @lang('view_pages.s_no')</th>
            <th> @lang('view_pages.request_id')</th>
            <th> @lang('view_pages.date')</th>
            <th> @lang('view_pages.user_name')</th>
            <th> @lang('view_pages.driver_name')</th>
          
            <th>Document</th>
            <th> @lang('view_pages.action')</th>


        </tr>
    </thead>

<tbody>
    @php  $i= $results->firstItem(); @endphp

    @forelse($results as $key => $result)
        <tr>
            <td>{{ $i++ }} </td>
            <td>{{$result->request_number}}</td>
            <td>{{$result->getConvertedAcceptedAtAttribute()}}</td>
            <td>
                <span>{{$result->userDetail->name ?? '-' }}</span>
            </td>
            <td>
                <span>{{ $result->driverDetail->name ?? '-' }}</span>
            </td>
            <td>   <a class="text-white" href="{{url('requests/trip_view',$result->id) }}">
               <button type="button" class="btn btn-info btn-sm">
                 View
                  </button></a>
               </td>
            <td>  
            <a class="text-white" href="{{url('requests/trip_view',$result->id) }}">
               <button type="button" class="btn btn-success btn-sm">
                Approved
                  </button></a> 
                <a class="text-white" href="{{url('requests/trip_view',$result->id) }}">
               <button type="button" class="btn btn-info btn-sm">
                 @lang('view_pages.view')
                  </button></a>
               </td>
        </tr>
    @empty
        <tr>
            <td colspan="11">
                <p id="no_data" class="lead no-data text-center">
                    <img src="{{asset('assets/img/dark-data.svg')}}" style="width:150px;margin-top:25px;margin-bottom:25px;" alt="">
                    <h4 class="text-center" style="color:#333;font-size:25px;">@lang('view_pages.no_data_found')</h4>
                </p>
            </td>
        </tr>
    @endforelse

    </tbody>
    </table>
    <ul class="pagination pagination-sm pull-right">
        <li>
            <a href="#">{{$results->links()}}</a>
        </li>
    </ul> -->
    <table class="table table-hover">
    <thead>
        <tr>
            <th> @lang('view_pages.s_no')</th>
            <th> @lang('view_pages.request_id')</th>
            <th> @lang('view_pages.date')</th>
            <th> @lang('view_pages.user_name')</th>
            <th> @lang('view_pages.driver_name')</th>
            <th> @lang('view_pages.action')</th>
        </tr>
    </thead>

    <tbody>
        @php  $i = $results->firstItem(); @endphp

        @forelse($results as $key => $result)
            <tr>
                <td>{{ $i++ }} </td>
                <td>{{ $result->request_number }}</td>
                <td>{{ $result->getConvertedAcceptedAtAttribute() }}</td>
                <td><span>{{ $result->userDetail->name ?? '-' }}</span></td>
                <td><span>{{ $result->driverDetail->name ?? '-' }}</span></td>
               
                <td>  
                 
                    <a class="text-white" href="{{ route('no-driver-show-document', $result->id) }}">
                        <button type="button" class="btn btn-info btn-sm">@lang('view_pages.view')</button>
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">
                    <p id="no_data" class="lead no-data text-center">
                        <img src="{{ asset('assets/img/dark-data.svg') }}" 
                             style="width:150px;margin-top:25px;margin-bottom:25px;" alt="">
                        <h4 class="text-center" style="color:#333;font-size:25px;">
                            @lang('view_pages.no_data_found')
                        </h4>
                    </p>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>


<!-- Pagination -->
<ul class="pagination pagination-sm pull-right">
    <li>
        <a href="#">{{ $results->links() }}</a>
    </li>
</ul>


