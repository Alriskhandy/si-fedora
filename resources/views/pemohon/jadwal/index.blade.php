@extends('layouts.app')

@section('title', 'Jadwal Fasilitasi')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Jadwal Fasilitasi</h4>
                <p class="text-muted mb-0">Daftar jadwal fasilitasi/evaluasi dokumen perencanaan yang tersedia</p>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('pemohon.jadwal.index') }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="jenis_dokumen" class="form-label">Jenis Dokumen</label>
                            <select name="jenis_dokumen" id="jenis_dokumen" class="form-select">
                                <option value="">Semua Jenis Dokumen</option>
                                @foreach ($filterJenisDokumen as $item)
                                    <option value="{{ $item->id }}"
                                        {{ request('jenis_dokumen') == $item->nam ? 'selected' : '' }}>
                                        {{ $item->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select name="tahun" id="tahun" class="form-select">
                                <option value="">Semua Tahun</option>
                                @foreach ($filterOptions['tahunList'] as $tahun)
                                    <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                                        {{ $tahun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class='bx bx-search me-1'></i> Filter
                            </button>
                            <a href="{{ route('pemohon.jadwal.index') }}" class="btn btn-secondary">
                                <i class='bx bx-reset me-1'></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Jadwal List -->
        <div class="row">
            @forelse($jadwalList as $jadwal)
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1">{{ $jadwal->jenisDokumen->nama }}
                                        {{ $jadwal->tahun_anggaran }}</h5>
                                    <span class="badge bg-label-primary">
                                        {{ strtoupper($jadwal->jenisDokumen->nama) }}
                                    </span>
                                </div>
                                <span
                                    class="badge bg-label-{{ $jadwal->status == 'published' ? 'success' : 'secondary' }}">
                                    {{ $jadwal->status_label }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Tahun Anggaran</small>
                                        <strong>{{ $jadwal->tahun_anggaran }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Batas Permohonan</small>
                                        <strong
                                            class="text-{{ $jadwal->batas_permohonan && $jadwal->batas_permohonan < now() ? 'danger' : 'success' }}">
                                            {{ $jadwal->batas_permohonan ? $jadwal->batas_permohonan->format('d M Y') : '-' }}
                                        </strong>
                                        <span class="text-muted"> (
                                            {{ \Carbon\Carbon::now()->diffForHumans($jadwal->batas_permohonan, [
                                                'parts' => 2,
                                                'short' => true,
                                                'syntax' => \Carbon\Carbon::DIFF_ABSOLUTE,
                                            ]) }} Lagi )
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Periode Fasilitasi</small>
                                <span>{{ $jadwal->tanggal_mulai->format('d M Y') }} -
                                    {{ $jadwal->tanggal_selesai->format('d M Y') }}</span>
                            </div>

                            @if ($jadwal->undangan_file)
                                <div class="mb-3">
                                    <a href="{{ route('pemohon.jadwal.download', $jadwal->id) }}"
                                        class="btn btn-sm btn-outline-success">
                                        <i class='bx bx-download me-1'></i> Unduh Penyampaian Jadwal
                                    </a>
                                </div>
                            @endif

                            <div class="d-flex gap-2 mt-3">
                                <a href="{{ route('pemohon.jadwal.show', $jadwal->id) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class='bx bx-show me-1'></i> Detail
                                </a>
                                @if ($jadwal->status == 'published' && $jadwal->batas_permohonan && $jadwal->batas_permohonan >= now())
                                    <a href="{{ route('permohonan.create', ['jadwal_id' => $jadwal->id]) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class='bx bx-plus me-1'></i> Buat Permohonan
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class='bx bx-calendar-x bx-lg text-muted mb-3'></i>
                            <p class="text-muted mb-0">Belum ada jadwal fasilitasi yang tersedia</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($jadwalList->hasPages())
            <div class="mt-4">
                {{ $jadwalList->links() }}
            </div>
        @endif
    </div>
@endsection
