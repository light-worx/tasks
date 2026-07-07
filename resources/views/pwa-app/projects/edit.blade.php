@extends('vendor.pwa.layouts.app')
@php($title = 'Edit project')

@section('content')
    <form method="POST" action="{{ route('app.projects.update', $project) }}">
        @csrf @method('PUT')
        @include('pwa-app.projects._form')
        <button class="btn btn-dark w-100">Save changes</button>
    </form>
@endsection