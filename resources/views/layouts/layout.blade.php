<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ url('images/favicon.png') }}">

    <title>{{ config('app.name', 'RPLUS') }} | @yield("title")</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">

    <!-- Styles -->
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet"> <!-- for all npm_modules -->

    <!-- Custom Theme Style -->
    <link href="{{ mix('css/custom.css') }}" rel="stylesheet">

    <!-- Rplus original Style -->
    <link href="{{ mix('css/rplus.css') }}" rel="stylesheet">

</head>

<body>
    <div class="container-fluid">
        <!-- top navigation -->
        @include("partials.header")
        <!-- /top navigation -->

        <!-- page content -->
        <div class="row custom-title">
            <div class="col-xl-11 ml-auto mr-auto mt-2 mb-2">
                <div class="row">
                    <div class="col-md-6">
                        <h3>@yield("title")</h3>
                    </div>
                    <div class="col-md-6">
                        <h3>@yield("top-content")</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row custom-content">
            <div class="col-xl-11 ml-auto mr-auto mt-2 mb-2">

                <div class="alert alert-danger" @if (!session('error')) style="display: none" @endif id="error-alert">{{ session('error') }}</div>

                @if(session()->has('message'))
                <p class="alert {{ session()->get('alert-class', 'alert-danger') }}">{{ session()->get('message') }}</p>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                        {{ $error }}<br/>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="col-xl-11 ml-auto mr-auto mt-2 mb-2">
                @yield('content')
                @include('partials.processing_modal')
            </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
        <div class="row custom-footer">
            <div class="col-12">
                @include('partials.footer')
            </div>
        </div>
        <!-- /footer content -->
    </div>

    <!-- Scripts -->
    <script src="{{ mix('/js/app.js') }}"></script> <!-- for all npm_modules -->
    <script src="{{ asset('js/signature_pad.min.js') }}"></script>

    <!-- Custom Theme Scripts -->
    <!-- <script src="{{ asset('js/custom.js') }}"></script> -->

    <!-- RPLUS JS -->
    <script src="{{ mix('js/rplus.js') }}"></script>
    @yield('scripts')

    <script>
        window.Laravel = <?= json_encode([
            'csrfToken' => csrf_token(),
            'userId' => (!auth()->guest() ? auth()->user()->id : null),
        ]) ?>;

        var submitCanceled = false;
        $(document).ready(function() {
            $("form").submit(function() {
                if (submitCanceled !== true) {
                    processingModal();
                    return true;
                }
            });

        });

    </script>
</body>
</html>
