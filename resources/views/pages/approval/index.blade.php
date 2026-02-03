@extends('layouts.app')

@section('title', 'Approval Draft Rekomendasi')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Approval Draft Rekomendasi</h5>
                </div>
                <div class="card-body">
                    @if($permohonan->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kabupaten/Kota</th>
                                    <th>Jenis Dokumen</th>
                                    <th>Tanggal Permohonan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permohonan as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->kabupatenKota->getFullNameAttribute() ?? '-' }}</td>
                                    <td>{{ $item->jenisDokumen->nama ?? '-' }}</td>
                                    <td>{{ $item->created_at->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge bg-label-warning">Draft Rekomendasi</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('approval.show', $item) }}" class="btn btn-sm btn-primary">
                                            <i class="bx bx-show me-1"></i> Review
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $permohonan->links() }}
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class='bx bx-file-find bx-lg mb-2 text-muted'></i>
                        <p class="text-muted">Tidak ada draft rekomendasi yang menunggu approval.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection