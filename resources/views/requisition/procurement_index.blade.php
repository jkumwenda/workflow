<table class="table table-bordered table-hover">
    <thead>
        <tr>
            @can('admin')
            <th></th>
            @endcan
            <th class="rplus-order" data-column-name="id">#</th>
            <th>Requisition title</th>
            <th>Owner</th>
            <th colspan="2">Department / Unit</th>
            <th>Status</th>
            <th class="rplus-order" data-column-name="created">Created</th>
            <th class="rplus-order desc" data-column-name="updated">Updated</th>
            <th>Current User</th>
            <th>Options</th>
        </tr>
    </thead>
    <tbody>
        <tr class="bg-light">
            {!! Form::open(['method' => 'GET', 'route' => 'requisition', 'autocomplete' => 'off', 'id' => 'requisitionSearchForm']) !!}
                @can('admin')
                <th></th>
                @endcan
                <th>{!! Form::text('id', Request::get('id'), ['class' => 'form-control ' . (Request::filled('id') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                <th>{!! Form::text('title', Request::get('title'), ['class' => 'form-control ' . (Request::filled('title') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                <th>{!! Form::text('owner', Request::get('owner'), ['class' => 'form-control ' . (Request::filled('owner') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                <th>{!! Form::select('unitCategory', addEmpty(['MAIN' => 'MAIN', 'PROJECT' => 'PROJECT']), Request::get('unitCategory'), ['class' => 'form-control ' . (Request::filled('unitCategory') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                <th>{!! Form::text('unit', Request::get('unit'), ['class' => 'form-control ' . (Request::filled('unit') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                <th>{!! Form::select('status', addEmpty($requisitionStatus), Request::get('status'), ['class' => 'form-control ' . (Request::filled('status') ? 'text-danger font-weight-bold border-danger' : 'border-success')])!!}</th>
                <th>{!! Form::text('created', Request::get('created'), ['class' => 'form-control ' . (Request::filled('created') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                <th>{!! Form::text('updated', Request::get('updated'), ['class' => 'form-control ' . (Request::filled('updated') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                <th>{!! Form::text('current', Request::get('current'), ['class' => 'form-control ' . (Request::filled('current') ? 'text-danger font-weight-bold border-danger' : 'border-success')])!!}</th>
                <th>
                    <span class="btn-group">
                        {{ Form::button('<i class="fa fa-search"></i>', ['type' => 'submit', 'class' => 'btn btn-outline-warning']) }}
                    </span>
                </th>
                {!! Form::hidden('q', $q) !!}
                {!! Form::hidden('type', $type) !!}
                {!! Form::hidden('order', Request::get('order')) !!}
                {!! Form::hidden('dir', Request::get('dir')) !!}
            {!! Form::close() !!}
        </tr>
        @foreach($requisitions as $procurement)
        <tr>
            @can('admin')
            <th>
                <div class="form-check">
                    {!! Form::checkbox('select', $procurement->id, false, ['class' => 'form-check-input']) !!}
                </div>
            </th>
            @endcan
            <td >
                @if ($procurement->archived)
                <i class="fa fa-file-archive"></i>
                @endif
                {{ idFormatter('procurements', $procurement->id) }}
            </td>
            @if ($procurement->travel)
            <td ><a href="{{ route('travel/show', $procurement->travel->id) }}">{{ $procurement->title }}</a></td>
                    @else
                    <td ><a href="{{ route('procurement/show', $procurement->id) }}">{{ $procurement->title }}</a></td>
                    @endif
            
            <td >{{ $procurement->createdUser->name }}</td>
            <td >{{ $procurement->unit->category }}</td>
            <td >{{ $procurement->unit->name }}</td>
            <td >{{ $procurement->requisitionStatus->name }}</td>
            <td >{{ dateformat($procurement->created_at) }}</td>
            <td >{{ dateformat($procurement->updated_at) }}</td>
            <td >{{ empty($procurement->currentUser) ? '-' : $procurement->currentUser->name }}</td>
            <td >
                <div class="btn-group" role="group">
                    @if ($procurement->travel)
                    <a href="{{ route('travel/show', $procurement->travel->id) }}" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a>
                    @else
                    <a href="{{ route('procurement/show', $procurement->id) }}" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a>
                    @endif
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@can('admin')
@if ($q != 'archived')
<span data-toggle="tooltip" data-placement="top" title="Administrator only" class="ml-2">
    <a href="javascript:void(0);" class='btn btn-outline-warning mb-3' data-toggle='modal' data-target='#archiveModal' data-archive='true'> <i class='fa fa-file-archive'></i> Archive selected</a>
</span>
@else
<span data-toggle="tooltip" data-placement="top" title="Administrator only" class="ml-2">
    <a href="javascript:void(0);" class='btn btn-outline-warning mb-3' data-toggle='modal' data-target='#archiveModal' data-archive='false'> <i class='fa fa-file-archive'></i> Unarchive selected</a>
</span>
@endif

@include('procurement.feedback.archive_modal')
@endcan