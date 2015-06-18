@extends('template')
@section('content')


    <p><span class="glyphicon glyphicon-chevron-left"></span><a href="{{ route('account') }}">Uz Mani konti</a></p>
    @if($data==null)
        <h3>Jūs neesat definējis nevienu maksājumu</h3>
    @else
    <table class="table table-bordered">
        <thead>
            <th>Nosaukums</th>
            <th>Jūsu konts</th>
            <th>Saņēmēja konts</th>
            <th>Maksāt</th>
             <th>Dzēst definēto maksājumu</th>
        </thead>
        <tbody>

        @for($i=0;$i< sizeof($data);$i++)
            <tr>
                <td>{{$data[$i]['name']}}</td>
                <td>{{$data[$i]['account_from']}}</td>
                <td>{{$data[$i]['account_to']}}</td>
                <td><a href="{{route('transactionsDef', ['id' =>$data[$i]['id'] ])}}"><button type="button" class="btn btn-primary">Veikt Maksajumu</button></a></td>
                <td>
                    <form action="{{route('fastpaymentsPost')}}" method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="def_id" value="{{ $data[$i]['id']  }}">
                        <button type="submit" class="btn btn-danger">Dzēst definēto maksājumu</button>
                    </form>
                </td>
            </tr>
        @endfor
        </tbody>
    </table>
    @endif
@stop
