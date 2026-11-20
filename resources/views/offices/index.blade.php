@extends('layouts.app')

@section('title', "Offices & Departments — LMO Records Registry")

@section('content')
<div class="lmo-ph">
    <div>
        <h1>Offices &amp; departments</h1>
        <p>Offices and departments are now grouped under Categories.</p>
    </div>
    <a href="{{ route('categories.index', ['tab' => 'offices']) }}" class="btn-lmo-primary">
        Open under Categories
    </a>
</div>
@endsection
