@extends('template')
@section('content')

    <legend>Konta izraksts: <small class="text-primary">{{ $account->account_number }}</small></legend>

    <p><span class="glyphicon glyphicon-chevron-left"></span><a href="{{ route('account') }}">Atpakaļ uz Mani konti</a></p>

    <table class="table table-bordered">
        <thead>
        <th>Konta numurs</th>
        <th>Summa</th>
        <th>Maksājuma mērķis</th>
        <th>Datums</th>
        </thead>
        <tbody>

        @foreach($transactions as $transaction => $t)
            @if($t->account_ID != $account->account_ID)
            <tr>


                @if($t->trans_account_ID_to == $account->account_ID)
                    <td><span class="glyphicon glyphicon-arrow-right" style="color: green;" aria-hidden="true"></span> {{ $t->account_number }}</td>
                    <td class="success">+ &euro; {{ number_format($t->trans_sum, 2, '.', ' ') }}</td>
                @else
                    <td><span class="glyphicon glyphicon-arrow-left" style="color: red;" aria-hidden="true"></span> {{ $t->account_number }}</td>
                    <td class="danger">- &euro; {{ number_format($t->trans_sum, 2, '.', ' ') }}</td>
                @endif
                <td>{{ $t->trans_note }}</td>
                <td>{{ date('F j, Y (H:i:s)', strtotime($t->trans_timestamp)) }}</td>
            </tr>
            @endif
        @endforeach

        </tbody>
    </table>

@stop
