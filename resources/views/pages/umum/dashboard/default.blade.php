@extends('layouts.app')

@section('title', 'Dashboard')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Dashboard</h5>
                    <p class="text-muted">Selamat datang di SIFEDORA</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class='bx bx-user bx-lg text-muted mb-3'></i>
                        <h4>Role Tidak Dikenali</h4>
                        <p class="text-muted">Silakan hubungi administrator untuk pengaturan role yang sesuai</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection