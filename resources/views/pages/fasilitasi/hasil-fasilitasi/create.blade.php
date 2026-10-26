@extends('layouts.app')

@push('styles')
    @include('pages.fasilitasi.hasil-fasilitasi.partials.gaya')
@endpush

@section('main')
    @php
        $userId = Auth::id();
        $bolehKelola = fn ($item) => ! $isLocked && ($isKoordinator || $item->user_id == $userId);
        $formatWaktu = fn ($tanggal) => $tanggal?->locale('id')->translatedFormat('d M Y, H:i');

        $semuaTab = [
            'sistematika' => ['label' => 'Sistematika & Rancangan Akhir', 'ikon' => 'bx-book-content', 'items' => $hasilFasilitasi->hasilSistematika],
            'form' => ['label' => 'Konsistensi & Keselarasan', 'ikon' => 'bx-link', 'items' => $hasilFasilitasi->hasilForm],
            'urusan' => ['label' => 'Urusan Pemerintahan', 'ikon' => 'bx-list-ul', 'items' => $hasilFasilitasi->hasilUrusan],
            'rekomendasi' => ['label' => 'Rekomendasi', 'ikon' => 'bx-check-shield', 'items' => $hasilFasilitasi->hasilRekomendasi],
        ];
        $bisaTambah = ! $isLocked;
        $denganOpd = ! $isUrusanOnly;
        $daftarTab = $isUrusanOnly ? ['urusan' => $semuaTab['urusan']] : $semuaTab;

        $urusanPerKode = $hasilFasilitasi->hasilUrusan->groupBy('master_urusan_id')
            ->map(fn ($items) => $items->sortBy('created_at')->values());
        $urusanBelumDiisi = $masterUrusanList->whereNotIn('id', $urusanPerKode->keys()->all());

        $renderCatatan = fn ($catatan) => is_string($catatan) || $catatan === null ? (string) $catatan : $catatan->render();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
            <div>
                <h4 class="fw-bold mb-1">Input Hasil Fasilitasi / Evaluasi</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hasil-fasilitasi.index') }}">Hasil Fasilitasi / Evaluasi</a></li>
                        <li class="breadcrumb-item active">Input</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('hasil-fasilitasi.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back"></i> Kembali
            </a>
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

        @if ($isLocked)
            <div class="alert alert-warning d-flex gap-2" role="alert">
                <i class="bx bx-lock-alt fs-4"></i>
                <div>
                    <strong>Masukan terkunci.</strong>
                    Hasil fasilitasi sedang dinilai atau sudah disetujui Kepala BAPPEDA. Masukan tidak dapat ditambah, diubah, atau dihapus.
                    <div class="small mt-1">
                        @if ($hasilFasilitasi->status_draft === 'menunggu_persetujuan_kaban')
                            Kunci terbuka kembali bila Kepala Badan mengembalikan hasil untuk diperbaiki.
                        @else
                            Hasil sudah disetujui; tidak ada perubahan masukan lagi.
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ($isAdmin || $isKoordinator)
            @include('pages.fasilitasi.hasil-fasilitasi.partials.input-dokumen')
        @endif

        <div class="card">
            <div class="card-header pb-0">
                <h5 class="mb-3"><i class="bx bx-list-check"></i> Masukan Fasilitasi / Evaluasi</h5>
                @if ($isUrusanOnly && $masterUrusanList->isEmpty())
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle"></i> OPD Anda belum memiliki urusan pemerintahan bidang. Hubungi Admin untuk melengkapinya di master OPD.
                    </div>
                @endif
                @if (count($daftarTab) > 1)
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
                @endif
            </div>
            <div class="card-body">
                <div class="tab-content p-0 pt-4">
                    @unless ($isUrusanOnly)
                        <!-- Tab: Sistematika -->
                        <div class="tab-pane fade show active" id="sistematika" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <p class="text-muted mb-0"><i class="bx bx-info-circle"></i> Catatan penyempurnaan sistematika dokumen perencanaan per Bab/Sub Bab.</p>
                                @unless ($isLocked)
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#tambah-sistematika">
                                        <i class="bx bx-plus"></i> Tambah Masukan
                                    </button>
                                @endunless
                            </div>

                            @unless ($isLocked)
                                <div class="collapse js-tambah" id="tambah-sistematika">
                                    <div class="card shadow-none border border-primary mb-4">
                                        <div class="card-body">
                                            <form class="js-form-tambah" data-bagian="sistematika" data-url="{{ route('hasil-fasilitasi.sistematika.store', $permohonan) }}" novalidate>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label" for="master_bab_id">Bab <span class="text-danger">*</span></label>
                                                        <select class="form-select" id="master_bab_id" name="master_bab_id" required>
                                                            <option value="">-- Pilih Bab --</option>
                                                            @foreach ($masterBabList as $bab)
                                                                <option value="{{ $bab->id }}">{{ $bab->nama_bab }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label" for="sub_bab">Sub Bab (Opsional)</label>
                                                        <input type="text" class="form-control" id="sub_bab" name="sub_bab" placeholder="Contoh: 1.2 Dasar Hukum">
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" for="catatan_penyempurnaan">Catatan Penyempurnaan <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" id="catatan_penyempurnaan" data-kolom="catatan_penyempurnaan" rows="3"></textarea>
                                                </div>
                                                @include('pages.fasilitasi.hasil-fasilitasi.partials.input-tombol-form')
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endunless

                            @include('pages.fasilitasi.hasil-fasilitasi.partials.daftar-sistematika')
                        </div>

                        <!-- Tab: Konsistensi & Keselarasan -->
                        <div class="tab-pane fade" id="form" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <p class="text-muted mb-0"><i class="bx bx-info-circle"></i> Catatan terkait konsistensi dan keselarasan dokumen perencanaan.</p>
                                @unless ($isLocked)
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#tambah-form">
                                        <i class="bx bx-plus"></i> Tambah Masukan
                                    </button>
                                @endunless
                            </div>

                            @unless ($isLocked)
                                <div class="collapse js-tambah" id="tambah-form">
                                    <div class="card shadow-none border border-primary mb-4">
                                        <div class="card-body">
                                            <form class="js-form-tambah" data-bagian="form" data-url="{{ route('hasil-fasilitasi.form.store', $permohonan) }}" novalidate>
                                                <div class="mb-3">
                                                    <label class="form-label" for="catatan_form">Catatan <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" id="catatan_form" data-kolom="catatan" rows="3"></textarea>
                                                </div>
                                                @include('pages.fasilitasi.hasil-fasilitasi.partials.input-tombol-form')
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endunless

                            @include('pages.fasilitasi.hasil-fasilitasi.partials.daftar-catatan', ['jenis' => 'form', 'items' => $hasilFasilitasi->hasilForm])
                        </div>
                    @endunless

                    <!-- Tab: Urusan Pemerintahan -->
                    <div class="tab-pane fade {{ $isUrusanOnly ? 'show active' : '' }}" id="urusan" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <p class="text-muted mb-0"><i class="bx bx-info-circle"></i> Catatan masukan/saran terhadap urusan pemerintahan konkuren yang diselenggarakan daerah.</p>
                            @unless ($isLocked || $masterUrusanList->isEmpty())
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#tambah-urusan">
                                    <i class="bx bx-plus"></i> Tambah Masukan
                                </button>
                            @endunless
                        </div>

                        @if ($isUrusanOnly && $masterUrusanList->isNotEmpty())
                            <div class="border rounded p-3 mb-3" id="urusan-bidang">
                                @if ($urusanBelumDiisi->isEmpty())
                                    <span class="text-success"><i class="bx bx-check-circle"></i> Semua urusan bidang OPD Anda sudah diberi masukan.</span>
                                @else
                                    <div class="small fw-semibold mb-2">
                                        Urusan bidang OPD Anda yang belum diberi masukan ({{ $urusanBelumDiisi->count() }} dari {{ $masterUrusanList->count() }}):
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($urusanBelumDiisi as $urusan)
                                            @if ($isLocked)
                                                <span class="badge bg-label-secondary">{{ $urusan->kode_urusan }} {{ $urusan->nama_urusan }}</span>
                                            @else
                                                <button type="button" class="btn btn-xs btn-outline-primary js-tambah-urusan" data-urusan-id="{{ $urusan->id }}">
                                                    <i class="bx bx-plus"></i> {{ $urusan->kode_urusan }} {{ $urusan->nama_urusan }}
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        @unless ($isLocked || $masterUrusanList->isEmpty())
                            <div class="collapse js-tambah" id="tambah-urusan">
                                <div class="card shadow-none border border-primary mb-4">
                                    <div class="card-body">
                                        <form class="js-form-tambah" data-bagian="urusan" data-url="{{ route('hasil-fasilitasi.urusan.store', $permohonan) }}" novalidate>
                                            <div class="mb-3">
                                                <label class="form-label" for="master_urusan_id">Urusan Pemerintahan <span class="text-danger">*</span></label>
                                                @if ($masterUrusanList->count() > 5)
                                                    <input type="search" class="form-control form-control-sm mb-2 js-cari-opsi" data-target="master_urusan_id"
                                                        placeholder="Cari kode atau nama urusan..." aria-label="Cari urusan">
                                                @endif
                                                <select class="form-select" id="master_urusan_id" name="master_urusan_id" required>
                                                    <option value="">-- Pilih Urusan --</option>
                                                    @foreach ($masterUrusanList as $urusan)
                                                        <option value="{{ $urusan->id }}">{{ $urusan->kode_urusan }} {{ $urusan->nama_urusan }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="catatan_masukan">Catatan Masukan / Saran <span class="text-danger">*</span></label>
                                                <textarea class="form-control" id="catatan_masukan" data-kolom="catatan_masukan" rows="3"></textarea>
                                            </div>
                                            @include('pages.fasilitasi.hasil-fasilitasi.partials.input-tombol-form')
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endunless

                        @include('pages.fasilitasi.hasil-fasilitasi.partials.daftar-urusan')
                    </div>

                    @unless ($isUrusanOnly)
                        <!-- Tab: Rekomendasi -->
                        <div class="tab-pane fade" id="rekomendasi" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <p class="text-muted mb-0"><i class="bx bx-info-circle"></i> Catatan rekomendasi hasil fasilitasi / evaluasi.</p>
                                @unless ($isLocked)
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#tambah-rekomendasi">
                                        <i class="bx bx-plus"></i> Tambah Masukan
                                    </button>
                                @endunless
                            </div>

                            @unless ($isLocked)
                                <div class="collapse js-tambah" id="tambah-rekomendasi">
                                    <div class="card shadow-none border border-primary mb-4">
                                        <div class="card-body">
                                            <form class="js-form-tambah" data-bagian="rekomendasi" data-url="{{ route('hasil-fasilitasi.rekomendasi.store', $permohonan) }}" novalidate>
                                                <div class="mb-3">
                                                    <label class="form-label" for="catatan_rekomendasi">Catatan <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" id="catatan_rekomendasi" data-kolom="catatan" rows="3"></textarea>
                                                </div>
                                                @include('pages.fasilitasi.hasil-fasilitasi.partials.input-tombol-form')
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endunless

                            @include('pages.fasilitasi.hasil-fasilitasi.partials.daftar-catatan', ['jenis' => 'rekomendasi', 'items' => $hasilFasilitasi->hasilRekomendasi])
                        </div>
                    @endunless
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ubah Masukan (dipakai keempat bagian) -->
    <div class="modal fade" id="modalUbah" tabindex="-1" aria-labelledby="modalUbahJudul" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUbahJudul"><i class="bx bx-edit me-2"></i>Ubah Masukan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    @unless ($isUrusanOnly)
                        <div class="row js-field-ubah" data-untuk="sistematika">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="ubah_master_bab_id">Bab <span class="text-danger">*</span></label>
                                <select class="form-select" id="ubah_master_bab_id">
                                    @foreach ($masterBabList as $bab)
                                        <option value="{{ $bab->id }}">{{ $bab->nama_bab }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="ubah_sub_bab">Sub Bab (Opsional)</label>
                                <input type="text" class="form-control" id="ubah_sub_bab" placeholder="Contoh: 1.2 Dasar Hukum">
                            </div>
                        </div>
                    @endunless
                    <div class="mb-3 js-field-ubah" data-untuk="urusan">
                        <label class="form-label" for="ubah_master_urusan_id">Urusan Pemerintahan <span class="text-danger">*</span></label>
                        <select class="form-select" id="ubah_master_urusan_id">
                            @foreach ($masterUrusanList as $urusan)
                                <option value="{{ $urusan->id }}">{{ $urusan->kode_urusan }} {{ $urusan->nama_urusan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="form-label" for="ubah_catatan">Catatan <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="ubah_catatan" rows="4"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSimpanUbah"><i class="bx bx-save"></i> Simpan</button>
                </div>
            </div>
        </div>
    </div>

    @include('pages.fasilitasi.hasil-fasilitasi.partials.preview-pdf')
@endsection

@push('scripts')
    <script src="https://cdn.tiny.cloud/1/{{ env('TINY_MCE_API_KEY') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('pages.fasilitasi.hasil-fasilitasi.partials.daftar-script')
    @include('pages.fasilitasi.hasil-fasilitasi.partials.input-script')
@endpush
