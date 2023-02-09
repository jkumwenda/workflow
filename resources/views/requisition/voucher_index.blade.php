<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th class="rplus-order" data-column-name="id">#</th>
            <th class="rplus-order" data-column-name="purchase_id">PR-#</th>
            <th>Requisition title</th>
            <th colspan="2">Department / Unit</th>
            <th>Supplier</th>
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
                    <th>{!! Form::text('purchase_id', Request::get('purchase_id'), ['class' => 'form-control ' . (Request::filled('purchase_id') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                    <th>{!! Form::text('title', Request::get('title'), ['class' => 'form-control ' . (Request::filled('title') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                    <th>{!! Form::select('unitCategory', addEmpty(['MAIN' => 'MAIN', 'PROJECT' => 'PROJECT']), Request::get('unitCategory'), ['class' => 'form-control ' . (Request::filled('unitCategory') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                    <th>{!! Form::text('unit', Request::get('unit'), ['class' => 'form-control ' . (Request::filled('unit') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
                    <th>{!! Form::text('supplier', Request::get('supplier'), ['class' => 'form-control ' . (Request::filled('supplier') ? 'text-danger font-weight-bold border-danger' : 'border-success')]) !!}</th>
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
        @foreach($requisitions as $voucher)
        <tr>
            <td >{{ idFormatter('voucher', $voucher->id) }}</td>
            <td >{{ idFormatter('purchase', $voucher->purchase_id) }}</td>
            <td ><a href="{{ route('voucher/show', $voucher->id) }}">{{ $voucher->purchase->procurement->title }}</a></td>
            <td >{{ $voucher->purchase->procurement->unit->category }}</td>
            <td >{{ $voucher->purchase->procurement->unit->name }}</td>
            <td >{{ $voucher->purchase->supplier->name }}</td>
            <td >{{ $voucher->requisitionStatus->name }}</td>
            <td >{{ dateformat($voucher->created_at) }}</td>
            <td >{{ dateformat($voucher->updated_at) }}</td>
            <td >{{ empty($voucher->currentUser) ? '-' : $voucher->currentUser->name }}</td>
            <td >
                <div class="btn-group" role="group">
                    <a href="{{ route('voucher/show', $voucher->id) }}" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>