@extends('template')
@section('content')

    <legend>Reģistrācija</legend>

    <div class="col-md-5">
    <form action="{{ route('registerPost') }}" method="POST">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="form-group">
            <label>Lietotāja numurs</label>
            <input name="user_login" type="number" min="1" max="999999" maxlength="6" class="form-control" placeholder="Ievadi lietotāja numuru">
        </div>
        <div class="form-group">
            <label>Parole</label>
            <input name="user_password" type="password" class="form-control" placeholder="Ievadi paroli">
        </div>
        <button type="submit" class="btn btn-success">Reģistrēties</button>
    </form>
    </div>

@stop
