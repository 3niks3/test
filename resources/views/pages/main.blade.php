@extends('template')
@section('content')

    <div class="jumbotron">
        <h1>Esi sveicināts iBankā!</h1>
        @if(Auth::check())
            <p>Apskati savus kontus vai veic maksājumu.</p>
            <p><a class="btn btn-primary btn-lg" href="{{ route('account') }}" role="button">Apskatīt kontus</a> <a class="btn btn-primary btn-lg" href="{{ route('transactions') }}" role="button">Veikt maksājumu</a></p>
        @else
            <p>Lūdzu, autorizējies.</p>
            <p><a class="btn btn-primary btn-lg" href="{{ route('login') }}" role="button">Autorizēties</a></p>
        @endif
    </div>

@stop
