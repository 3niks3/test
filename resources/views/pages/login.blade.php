@extends('template')
	@section('content')

        <legend>Autorizācija</legend>

        <div class="col-md-5">
        <form action="{{ route('loginPost') }}" method="POST">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="form-group">
                <label>Klienta numurs</label>
                <input name="user_login" type="number" min="1" max="999999" maxlength="6" class="form-control" placeholder="Ievadi klienta numuru" required>
            </div>
            <div class="form-group">
                <label>Parole</label>
                <input name="user_password" type="password" class="form-control" placeholder="Ievadi paroli" required>
            </div>
            <button type="submit" class="btn btn-success">Ienākt</button>
        </form>
        </div>

    @stop
