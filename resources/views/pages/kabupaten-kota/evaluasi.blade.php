@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Evaluasi Kabupaten/Kota: {{ $kabupatenKota->nama }}</h4>
                    @can('evaluasi.create')
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEvaluasiModal">
                        <i class="fas fa-plus"></i> Tambah Evaluasi
                    </a>
                    @endcan
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Aspek</th>
                                    <th>Nilai</th>
                                    <th>Keterangan</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($evaluasi as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->aspek }}</td>
                                    <td>{{ $item->nilai }}</td>
                                    <td>{{ $item->keterangan }}</td>
                                    <td>
                                        @can('evaluasi.edit')
                                        <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editEvaluasiModal{{ $item->id }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('evaluasi.delete')
                                        <form action="{{ route('evaluasi.destroy', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin menghapus data?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Evaluasi -->
@can('evaluasi.create')
<div class="modal fade" id="addEvaluasiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('evaluasi.store') }}" method="POST">
                @csrf
                <input type="hidden" name="kabupaten_kota_id" value="{{ $kabupatenKota->id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Evaluasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form fields here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@endsection