@extends('layouts.app')

@push('styles')
    @include('pages.fasilitasi.hasil-fasilitasi.partials.gaya')
@endpush

@section('main')
    @php
        $userId = Auth::id();
        $formatWaktu = fn ($tanggal) => $tanggal?->locale('id')->translatedFormat('d M Y, H:i');
        $renderCatatan = fn ($catatan) => is_string($catatan) || $catatan === null ? (string) $catatan : $catatan->render();

        // Halaman detail hanya-baca: tanpa aksi Ubah/Hapus; saringan "Masukan Saya" hanya bagi yang bisa menginput
        $bolehKelola = fn ($item) => false;
        $denganAksi = false;
        $denganSaya = Auth::user()->can('hasil-fasilitasi.input-urusan');
        $denganOpd = true;

        $daftarTab = array_filter([
            'sistematika' => ['label' => 'Sistematika & Rancangan Akhir', 'ikon' => 'bx-book-content', 'items' => $hasilFasilitasi->hasilSistematika],
            'form' => $showExtendedTabs ? ['label' => 'Konsistensi & Keselarasan', 'ikon' => 'bx-link', 'items' => $hasilFasilitasi->hasilForm] : null,
            'urusan' => ['label' => 'Urusan Pemerintahan', 'ikon' => 'bx-list-ul', 'items' => $hasilFasilitasi->hasilUrusan],
            'rekomendasi' => $showExtendedTabs ? ['label' => 'Rekomendasi', 'ikon' => 'bx-check-shield', 'items' => $hasilFasilitasi->hasilRekomendasi] : null,
        ]);
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
            <div>
                <h4 class="fw-bold mb-1">Detail Hasil Fasilitasi / Evaluasi</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hasil-fasilitasi.index') }}">Hasil Fasilitasi / Evaluasi</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if ($bisaInput)
                    <a href="{{ route('hasil-fasilitasi.create', $permohonan) }}" class="btn btn-primary">
                        <i class="bx bx-edit"></i> Input Masukan
                    </a>
                @endif
                @if ($isKaban && $hasilFasilitasi->status_draft === 'menunggu_persetujuan_kaban')
                    <a href="{{ route('approval.show', $permohonan) }}" class="btn btn-success">
                        <i class="bx bx-check-shield"></i> Review &amp; Setujui
                    </a>
                @endif
                <a href="{{ route('hasil-fasilitasi.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
        </div>

        @foreach (['success' => 'success', 'error' => 'danger', 'info' => 'info'] as $key => $class)
            @if (session($key))
                <div class="alert alert-{{ $class }} alert-dismissible" role="alert">
                    {{ session($key) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif
        @endforeach

        @include('pages.fasilitasi.hasil-fasilitasi.partials.input-ringkasan')

        @if ($isAdmin || $isKoordinator)
            @include('pages.fasilitasi.hasil-fasilitasi.partials.input-dokumen')
        @endif

        <div class="card mb-4">
            <div class="card-header pb-0">
                <h5 class="mb-3"><i class="bx bx-list-check"></i> Masukan Fasilitasi / Evaluasi</h5>
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    @foreach ($daftarTab as $kunci => $tab)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ $kunci }}-tab" data-bs-toggle="tab"
                                data-bs-target="#{{ $kunci }}" data-hash="tab-{{ $kunci }}" type="button" role="tab">
                                <i class="bx {{ $tab['ikon'] }}"></i> {{ $tab['label'] }}
                                <span class="badge rounded-pill {{ $tab['items']->isNotEmpty() ? 'bg-label-primary' : 'bg-label-secondary' }} ms-1">{{ $tab['items']->count() }}</span>
                                @if ($tab['items']->contains('user_id', $userId))
                                    <span class="penanda-saya bg-success ms-1" title="Ada masukan Anda"></span>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content p-0 pt-4">
                    <div class="tab-pane fade show active" id="sistematika" role="tabpanel">
                        <p class="text-muted mb-3"><i class="bx bx-info-circle"></i> Catatan penyempurnaan sistematika dokumen perencanaan per Bab/Sub Bab.</p>
                        @include('pages.fasilitasi.hasil-fasilitasi.partials.daftar-sistematika')
                    </div>

                    @if ($showExtendedTabs)
                        <div class="tab-pane fade" id="form" role="tabpanel">
                            <p class="text-muted mb-3"><i class="bx bx-info-circle"></i> Catatan terkait konsistensi dan keselarasan dokumen perencanaan.</p>
                            @include('pages.fasilitasi.hasil-fasilitasi.partials.daftar-catatan', ['jenis' => 'form', 'items' => $hasilFasilitasi->hasilForm])
                        </div>
                    @endif

                    <div class="tab-pane fade" id="urusan" role="tabpanel">
                        <p class="text-muted mb-3"><i class="bx bx-info-circle"></i> Catatan masukan/saran terhadap urusan pemerintahan konkuren yang diselenggarakan daerah.</p>
                        @include('pages.fasilitasi.hasil-fasilitasi.partials.daftar-urusan')
                    </div>

                    @if ($showExtendedTabs)
                        <div class="tab-pane fade" id="rekomendasi" role="tabpanel">
                            <p class="text-muted mb-3"><i class="bx bx-info-circle"></i> Catatan rekomendasi hasil fasilitasi / evaluasi.</p>
                            @include('pages.fasilitasi.hasil-fasilitasi.partials.daftar-catatan', ['jenis' => 'rekomendasi', 'items' => $hasilFasilitasi->hasilRekomendasi])
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if ($hasilFasilitasi->catatan)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bx bx-note"></i> Catatan</h5>
                </div>
                <div class="card-body">
                    <div class="border rounded p-3" style="white-space: pre-line;">{{ $hasilFasilitasi->catatan }}</div>
                </div>
            </div>
        @endif
    </div>

    @include('pages.fasilitasi.hasil-fasilitasi.partials.preview-pdf')
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('pages.fasilitasi.hasil-fasilitasi.partials.daftar-script')
@endpush
