@extends('vendor.pwa.layouts.app')
@php($title = 'Edit task')

@section('content')
    <form method="POST" action="{{ route('app.tasks.update', $task) }}">
        @csrf @method('PUT')
        @include('pwa-app.tasks._form')
        <button class="btn btn-dark w-100">Save changes</button>
    </form>
@endsection