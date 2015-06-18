@extends('template')
@section('content')



            <legend><span class="glyphicon glyphicon-credit-card" style="color: royalblue;" aria-hidden="true"></span> Veikt maksājumu</legend>

            <form action="{{ route('transactionsPost') }}" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="form-group">
                    <label><b>Izvēlies kontu</b></label>
                    <select name="trans_account_ID_from" class="form-control">
                        @foreach($accounts as $account => $a)
                            <option @if(isset($data) && $data['From']==$a->account_ID) selected @endif value="{{ $a->account_ID }}">{{ $a->account_number }}  (&euro; {{ number_format($a->account_balance, 2, '.', ' ') }} )</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label><b>Saņēmēja konts</b></label>
                    <input @if(isset($data)) value ="{{$data['To']}}" @endif name="trans_account_number" type="text" class="form-control" placeholder="Ievadi saņēmēja kontu" required>
                </div>
                <div class="form-group">
                    <label><b>Summa</b></label>
                    <input name="trans_sum" type="number" min="0.01" step="0.01" class="form-control" placeholder="Ievadi summu" required>
                </div>
                <div class="form-group">
                    <label><b>Maksājuma mērķis</b></label>
                    <input name="trans_note" type="text" class="form-control" placeholder="Ievadi maksājuma mērķi" required>
                </div>
                <button type="submit" class="btn btn-success">Izpildīt maksājumu</button>
            </form>



@stop
