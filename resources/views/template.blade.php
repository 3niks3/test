<html lang="en"><head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title>iBanka</title>

    <!-- Bootstrap core CSS -->
    <link href="http://localhost/bank/public/css/bootstrap.min.css" rel="stylesheet">
    <link href="http://localhost/bank/public/css/style.css" rel="stylesheet">

<body>

<!-- Fixed navbar -->
<nav class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ url('/') }}">iBanka</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                @if(Auth::check())
                    <li><a href="{{ url('/account') }}"><span class="glyphicon glyphicon-tasks" style="color: forestgreen;" aria-hidden="true"></span> Mani konti</a></li>
                    <li><a href="{{ url('/transactions') }}"><span class="glyphicon glyphicon-credit-card" style="color: royalblue;" aria-hidden="true"></span> Maksājumi</a></li>
                    <li><a href="{{ url('/fastpayments') }}"><span class="glyphicon glyphicon-book" style="color: purple;" aria-hidden="true"></span> Mani definētie maksājumi</a></li>
                    <li><a href="{{ url('/logout') }}"><span class="glyphicon glyphicon-remove-sign" style="color: orangered;" aria-hidden="true"></span> Iziet</a></li>
                @else
                    <li><a href="{{ url('/login') }}"><span class="glyphicon glyphicon-user" style="color: royalblue;" aria-hidden="true"></span> Autorizēties</a></li>
                @endif
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>

<div class="container">

    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        @if (Session::has('success'))
            <div class="alert alert-success">
                {{ Session::get('success') }}
            </div>
        @endif


        <div class="panel panel-default">
        <div class="panel-body">
            @yield('content')
        </div>
    </div>

</div>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="/js/bootstrap.min.js"></script>

<script>
    $('#myTab a').click(function (e) {
        e.preventDefault()
        $(this).tab('show')
    })
</script>


</body></html>