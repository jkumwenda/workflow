@extends('layouts.layout')
@section('title', 'Preferences - Vehicles')

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
    <div class="col-xl-9 m-xl-auto">
        {!! Form::open(['route' => 'vehicles.store', 'autocomplete' => 'off']) !!}
            <div class="card">
                <div class="card-header card-header-primary">
                    <h4 class="card-title">Create a Vehicle</h4>
                </div>
                <div class="card-body">



                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('registration_number', 'Registration Number') !!}
                                {!! Form::text('registration_number', null, ['class' => 'form-control','required' => 'required']) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('colour', 'Colour') !!}
                                {!! Form::text('colour', null, ['class' => 'form-control','required' => 'required']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('mileage', 'Mileage') !!}
                                {!! Form::number('mileage', null, ['class' => 'form-control','required' => 'required']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('capacity', 'Capacity') !!}
                                {!! Form::number('capacity', null, ['class' => 'form-control','required' => 'required']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('vehicle_type_id', 'Vehicle Type') !!}
                                {!! Form::select('vehicle_type_id',$vehicleTypes,null, ['class' => 'form-control select2','required' => 'required','placeholder' => '-- Please Select --']) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <span id="flow-summary-popover"></span>
                        </div>
                    </div>

                    <!-- Horizontal line -->
                    <div class="row">
                        <div class="col-md-8">
                            <hr>
                        </div>
                        <div class="col-md-4">
                            <span id="flow-summary-popover"></span>
                        </div>
                    </div>

                    <!-- Radio buttons -->
                    <div class="form-group">
                        {!! Form::label('title', 'Where does this vehicle belong? Please check') !!}
                        <div class="col-md-4">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="radioButton1" value="option1">
                                <label class="form-check-label" for="inlineRadio1">Department/Project</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="radioButton2" value="option2">
                                <label class="form-check-label" for="inlineRadio2">Pool</label>
                            </div>
                        </div>
                    </div>

                    <!-- Horizontal line -->
                    <div class="row">
                        <div class="col-md-8">
                            <hr>
                        </div>
                        <div class="col-md-4">
                            <span id="flow-summary-popover"></span>
                        </div>
                    </div>

                    <div class="row d-none" id="unitField">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('unit_id', 'Unit') !!}
                                {!! Form::select('unit_id',$units, null, ['class' => 'form-control select2','placeholder' => '-- Please Select --']) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <span id="flow-summary-popover"></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('make_id', 'Make') !!}
                                {!! Form::select('make_id',$makes,null, ['class' => 'form-control select2','required' => 'required','placeholder' => '-- Please Select --']) !!}

                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('campus_id', 'Campus') !!}
                                {!! Form::select('campus_id',$campuses,null, ['class' => 'form-control select2','required' => 'required','placeholder' => '-- Please Select --']) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <span id="flow-summary-popover"></span>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <!-- Cancel -->
                    <a href="{{ route('vehicles.index') }}" class="btn btn-outline-warning"><i class="fa fa-backward"></i> Cancel</a>
                    <!-- Submit -->
                    {!! Form::button('<i class="fa fa-save"></i> Save', ['type' => 'submit', 'class' => 'btn btn-warning text-white float-right']) !!}
                </div>
            </div>
        </div>
        {!! Form::close() !!}
</div>

@endsection
