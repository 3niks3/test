@extends('template')

@section('content')
<div class="col-md-5">
    {!! Form::open(['route' => ['company_create', $id]]) !!}

    <div class="form-group">
        {!! Form::label('callback') !!}
        {!! Form::text('callback', null, ['class'=>'form-control','required' =>'required']) !!}
    </div>

    {!! Form::submit('Apstiprināt', ['class'=>'btn btn-danger'])!!}

    {!! Form::close() !!}
</div>

@stop