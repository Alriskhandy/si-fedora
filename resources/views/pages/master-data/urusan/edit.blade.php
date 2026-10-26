@extends('layouts.app')

@section('title', 'Ubah Urusan')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <form action="{{ route('master-urusan.update', $masterUrusan) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Ubah Urusan</h5>
                <a href="{{ route('master-urusan.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
            <div class="card-body">
                @include('pages.master-data.urusan.partials.form', ['masterUrusan' => $masterUrusan])
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </form>
</div>
@endsection
