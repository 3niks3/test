@extends('template')
@section('content')

    <div class="row">

        <div class="col-md-5">
            <legend>Veikt maksājumu</legend>

            <form action="{{ route('transactionsPost') }}" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="form-group">
                    <label><b>Izvēlies kontu</b></label>
                    <select name="trans_account_ID_from" class="form-control">
                        @foreach($accounts as $account => $a)
                            <option value="{{ $a->account_ID }}">{{ $a->account_number }}  (&euro; {{ number_format($a->account_balance, 2, '.', ' ') }} )</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label><b>Saņēmēja konts</b></label>
                    <input name="trans_account_number" type="text" class="form-control" placeholder="Ievadi saņēmēja kontu">
                </div>
                <div class="form-group">
                    <label><b>Summa</b></label>
                    <input name="trans_sum" type="number" min="1" class="form-control" placeholder="Ievadi summu">
                </div>
                <div class="form-group">
                    <label><b>Maksājuma mērķis</b></label>
                    <input name="trans_note" type="text" class="form-control" placeholder="Ievadi maksājuma mērķi">
                </div>
                <button type="submit" class="btn btn-success">Izpildīt maksājumu</button>
            </form>

        </div>

        <div class="col-md-7">
            <legend>Izpildītie maksājumi</legend>

            <table class="table table-bordered table-stripped">
                @foreach($transactions as $transaction => $t)
                    <tr>
                        <td>{{ $t->trans_ID }}</td>
                        <td>{{ $t->trans_sum }}</td>
                        <td>{{ $t->trans_note }}</td>
                        <td>{{ date('F j, Y (H:i:s)', strtotime($t->trans_timestamp)) }}</td>
                    </tr>
                    @endforeach
            </table>

        </div>
    </div>




@stop
