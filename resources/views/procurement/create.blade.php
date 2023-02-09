@extends('layouts.layout')
@section('title', 'Create Procurement Requisition')

@section('scripts')
<script language="javascript">
$(document).ready(function(){
    $('#edit_procurement_items').itemTable();

    $(document).on("focus", ".js-item-name", function(e) {
        if ( !$(this).data("autocomplete") ) {
            $(this).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "{{ route('procurement/itemSearch') }}",
                        data: {
                            name : request.term
                        },
                        dataType: "json",
                        success: function(data){
                            response($.map(data, function(obj, id){
                                return {
                                    label: obj,
                                    value: id
                                };
                            }));
                        }
                    });
                },
                select: function (event, ui) {
                    $(this).siblings('.js-item-id').val(ui.item.value);
                    this.value = ui.item.label;
                    return false;
                },
                minLength: 3
            });
        }
    });

});
</script>

@endsection

@section('content')
<div class="row">
    <div class="col-xl-10 m-xl-auto">
        {!! Form::open(['method' => 'POST', 'autocomplete' => 'off']) !!}

            <div class="card">
                <div class="card-body">
                    <div class="form-group row {{ $errors->has('unit_id') ? 'has-danger' :'' }}">
                        {!! Form::label('unit_id', 'Unit/Dept.',['class' => 'col-md-2 col-form-label'], false) !!}
                        <div class="col-md-8">
                            @if (count($units) == 1)
                                <span>{{ $units[0]->name }}</span>
                                {!! Form::hidden('unit_id', $units[0]->id) !!}
                            @else
                                {!! Form::select('unit_id', $units->pluck('name', 'id'), null, ['class' => 'form-control','required' => 'required', 'placeholder' => '- Select unit/department - ']) !!}
                                @error('unit_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            @endif
                        </div>
                    </div>

                    <div class="form-group row {{ $errors->has('title') ? 'has-danger' :'' }}">
                        {!! Form::label('title', 'Requisition Title',['class' => 'col-md-2 col-form-label'], false) !!}
                        <div class="col-md-8">
                            {!! Form::text('title', null, ['class' => 'form-control','required' => 'required', 'autofocus' => true]) !!}
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-warning"><i class="fa fa-exclamation-triangle fa-lg"></i> <span class="h5">DON'T combine <strong>two</strong> or more items in <strong>one</strong> row (Sizes, Colours)</span></div>

                    <table class="table table-striped" id="edit_procurement_items">
                        <thead>
                            <tr class="headings">
                                <th class="column-title">No. </th>
                                <th class="column-title">Items. </th>
                                <th class="column-title">Description </th>
                                <th class="column-title">UOM <a href="#" data-toggle="tooltip" data-html="true" title="Bottled water - [CASE]<br/>Envelopes - [CARTON] <br/>Examination sheets - [REAM]<br/>Pens - [BOX]<br/>Photocopy paper - [REAM]<br/>Ruled paper - [REAM]<br/>Soft drinks - [CRATE]<br/>Staples - [PACK]<br/>Sugars - [PACKET]<br/>Toners - [EACH]<br/>"><i class="fa fa-question-circle"></i></a></th>
                                <th class="column-title">Qty Reg </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="even pointer">
                                <td class="js-row-no"></td>
                                <td class="form-group">
                                    {!! Form::text('item_name[]', null, ['class' => 'form-control js-item-name', 'required' => 'required']) !!}
                                    {!! Form::hidden('item_id[]', null, ['class' => 'js-item-id']) !!}
                                </td>
                                <td class="form-group">
                                    {!! Form::textarea('description[]', null, ['class' => 'form-control', 'required' => 'required']) !!}
                                </td>
                                <td class="form-group">
                                    {!! Form::select('uom[]', $uoms, null, ['class' => 'form-control', 'required' => 'required']) !!}
                                </td>
                                <td class="form-group">
                                    {!! Form::number('quantity[]', null, ['class' => 'form-control', 'required' => 'required']) !!}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <!-- Cancel -->
                    <a href="{{ route('requisition') }}" class="btn btn-outline-dark"><i class="fa fa-backward"></i> Cancel</a>
                    <!-- Submit -->
                    {!! Form::button('<i class="fa fa-save"></i> Save', ['type' => 'submit', 'class' => 'btn btn-primary float-right']) !!}
                </div>
            </div>
        {!! Form::close() !!}

    </div>
</div>

@endsection