@extends('vendor.pwa.layouts.app')
@php($title = 'New project')

@section('content')
    <form method="POST" action="{{ route('app.projects.store') }}">
        @csrf
        @include('pwa-app.projects._form')
        <button class="btn btn-dark w-100">Create project</button>
    </form>
@endsection