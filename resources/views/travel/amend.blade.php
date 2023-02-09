@extends('layouts.layout')
@section('title', 'Create Travel Requisition')

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
            {!! Form::model($requisition, ['method' => 'POST', 'autocomplete' => 'off']) !!}

            <div class="card">
                <div class="card-body">
                    <div class="form-group row {{ $errors->has('unit_id') ? 'has-danger' :'' }}">
                        {!! Form::label('unit_id', 'Unit/Dept.',['class' => 'col-md-2 col-form-label'], false) !!}
                        <div class="col-md-10">
                            @if (count($units) == 1)
                                <span>{{ $requisition->unit->name }}</span>
                                {!! Form::hidden('unit_id', $requisition->unit_id) !!}
                            @else
                                {!! Form::select('unit_id', $travel->procurement->unit->pluck('name', 'id'), null,['class' => 'form-control','required' => 'required', 'placeholder' => '- Select unit/department - ']) !!}
                                @error('unit_id')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            @endif
                        </div>
                    </div>

                    <div class="form-group row {{ $errors->has('title') ? 'has-danger' :'' }}">
                        {!! Form::label('title', 'Requisition title',['class' => 'col-md-2 col-form-label'], false) !!}
                        <div class="col-md-10">
                            {!! Form::text('title', null, ['class' => 'form-control','required' => 'required', 'autofocus' => true]) !!}
                            @error('title')
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row {{ $errors->has('description') ? 'has-danger' :'' }}">
                        {!! Form::label('purpose', 'Purpose description',['class' => 'col-md-2 col-form-label'], false) !!}
                        <div class="col-md-10">
                            {!! Form::textarea('purpose', $requisition->travel->purpose, ['class' => 'form-control','required' => 'required', 'autofocus' => true]) !!}
                            @error('purpose')
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row {{ $errors->has('title') ? 'has-danger' :'' }}">
                        {!! Form::label('departureDate', 'Departure date',['class' => 'col-md-2 col-form-label'], false) !!}
                        <div class="col-md-4">
                            {!! Form::date('departureDate', date('Y-m-d', strtotime($requisition->travel->datetime_out)), ['class' => 'form-control','required' => 'required', 'autofocus' => true, 'id'=>"departureDate"]) !!}
                            @error('departureDate')
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        {!! Form::label('returnDate', 'Return date',['class' => 'col-md-2 col-form-label'], false) !!}
                        <div class="col-md-4">
                            {!! Form::date('returnDate',date('Y-m-d', strtotime($requisition->travel->datetime_in)) , ['class' => 'form-control','required' => 'required', 'autofocus' => true, 'id'=>"returnDate"]) !!}
                            @error('returnDate')
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row {{ $errors->has('title') ? 'has-danger' :'' }}">
                        {!! Form::label('origin', 'Origin',['class' => 'col-md-2 col-form-label'], false) !!}
                        <div class="col-md-4">
                            {!! Form::select('origin', $campuses, $requisition->travel->origin, ['class' => 'form-control select2', 'required' => 'required','placeholder' => '- Please Select - ']) !!}
                            @error('origin')
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        {!! Form::label('destination', 'Destination',['class' => 'col-md-2 col-form-label'], false) !!}
                        <div class="col-md-4">
                            {!! Form::select('destination', $districts, $requisition->travel->destination, ['class' => 'form-control select2', 'required' => 'required','placeholder' => '- Please Select - ']) !!}
                            @error('destination')
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>

                    <!--transport section-->
                    <div class="alert alert-warning"><i class="fa fa-car fa-lg"></i> Transport </div>

                    <div class="form-group row {{ $errors->has('title') ? 'has-danger' :'' }}">
                        {!! Form::label('vehicle', ' ',['class' => 'col-md-3 col-form-label'], false) !!}
                        <div class="col-md-9">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="radioButton1" value="option1">
                                <label class="form-check-label" for="inlineRadio1"> Department/Project vehicle </label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="radioButton2" value="option2">
                                <label class="form-check-label" for="inlineRadio2"> Pool vehicle </label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="inlineRadioOptions" id="radioButton3" value="option2">
                                <label class="form-check-label" for="inlineRadio2">Personal vehicle</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row d-none" id="unitField">
                        {!! Form::label('vehicleType', 'Vehicle preference',['class' => 'col-md-2 col-form-label']) !!}

                        <div class="col-md-4">
                            <select class="form-control select2" name="vehicle">
                                @foreach($unitVehicles as $unitVehicle)
                                    <option> {{$unitVehicle->registration_number}} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row d-none" id="unitField2">
                        {!! Form::label('title', 'Vehicle type preference',['class' => 'col-md-2 col-form-label'], false) !!}
                        <div class="col-md-4">
                            {!! Form::select('vehicleType', $poolVehicles, $travel->vehicle_type_id,['class' => 'form-control','required' => 'required', 'placeholder' => '--Please Select--']) !!}
                        </div>
                    </div>

                    <!--travellers list section-->
                    <div class="alert alert-warning"><i class="fa fa-users fa-lg"></i> List of travellers </div>

                    <table class="table table-striped" id="edit_procurement_items">
                        <thead>
                        <tr class="headings">
                            <th class="column-title">Traveller </th>
                            <th class="column-title">Departure date </th>
                            <th class="column-title">Return date </th>
                            <th class="column-title">Should accommodation be provided? </th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach ($travel->travellers as $traveller)
                            
                            @php
                            if($traveller->user->roles->pluck("short_name")->first() == "Driver")
                                continue;
                            @endphp

                            <tr class="even pointer">
                                <td class="form-group">
                                    {!! Form::select('users[]', $users, $traveller->user_id, ['class' => 'form-control', 'required' => 'required','placeholder' => '- Please Select - ']) !!}
                                </td>
                                <td class="form-group">
                                    {!! Form::date('userDepartureDate[]',date('Y-m-d', strtotime($traveller->departure_date)) , ['class' => 'form-control','required' => 'required', 'autofocus' => true, 'id'=>"departureDate2"]) !!}
                                </td>
                                
                                <td class="form-group">
                                    {!! Form::date('userReturnDate[]', date('Y-m-d', strtotime($traveller->return_date)), ['class' => 'form-control','required' => 'required', 'autofocus' => true, 'id'=>"returnDate2"]) !!}
                                </td>
                                <td class="form-group">
                                    {!! Form::select('accommodationProvided[]', $accommodationProvided, $traveller->accomodation_provided, ['class' => 'form-control', 'required' => 'required','placeholder' => '- Please Select - ']) !!}
                                </td>
                                {!! Form::hidden('requisition_traveller_id[]', $traveller->id, ['id'=>'hiddenTravellerInput']) !!}
                            </tr>
                        @endforeach
                        
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <!-- Cancel -->
                    <a href="{{ route('requisition')}}" class="btn btn-outline-warning"><i class="fa fa-backward"></i> Cancel</a>
                    <!-- Submit -->
                    {!! Form::button('<i class="fa fa-save"></i> Save', ['type' => 'submit', 'class' => 'btn btn-warning text-white float-right']) !!}
                </div>
            </div>
            {!! Form::close() !!}

        </div>
    </div>

@endsection