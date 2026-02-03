@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Detail Undangan Pelaksanaan</h4>
            <a href="{{ route('undangan-pelaksanaan.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info alert-dismissible" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-4">
                <!-- Informasi Permohonan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Permohonan</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Kabupaten/Kota</dt>
                            <dd class="col-sm-7">{{ $permohonan->kabupatenKota->nama }}</dd>

                            <dt class="col-sm-5">No. Permohonan</dt>
                            <dd class="col-sm-7">{{ $permohonan->no_permohonan }}</dd>

                            <dt class="col-sm-5">Tanggal</dt>
                            <dd class="col-sm-7">{{ $permohonan->created_at->format('d M Y') }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- Status Undangan -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Status Undangan</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Status</dt>
                            <dd class="col-sm-7">
                                @if ($undangan->status == 'draft')
                                    <span class="badge bg-warning">Draft</span>
                                @else
                                    <span class="badge bg-success">Terkirim</span>
                                @endif
                            </dd>

                            <dt class="col-sm-5">Dibuat Oleh</dt>
                            <dd class="col-sm-7">{{ $undangan->pembuat->name }}</dd>

                            <dt class="col-sm-5">Tanggal Dibuat</dt>
                            <dd class="col-sm-7">{{ $undangan->tanggal_dibuat->format('d M Y H:i') }}</dd>

                            @if ($undangan->tanggal_dikirim)
                                <dt class="col-sm-5">Tanggal Dikirim</dt>
                                <dd class="col-sm-7">{{ $undangan->tanggal_dikirim->format('d M Y H:i') }}</dd>
                            @endif

                            <dt class="col-sm-5">Penerima</dt>
                            <dd class="col-sm-7">{{ $undangan->jumlah_penerima }} orang</dd>

                            @if ($undangan->isTerkirim())
                                <dt class="col-sm-5">Sudah Dibaca</dt>
                                <dd class="col-sm-7">{{ $undangan->jumlah_dibaca }} /
                                    {{ $undangan->jumlah_penerima }}</dd>
                            @endif
                        </dl>

                        @if ($undangan->isDraft())
                            <form method="POST" action="{{ route('undangan-pelaksanaan.send', $permohonan) }}"
                                onsubmit="return confirm('Kirim undangan ini ke semua penerima?')">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100 mt-3">
                                    <i class="bx bx-send"></i> Kirim Undangan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Detail Undangan -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detail Undangan</h5>
                        @if ($undangan->file_undangan)
                            <a href="{{ route('undangan-pelaksanaan.download', $permohonan) }}"
                                class="btn btn-sm btn-info">
                                <i class="bx bx-download"></i> Download PDF
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Nomor Undangan</dt>
                            <dd class="col-sm-9">{{ $undangan->nomor_undangan }}</dd>

                            <dt class="col-sm-3">Perihal</dt>
                            <dd class="col-sm-9">{{ $undangan->perihal }}</dd>

                            <dt class="col-sm-3">Isi Undangan</dt>
                            <dd class="col-sm-9">
                                <div class="border rounded p-3" style="white-space: pre-line;">
                                    {{ $undangan->isi_undangan }}</div>
                            </dd>
                        </dl>

                        <hr class="my-3">

                        <h6 class="mb-3">Jadwal Fasilitasi</h6>
                        <dl class="row mb-0">
                            <dt class="col-sm-3">Tanggal</dt>
                            <dd class="col-sm-9">
                                {{ $undangan->penetapanJadwal->tanggal_mulai->format('d F Y') }} -
                                {{ $undangan->penetapanJadwal->tanggal_selesai->format('d F Y') }}
                            </dd>

                            <dt class="col-sm-3">Lokasi</dt>
                            <dd class="col-sm-9">{{ $undangan->penetapanJadwal->lokasi ?? '-' }}</dd>

                            <dt class="col-sm-3">Durasi</dt>
                            <dd class="col-sm-9">{{ $undangan->penetapanJadwal->durasi_hari }} hari</dd>
                        </dl>
                    </div>
                </div>

                <!-- Daftar Penerima -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Daftar Penerima Undangan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Jenis</th>
                                        <th>Status</th>
                                        <th>Tanggal Dibaca</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($undangan->penerima as $penerima)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $penerima->user->name }}</td>
                                            <td>{{ $penerima->user->email }}</td>
                                            <td>
                                                <span class="badge bg-label-primary">
                                                    {{ ucfirst($penerima->jenis_penerima) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($penerima->dibaca)
                                                    <span class="badge bg-success">Sudah Dibaca</span>
                                                @else
                                                    <span class="badge bg-secondary">Belum Dibaca</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $penerima->tanggal_dibaca ? $penerima->tanggal_dibaca->format('d M Y H:i') : '-' }}
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
@endsection
