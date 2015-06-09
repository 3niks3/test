@extends('template')
@section('content')

    <ul class="nav nav-tabs h5" role="tablist" id="myTab">
        <li role="presentation" class="active"><a href="#payment" aria-controls="payment" role="tab" data-toggle="tab">Veikt maksājumu</a></li>
        <li role="presentation"><a href="#transactions" aria-controls="transactions" role="tab" data-toggle="tab">Transakcijas</a></li>
    </ul>

    <hr/>

        <div class="tab-content">

        <div role="tabpanel" class="col-md-6 tab-pane fade in active" id="payment">
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
                    <input name="trans_sum" type="number" min="1" step="0.01" class="form-control" placeholder="Ievadi summu">
                </div>
                <div class="form-group">
                    <label><b>Maksājuma mērķis</b></label>
                    <input name="trans_note" type="text" class="form-control" placeholder="Ievadi maksājuma mērķi" required>
                </div>
                <button type="submit" class="btn btn-success">Izpildīt maksājumu</button>
            </form>

        </div>

            <div role="tabpanel" class="col-md-12 tab-pane fade" id="transactions">

                <div class="col-md-6">
            <legend>Izpildītie maksājumi</legend>

            <table class="table table-bordered table-striped table-condensed small">
                <thead>
                <th>ID</th>
                <th>No</th>
                <th>Uz</th>
                <th>Summa</th>
                <th>Maksājuma mērķis</th>
                <th>Datums</th>
                </thead>
                <tbody>
                @for($i=0; $i<sizeof($transactions['out']); $i++)
                @foreach($transactions['out'][$i] as $transaction => $t)
                    <tr>
                        <td>{{ $t->trans_ID }}</td>
                        <td>{{ $t->trans_account_ID_from }}</td>
                        <td>{{ $t->trans_account_ID_to }}</td>
                        <td class="danger">- &euro; {{ number_format($t->trans_sum, 2, '.', ' ') }}</td>
                        <td>{{ $t->trans_note }}</td>
                        <td>{{ date('F j, Y (H:i:s)', strtotime($t->trans_timestamp)) }}</td>
                    </tr>
                @endforeach
                @endfor
                </tbody>
            </table>
                    </div>

                <div class="col-md-6">

            <legend>Saņemtie maksājumi</legend>

            <table class="table table-bordered table-striped table-condensed small">
                <thead>
                <th>ID</th>
                <th>No</th>
                <th>Uz</th>
                <th>Summa</th>
                <th>Maksājuma mērķis</th>
                <th>Datums</th>
                </thead>
                <tbody>
                @for($i=0; $i<sizeof($transactions['in']); $i++)
                    @foreach($transactions['in'][$i] as $transaction => $t)
                        <tr>
                            <td>{{ $t->trans_ID }}</td>
                            <td>{{ $t->trans_account_ID_from }}</td>
                            <td>{{ $t->trans_account_ID_to }}</td>
                            <td class="success">+ &euro; {{ number_format($t->trans_sum, 2, '.', ' ') }}</td>
                            <td>{{ $t->trans_note }}</td>
                            <td>{{ date('F j, Y (H:i:s)', strtotime($t->trans_timestamp)) }}</td>
                        </tr>
                    @endforeach
                @endfor
                </tbody>
            </table>
                    </div>

        </div>

        </div> <!-- end tabs -->


@stop
