<table class="table table-bordered table-hover">
    <thead>
        <tr>
        
            <th class="rplus-order" data-column-name="id">#</th>
            <th class="rplus-order" data-column-name="procurement_id">P-#</th>
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
               
                <th>{!! Form::text('id', Request::get('id'), ['class' => 'form-control ' . (Request::filled('id') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                <th>{!! Form::text('procurement_id', Request::get('procurement_id'), ['class' => 'form-control ' . (Request::filled('procurement_id') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
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
        
        @foreach($requisitions as $travel)
        <tr>
            <td >{{ idFormatter('travel', $travel->id) }}</td>
            <td >
                @if ($travel->procurement->archived)
                    <i class="fa fa-file-archive"></i>
                @endif
                {{ idFormatter('procurement', $travel->procurement_id) }}
            </td>
            <td ><a href="{{ route('travel/show', $travel->id) }}">{{ $travel->procurement->title }}</a></td>
            <td >{{ $travel->procurement->createdUser->name }}</td>
            <td >{{ $travel->procurement->unit->category }}</td>
            <td >{{ $travel->procurement->unit->name }}</td>
            <td >{{ $travel->requisitionStatus->name }}</td>
            <td >{{ dateformat($travel->procurement->created_at) }}</td>
            <td >{{ dateformat($travel->procurement->updated_at) }}</td>
            <td >{{ empty($travel->procurement->currentUser) ? '-' : $travel->procurement->currentUser->name }}</td>
            <td >
                <div class="btn-group" role="group">
                    <a href="{{ route('travel/show', $travel->id) }}" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a>
                </div>
            </td>
            
            
        </tr>
        @endforeach
    </tbody>
</table>

<span data-toggle="tooltip" data-placement="top" title="Administrator only" class="ml-2">
    <a href="javascript:void(0);" class='btn btn-outline-warning mb-3' data-toggle='modal' data-target='#archiveModal' data-archive='true'> <i class='fa fa-file-archive'></i> Archive selected</a>
</span>

<span data-toggle="tooltip" data-placement="top" title="Administrator only" class="ml-2">
    <a href="javascript:void(0);" class='btn btn-outline-warning mb-3' data-toggle='modal' data-target='#archiveModal' data-archive='false'> <i class='fa fa-file-archive'></i> Unarchive selected</a>
</span>