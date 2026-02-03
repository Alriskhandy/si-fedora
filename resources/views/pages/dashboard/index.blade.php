@extends('layouts.app')

@section('title', 'Dashboard')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(auth()->user()->hasRole('superadmin'))
        @include('pages.dashboard.partials.superadmin')
    @elseif(auth()->user()->hasRole('kaban'))
        @include('pages.dashboard.partials.kaban')
    @elseif(auth()->user()->hasRole('admin_peran'))
        @include('pages.dashboard.partials.admin_peran')
    @elseif(auth()->user()->hasRole('verifikator'))
        @include('pages.dashboard.partials.verifikator')
    @elseif(auth()->user()->hasRole('fasilitator'))
        @include('pages.dashboard.partials.fasilitator')
    @elseif(auth()->user()->hasRole('auditor'))
        @include('pages.dashboard.partials.auditor')
    @elseif(auth()->user()->hasRole('pemohon'))
        @include('pages.dashboard.partials.kab_kota')
    @else
        @include('pages.dashboard.partials.default')
    @endif
</div>
@endsection
