@extends('vendor.pwa.layouts.app')
@php($title = 'New task')

@section('content')
    <form method="POST" action="{{ route('app.tasks.store') }}">
        @csrf
        @include('pwa-app.tasks._form')
        <button class="btn btn-dark w-100">Create task</button>
    </form>
@endsection