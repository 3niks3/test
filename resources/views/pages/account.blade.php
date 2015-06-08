@extends('template')
@section('content')

    <legend>Konta informācija</legend>

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
                <td>...</td>
            </tr>
        @endforeach

        </tbody>
    </table>

@stop
