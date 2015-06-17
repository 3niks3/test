@extends('template')
@section('content')

    <legend><span class="glyphicon glyphicon-tasks" style="color: forestgreen;" aria-hidden="true"></span> Kontu informācija</legend>

    <table class="table table-bordered">
        <thead>
            <th>Konta numurs</th>
            <th>Bilance</th>
            <th>Iespējas</th>
        </thead>
        <tbody>

        @foreach($accounts as $account => $a)
            <tr>
                <td>{{ $a->account_number }}</td>
                <td>&euro; {{ number_format($a->account_balance, 2, '.', ' ') }}</td>
                <td><a href="{{ route('accountSummary', $a->account_ID) }}" class="btn btn-default">Skatīt izrakstu</a>
                    <a href="{{ route('company', $a->account_ID) }}" class="btn btn-default">Izveidot firmas kontu</a>

                </td>
            </tr>
        @endforeach

        </tbody>
    </table>

@stop
