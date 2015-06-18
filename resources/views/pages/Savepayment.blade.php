@extends('template')
@section('content')

   save payments


   		 <legend><span class="glyphicon glyphicon-book" style="color: purple;" aria-hidden="true"></span> Definēt maksājumu</legend>

                    <form action="{{route('SavepaymentPost')}}" method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="form-group">
                              <label><b>Nosaukums</b></label>
                               <input name="name" type="text" class="form-control" placeholder="Ievadi definētā maksājuma nosaukumu" required>
                        </div>
                        <div class="form-group">
                            <label><b>Konts</b></label><br>
                            <label>{{$data['From_account_number']}}</label>
                        </div>
                        <div class="form-group">
                            <label><b>Saņēmēja konts</b></label><br>
                            <label>{{$data['To_account_number']}}</label>
                        </div>
                        <input type="hidden" name="accountFrom" value="{{ $data['From'] }}">
                        <input type="hidden" name="accountTo" value="{{ $data['To'] }}">

                        <button type="submit" class="btn btn-success">Saglabāt</button>
                    </form>


@stop
