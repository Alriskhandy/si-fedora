@extends('layouts.app')

@section('title', 'Tambah Urusan')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <form action="{{ route('master-urusan.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Tambah Urusan</h5>
                <a href="{{ route('master-urusan.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
            <div class="card-body">
                @include('pages.master-data.urusan.partials.form', ['masterUrusan' => null])
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </form>
</div>
@endsection
