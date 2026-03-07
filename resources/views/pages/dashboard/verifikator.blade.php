@extends('layouts.app')

@section('title', 'Dashboard Verifikator')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Row 1: Greeting and Notifications -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8 col-md-12">
                <div class="card h-100">
                    <div class="d-flex align-items-end row">
                        <div class="col-sm-7">
                            <div class="card-body">
                                <h3 class="card-title">DASHBOARD SI-FEDORA</h3>
                                <h5 class="card-title text-secondary">Selamat datang, {{ auth()->user()->name }}!</h5>
                                <p class="mb-4">Verifikasi dan evaluasi dokumen permohonan dari kabupaten/kota.</p>
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-4">
                                <img src="../assets/img/illustrations/man-with-laptop-light.png" height="140"
                                    alt="Ilustrasi" data-app-dark-img="illustrations/man-with-laptop-dark.png"
                                    data-app-light-img="illustrations/man-with-laptop-light.png" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <h6 class="card-title mb-3">Notifikasi</h6>
                        <div class="row g-0 text-center">
                            <div class="col-4">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="avatar mb-2">
                                        <i class="bx bx-bell bx-sm"></i>
                                    </span>
                                    <p class="mb-2 small text-muted">Total</p>
                                    <h2 class="mb-0 fw-bold">{{ $stats['notifications']['total'] }}</h2>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="avatar mb-2">
                                        <i class="bx bx-envelope-open bx-sm"></i>
                                    </span>
                                    <p class="mb-2 small text-muted">Belum</p>
                                    <h2 class="mb-0 fw-bold">{{ $stats['notifications']['unread'] }}</h2>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="avatar mb-2">
                                        <i class="bx bx-check-circle bx-sm"></i>
                                    </span>
                                    <p class="mb-2 small text-muted">Sudah</p>
                                    <h2 class="mb-0 fw-bold">{{ $stats['notifications']['read'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Tugas Verifikasi and Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8 col-md-12">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="m-0">Tugas Verifikasi Saya</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Kabupaten/Kota</th>
                                        <th>Jenis Dokumen</th>
                                        <th>Tahun</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stats['my_tasks'] as $permohonan)
                                        <tr>
                                            <td>{{ $permohonan->kabupatenKota->nama ?? '-' }}</td>
                                            <td>{{ $permohonan->jenisDokumen->nama ?? '-' }}</td>
                                            <td>{{ $permohonan->tahun ?? '-' }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ match ($permohonan->status_akhir ?? 'draft') {
                                                        'belum' => 'secondary',
                                                        'proses' => 'primary',
                                                        'revisi' => 'warning',
                                                        'selesai' => 'success',
                                                        default => 'secondary',
                                                    } }}">
                                                    {{ match ($permohonan->status_akhir ?? 'draft') {
                                                        'belum' => 'Belum Diproses',
                                                        'proses' => 'Sedang Diproses',
                                                        'revisi' => 'Perlu Revisi',
                                                        'selesai' => 'Selesai',
                                                        default => 'Draft',
                                                    } }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('permohonan.show', $permohonan->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    Verifikasi
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                Tidak ada tugas verifikasi
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="row row-cols-2 g-4 h-100">
                    <div class="col-12">
                        <div class="card h-100">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="avatar flex-shrink-0">
                                        <span class="avatar-initial rounded bg-label-primary">
                                            <i class='bx bx-task bx-sm'></i>
                                        </span>
                                    </div>
                                    <p class="card-title mb-0 text-muted">Tugas Saya</p>
                                </div>
                                <div class="text-center">
                                    <h2 class="mb-0 fw-bold">{{ $stats['my_verifikasi'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card h-100">
                            <div class="card-body p-3 d-flex flex-column justify-content-center">
                                <div class="avatar mb-2 mx-auto">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class='bx bx-check-circle bx-sm'></i>
                                    </span>
                                </div>
                                <p class="card-title mb-2 text-muted text-center small">Selesai</p>
                                <div class="text-center">
                                    <h3 class="mb-0 fw-bold">{{ $stats['completed_verifikasi'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card h-100">
                            <div class="card-body p-3 d-flex flex-column justify-content-center">
                                <div class="avatar mb-2 mx-auto">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class='bx bx-time-five bx-sm'></i>
                                    </span>
                                </div>
                                <p class="card-title mb-2 text-muted text-center small">Menunggu</p>
                                <div class="text-center">
                                    <h3 class="mb-0 fw-bold">{{ $stats['pending_verifikasi'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Undangan Saya and Info Card -->
        <div class="row g-4">
            <div class="col-lg-8 col-md-12">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="m-0">Undangan Saya</h5>
                        <a href="{{ route('my-undangan.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        @forelse($stats['undangan'] ?? [] as $item)
                            <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span
                                        class="avatar-initial rounded {{ $item->dibaca ? 'bg-label-secondary' : 'bg-label-primary' }}">
                                        <i class='bx {{ $item->dibaca ? 'bx-envelope-open' : 'bx-envelope' }}'></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        Undangan Fasilitasi - {{ $item->undangan->permohonan->kabupatenKota->nama ?? '-' }}
                                        @if (!$item->dibaca)
                                            <span class="badge bg-danger ms-1">Baru</span>
                                        @endif
                                    </h6>
                                    <p class="mb-1 small">
                                        <strong>Jenis Dokumen:</strong>
                                        {{ $item->undangan->permohonan->jenisDokumen->nama ?? '-' }}
                                    </p>
                                    @if ($item->undangan->penetapanJadwal)
                                        <p class="mb-2 small text-muted">
                                            <i class='bx bx-calendar'></i>
                                            {{ \Carbon\Carbon::parse($item->undangan->penetapanJadwal->tanggal_fasilitasi)->format('d M Y') }}
                                            di {{ $item->undangan->penetapanJadwal->lokasi ?? 'Lokasi belum ditentukan' }}
                                        </p>
                                    @endif
                                    <p class="mb-0 small text-muted">
                                        <i class='bx bx-time'></i>
                                        {{ $item->created_at ? $item->created_at->diffForHumans() : '-' }}
                                    </p>
                                </div>
                                <a href="{{ route('my-undangan.view', $item->id) }}" class="btn btn-sm btn-primary ms-2">
                                    Lihat
                                </a>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class='bx bx-envelope bx-lg mb-2 d-block'></i>
                                <p class="small">Tidak ada undangan baru</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Verifikator</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class='bx bx-user'></i>
                                </span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Verifikator</small>
                                <strong>{{ Auth::user()->name }}</strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="mb-2">Status Pekerjaan</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small">Tugas Aktif</span>
                                <strong>{{ $stats['my_verifikasi'] }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small">Selesai Bulan Ini</span>
                                <strong>{{ $stats['completed_verifikasi'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
