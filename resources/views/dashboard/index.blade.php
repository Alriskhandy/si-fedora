@extends('layouts.app')

@section('title', 'Dashboard')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(auth()->user()->hasRole('superadmin'))
        @include('dashboard.partials.superadmin')
    @elseif(auth()->user()->hasRole('kaban'))
        @include('dashboard.partials.kaban')
    @elseif(auth()->user()->hasRole('admin_peran'))
        @include('dashboard.partials.admin_peran')
    @elseif(auth()->user()->hasRole('verifikator'))
        @include('dashboard.partials.verifikator')
    @elseif(auth()->user()->hasRole('fasilitator'))
        @include('dashboard.partials.fasilitator')
    @elseif(auth()->user()->hasRole('auditor'))
        @include('dashboard.partials.auditor')
    @elseif(auth()->user()->hasRole('pemohon'))
        @include('dashboard.partials.kab_kota')
    @else
        @include('dashboard.partials.default')
    @endif
</div>
@endsection
