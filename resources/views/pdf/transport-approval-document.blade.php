<!DOCTYPE html>
<html>
<head>
    <title>Travel Requisition</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .center {
            text-align: center;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 10px;
            border: 1px solid #ccc;
        }
        .check {
            background-image: url({{public_path('images/icons/check.png')}});
            width: 20px;
            height: 20px;
            display: inline-block;
            background-size: cover;
        }
        .ban {
            background-image: url({{public_path('images/icons/ban.png')}});
            width: 20px;
            height: 20px;
            display: inline-block;
            background-size: cover;
        }
        .random {
            background-image: url({{public_path('images/icons/random.png')}});
            width: 20px;
            height: 20px;
            display: inline-block;
            background-size: cover;
        }
        .reply {
            background-image: url({{public_path('images/icons/reply.png')}});
            width: 20px;
            height: 20px;
            display: inline-block;
            background-size: cover;
        }
        .pending::before {
            content: "Pending";
            color: orange;
        }
    </style>
</head>
<body>
<div class="center">
    <img src="images/logo.svg" alt="Logo">
</div>
<h1 class="center">Travel Requisition</h1>
<table class="table">
    <tr>
        <td style="width: 25%;">Requisition title:</td>
        <td style="width: 75%;">{{$requisition->title}}</td>
    </tr>
    <tr>
        <td style="width: 25%;">Requisition owner:</td>
        <td style="width: 75%;">{{$requisition->createdUser->name}}</td>
    </tr>
    <tr>
        <td style="width: 25%;">Department:</td>
        <td style="width: 75%;">{{$requisition->unit->name}}</td>
    </tr>
    <tr>
        <td style="width: 25%;">Raised on:</td>
        <td style="width: 75%;">{{ $requisition->created_at }}</td>
    </tr>
    <tr>
        <td style="width: 25%;">Purpose:</td>
        <td style="width: 75%;">{{$travel->purpose}}</td>
    </tr>
    <tr>
        <td style="width: 25%;">Travelling from:</td>
        <td style="width: 75%;">{{$travel->campus->name}} Campus</td>
    </tr>
    <tr>
        <td style="width: 25%;">Travelling to:</td>
        <td style="width: 75%;">{{ $travel->district->name }}</td>
    </tr>

</table>
<br>
<h2 class="center">Travelers</h2>
<table class="table">
    <tr>
        <th>Name</th>
        <th>Phone Number</th>
        <th>Departure</th>
        <th>Return</th>
        <th>Days</th>
    </tr>
    @foreach ($travel->travellers as $traveller)
    <tr>
        <td>{{ $traveller->user->first_name }} {{ $traveller->user->surname }}</td>
        <td>{{ $traveller->user->phone_number?? 'Not available' }}</td>
        <td>{{ dateformat($traveller->departure_date) }}</td>
        <td>{{ dateformat($traveller->return_date) }}</td>
        <td>{{$travellerDays[$loop->iteration-1]}}</td>
    </tr>
    @endforeach

</table>
<br>
<h2 class="center">Approval Status</h2>

<table class="table">
    <tr>
        <th> </th>
        <th>User</th>
        <th>Role</th>
        <th>Stage</th>
        <th>Status</th>
        <th>Comment</th>
        <th>Date approved</th>
    </tr>
    <tbody>

    //main requisition flow
    @php
        $i = 1;
        foreach($trails as $trail){
            if($trail->flowDetail->requisition_status_id == 72){
                        continue;
            }
            echo pdfTrailTableColumn($i, $trail);
            $i++;
        }
        if (!$canceled->isEmpty()) {
            echo pdfTrailTableColumn(($i+1), $canceled[0]);
        }
    @endphp
    </tbody>

    //transport requisition flow
    @if ($transport)
        <thead>
            <tr>
                <th colspan="12">Transport - <a href="{{ route('transport/show', $transport->id) }}">{{
                            idFormatter('transport', $transport->id) }}</a>
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $i = 1;
                foreach($transportTrails as $trail){
                    if($trail->flowDetail->requisition_status_id == 90){
                        continue;
                    }
                echo pdfTrailTableColumn($i, $trail);
                $i++;
                }
                if (!$canceled->isEmpty()) {
                echo pdfTrailTableColumn(($i+1), $canceled[0]);
                }
            @endphp
        </tbody>
    @endif

    //subsistence requisition flow
    @if ($subsistence)
        <thead>
            <tr>
                <th colspan="12">Subsistence - <a href="{{ route('subsistence/show', $subsistence->id) }}">{{
                                idFormatter('subsistence', $subsistence->id) }}</a></th>
            </tr>
        </thead>
        @php
            $subsistenceTrails = $subsistence->trails()->get();
            $i = 1;
            foreach($subsistenceTrails as $trail){
                if($trail->flowDetail->requisition_status_id == 80){
                        continue;
                    }
            echo pdfTrailTableColumn($i, $trail, true);
            $i++;
            }
            if (!$canceled->isEmpty()) {
            echo pdfTrailTableColumn(($i+1), $canceled[0], true);
            }
        @endphp
        </tbody>
    @endif
</table>
</body>
</html>


