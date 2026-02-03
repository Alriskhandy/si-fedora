@extends('layouts.app')

@push('styles')
    <style>
        .tinymce-wrapper {
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
        }
    </style>
@endpush

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">
                Input Hasil Fasilitasi
                @if ($isKoordinator)
                    <span class="badge bg-success">Koordinator</span>
                @endif
            </h4>
            <div>
                @if (
                    $isKoordinator &&
                        $hasilFasilitasi &&
                        ($hasilFasilitasi->hasilSistematika->count() > 0 || $hasilFasilitasi->hasilUrusan->count() > 0))
                    <a href="{{ route('hasil-fasilitasi.generate', $permohonan->id) }}" class="btn btn-primary me-2"
                        data-bs-toggle="tooltip"
                        title="Hanya koordinator (fasilitator dengan is_pic) yang dapat generate dokumen">
                        <i class="bx bxs-file-doc"></i> Generate Word
                    </a>
                    <a href="{{ route('hasil-fasilitasi.generate-pdf', $permohonan->id) }}" class="btn btn-danger me-2"
                        data-bs-toggle="tooltip"
                        title="Hanya koordinator (fasilitator dengan is_pic) yang dapat generate dokumen">
                        <i class="bx bxs-file-pdf"></i> Generate PDF
                    </a>
                @elseif (
                    !$isKoordinator &&
                        $hasilFasilitasi &&
                        ($hasilFasilitasi->hasilSistematika->count() > 0 || $hasilFasilitasi->hasilUrusan->count() > 0))
                    <span class="text-muted me-2" data-bs-toggle="tooltip"
                        title="Hanya koordinator tim yang dapat generate dokumen">
                        <i class="bx bx-info-circle"></i> Generate dokumen: Hanya Koordinator
                    </span>
                @endif
                <a href="{{ route('hasil-fasilitasi.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
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

                            <dt class="col-sm-5">Jenis Dokumen</dt>
                            <dd class="col-sm-7"><span
                                    class="badge bg-primary">{{ strtoupper($permohonan->jenis_dokumen) }}</span></dd>

                            <dt class="col-sm-5">Tahun</dt>
                            <dd class="col-sm-7">{{ $permohonan->tahun }}</dd>

                            <dt class="col-sm-5">No. Permohonan</dt>
                            <dd class="col-sm-7">{{ $permohonan->no_permohonan }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- Informasi Tim -->
                @if ($timInfo)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bx bx-group"></i> Tim Fasilitasi</h5>
                        </div>
                        <div class="card-body">
                            @if ($timInfo['verifikator'])
                                <div class="mb-2">
                                    <small class="text-muted">PIC Verifikator:</small>
                                    <div><strong>{{ $timInfo['verifikator']->user->name }}</strong></div>
                                </div>
                            @endif

                            @if ($timInfo['koordinator'])
                                <div class="mb-2">
                                    <small class="text-muted">Koordinator Fasilitator:</small>
                                    <div>
                                        <strong class="text-primary">{{ $timInfo['koordinator']->user->name }}</strong>
                                        @if ($timInfo['koordinator']->user_id == Auth::id())
                                            <span class="badge bg-success">Anda</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if ($timInfo['anggota']->count() > 0)
                                <div class="mb-0">
                                    <small class="text-muted">Anggota Fasilitator:</small>
                                    @foreach ($timInfo['anggota'] as $anggota)
                                        <div>
                                            {{ $anggota->user->name }}
                                            @if ($anggota->user_id == Auth::id())
                                                <span class="badge bg-success">Anda</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                <!-- Jadwal Fasilitasi -->
                @if ($permohonan->penetapanJadwal)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Jadwal Fasilitasi</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-5">Tanggal</dt>
                                <dd class="col-sm-7">
                                    {{ $permohonan->penetapanJadwal->tanggal_mulai->format('d M Y') }} -
                                    {{ $permohonan->penetapanJadwal->tanggal_selesai->format('d M Y') }}
                                </dd>

                                <dt class="col-sm-5">Lokasi</dt>
                                <dd class="col-sm-7">{{ $permohonan->penetapanJadwal->lokasi ?? '-' }}</dd>
                            </dl>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                <!-- Ringkasan -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Ringkasan</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Sistematika</dt>
                            <dd class="col-sm-7">
                                <span class="badge bg-primary"
                                    id="badge-sistematika">{{ $hasilFasilitasi->hasilSistematika->count() }}</span> item
                            </dd>

                            <dt class="col-sm-5">Urusan</dt>
                            <dd class="col-sm-7">
                                <span class="badge bg-primary"
                                    id="badge-urusan">{{ $hasilFasilitasi->hasilUrusan->count() }}</span> item
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <!-- Tabs untuk Sistematika dan Urusan -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bx bx-list-check"></i> Masukan Fasilitasi</h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" id="sistematika-tab" data-bs-toggle="tab"
                                    data-bs-target="#sistematika" type="button" role="tab">
                                    <i class="bx bx-book-content"></i> Sistematika & Rancangan Akhir
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="urusan-tab" data-bs-toggle="tab" data-bs-target="#urusan"
                                    type="button" role="tab">
                                    <i class="bx bx-list-ul"></i> Urusan Pemerintahan
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-4">
                            <!-- Tab 1: Sistematika -->
                            <div class="tab-pane fade show active" id="sistematika" role="tabpanel">
                                <p class="text-muted mb-3">
                                    <i class="bx bx-info-circle"></i> Catatan penyempurnaan terhadap sistematika
                                    dokumen perencanaan per Bab/Sub Bab
                                </p>

                                <!-- Form Tambah Sistematika -->
                                <div class="card border-primary mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bx bx-plus-circle"></i> Tambah Item Sistematika
                                        </h6>
                                        <form id="formSistematika">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Pilih Bab <span
                                                                class="text-danger">*</span></label>
                                                        <select class="form-select" id="master_bab_id" required>
                                                            <option value="">-- Pilih Bab --</option>
                                                            @foreach ($masterBabList as $bab)
                                                                <option value="{{ $bab->id }}">{{ $bab->nama_bab }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Sub Bab (Opsional)</label>
                                                        <input type="text" class="form-control" id="sub_bab"
                                                            placeholder="Contoh: 1.2 Dasar Hukum">
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Catatan Penyempurnaan <span
                                                                class="text-danger">*</span></label>
                                                        <textarea class="form-control tinymce-editor" id="catatan_penyempurnaan" rows="3"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus"></i> Tambah Item
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- List Sistematika -->
                                <div id="listSistematika">
                                    @if ($hasilFasilitasi->hasilSistematika->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="5%">No</th>
                                                        <th width="20%">Bab / Sub Bab</th>
                                                        <th width="50%">Catatan Penyempurnaan</th>
                                                        <th width="15%">Oleh</th>
                                                        <th width="10%">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($hasilFasilitasi->hasilSistematika as $item)
                                                        <tr class="sistematika-item" data-id="{{ $item->id }}">
                                                            <td class="text-center">{{ $loop->iteration }}</td>
                                                            <td>
                                                                <strong
                                                                    class="text-primary">{{ $item->masterBab->nama_bab ?? '-' }}</strong>
                                                                @if ($item->sub_bab)
                                                                    <br><small
                                                                        class="text-muted">{{ $item->sub_bab }}</small>
                                                                @endif
                                                            </td>
                                                            <td>{!! is_string($item->catatan_penyempurnaan)
                                                                ? $item->catatan_penyempurnaan
                                                                : $item->catatan_penyempurnaan->render() !!}</td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    {{ $item->user->name ?? '-' }}<br>
                                                                    <span
                                                                        class="text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                                                                </small>
                                                            </td>
                                                            <td class="text-center">
                                                                @if ($isKoordinator || $item->user_id == Auth::id())
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger btn-hapus-sistematika"
                                                                        data-id="{{ $item->id }}">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                @else
                                                                    <span class="text-muted small">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info" id="emptySistematika">
                                            <i class="bx bx-info-circle"></i> Belum ada item sistematika. Tambahkan item
                                            menggunakan form di atas.
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Tab 2: Urusan Pemerintahan -->
                            <div class="tab-pane fade" id="urusan" role="tabpanel">
                                <p class="text-muted mb-3">
                                    <i class="bx bx-info-circle"></i> Catatan masukan/saran terhadap urusan
                                    pemerintahan konkuren yang diselenggarakan daerah
                                </p>

                                <!-- Form Tambah Urusan -->
                                <div class="card border-primary mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bx bx-plus-circle"></i> Tambah Item Urusan</h6>
                                        <form id="formUrusan">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Pilih Urusan <span
                                                                class="text-danger">*</span></label>
                                                        <select class="form-select" id="master_urusan_id" required>
                                                            <option value="">-- Pilih Urusan --</option>
                                                            @foreach ($masterUrusanList as $urusan)
                                                                <option value="{{ $urusan->id }}">
                                                                    {{ $urusan->urutan }}. {{ $urusan->nama_urusan }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Catatan Masukan / Saran <span
                                                                class="text-danger">*</span></label>
                                                        <textarea class="form-control tinymce-editor" id="catatan_masukan" rows="3"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus"></i> Tambah Item
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- List Urusan -->
                                <div id="listUrusan">
                                    @if ($hasilFasilitasi->hasilUrusan->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="5%">No</th>
                                                        <th width="25%">Urusan Pemerintahan</th>
                                                        <th width="45%">Catatan Masukan / Saran</th>
                                                        <th width="15%">Oleh</th>
                                                        <th width="10%">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($hasilFasilitasi->hasilUrusan as $item)
                                                        <tr class="urusan-item" data-id="{{ $item->id }}">
                                                            <td class="text-center">{{ $loop->iteration }}</td>
                                                            <td><strong
                                                                    class="text-primary">{{ $item->masterUrusan->urutan }}.
                                                                    {{ $item->masterUrusan->nama_urusan }}</strong></td>
                                                            <td>{!! is_string($item->catatan_masukan) ? $item->catatan_masukan : $item->catatan_masukan->render() !!}</td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    {{ $item->user->name ?? '-' }}<br>
                                                                    <span
                                                                        class="text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                                                                </small>
                                                            </td>
                                                            <td class="text-center">
                                                                @if ($isKoordinator || $item->user_id == Auth::id())
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger btn-hapus-urusan"
                                                                        data-id="{{ $item->id }}">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                @else
                                                                    <span class="text-muted small">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info" id="emptyUrusan">
                                            <i class="bx bx-info-circle"></i> Belum ada item urusan. Tambahkan item
                                            menggunakan form di atas.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.tiny.cloud/1/{{ env('TINY_MCE_API_KEY') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin">
    </script>
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '.tinymce-editor',
            height: 200,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
                'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
                'bold italic underline strikethrough | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | table | code | help',
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px }',
            branding: false,
            promotion: false,
            statusbar: false,

            // Paste settings
            paste_as_text: false,
            paste_webkit_styles: 'font-weight font-style text-decoration',
            paste_retain_style_properties: 'color font-size font-weight font-style text-decoration',

            // Clean up HTML
            valid_elements: 'p,br,strong/b,em/i,u,s,strike,del,h1,h2,h3,h4,h5,h6,' +
                'ul,ol,li,a[href|title|target],blockquote,pre,code,table,thead,tbody,tr,th,td,' +
                'span[class|style],div[class|style],sub,sup',

            valid_styles: {
                '*': 'color,font-size,font-weight,font-style,text-decoration,text-align'
            },

            // Setup function
            setup: function(editor) {
                editor.on('init', function() {
                    console.log('TinyMCE initialized for: ' + editor.id);
                });
            }
        });

        const permohonanId = {{ $permohonan->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const isKoordinator = {{ $isKoordinator ? 'true' : 'false' }};
        const currentUserId = {{ Auth::id() }};

        // Submit Form Sistematika
        document.getElementById('formSistematika').addEventListener('submit', async function(e) {
            e.preventDefault();

            const masterBabId = document.getElementById('master_bab_id').value;
            const subBab = document.getElementById('sub_bab').value;

            // Get content from TinyMCE
            const catatanPenyempurnaan = tinymce.get('catatan_penyempurnaan').getContent();

            // Ambil nama bab dari dropdown
            const selectElement = document.getElementById('master_bab_id');
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const namaBab = selectedOption.text;

            try {
                const response = await fetch(`/hasil-fasilitasi/${permohonanId}/sistematika`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        master_bab_id: masterBabId,
                        sub_bab: subBab,
                        catatan_penyempurnaan: catatanPenyempurnaan
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Reset form
                    document.getElementById('formSistematika').reset();
                    tinymce.get('catatan_penyempurnaan').setContent('');

                    // Tambahkan ke list
                    const listSistematika = document.getElementById('listSistematika');
                    const emptyAlert = document.getElementById('emptySistematika');

                    if (emptyAlert) {
                        // Jika belum ada tabel, buat tabel baru
                        emptyAlert.remove();
                        listSistematika.innerHTML = `
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="20%">Bab / Sub Bab</th>
                                            <th width="50%">Catatan Penyempurnaan</th>
                                            <th width="15%">Oleh</th>
                                            <th width="10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableSistematika"></tbody>
                                </table>
                            </div>
                        `;
                    }

                    const tbody = document.getElementById('tableSistematika') || listSistematika.querySelector(
                        'tbody');
                    const rowCount = tbody.querySelectorAll('tr').length + 1;

                    // Format tampilan bab/sub bab
                    let displayBabSubBab = `<strong class="text-primary">${namaBab}</strong>`;
                    if (subBab) {
                        displayBabSubBab += `<br><small class="text-muted">${subBab}</small>`;
                    }

                    // Check if user can delete (koordinator or owner)
                    const canDelete = isKoordinator || data.data.user_id === currentUserId;
                    const deleteButton = canDelete ?
                        `<button type="button" class="btn btn-sm btn-danger btn-hapus-sistematika" data-id="${data.data.id}">
                                <i class="bx bx-trash"></i>
                            </button>` :
                        `<span class="text-muted small">-</span>`;

                    const newRow = `
                        <tr class="sistematika-item" data-id="${data.data.id}">
                            <td class="text-center">${rowCount}</td>
                            <td>${displayBabSubBab}</td>
                            <td>${data.data.catatan_penyempurnaan}</td>
                            <td><small class="text-muted">${data.data.user ? data.data.user.name : '-'}<br><span class="text-secondary">${data.data.created_at}</span></small></td>
                            <td class="text-center">
                                ${deleteButton}
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', newRow);

                    // Update badge count
                    updateBadgeCount('sistematika');

                    showToast('success', data.message);
                } else {
                    showToast('error', data.error || 'Gagal menambahkan item');
                }
            } catch (error) {
                showToast('error', 'Terjadi kesalahan: ' + error.message);
            }
        });

        // Submit Form Urusan
        document.getElementById('formUrusan').addEventListener('submit', async function(e) {
            e.preventDefault();

            const masterUrusanId = document.getElementById('master_urusan_id').value;

            // Get content from TinyMCE
            const catatanMasukan = tinymce.get('catatan_masukan').getContent();

            const selectElement = document.getElementById('master_urusan_id');
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const namaUrusan = selectedOption.text;

            try {
                const response = await fetch(`/hasil-fasilitasi/${permohonanId}/urusan`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        master_urusan_id: masterUrusanId,
                        catatan_masukan: catatanMasukan
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Reset form
                    document.getElementById('formUrusan').reset();
                    tinymce.get('catatan_masukan').setContent('');

                    // Tambahkan ke list
                    const listUrusan = document.getElementById('listUrusan');
                    const emptyAlert = document.getElementById('emptyUrusan');

                    if (emptyAlert) {
                        // Jika belum ada tabel, buat tabel baru
                        emptyAlert.remove();
                        listUrusan.innerHTML = `
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="25%">Urusan Pemerintahan</th>
                                            <th width="45%">Catatan Masukan / Saran</th>
                                            <th width="15%">Oleh</th>
                                            <th width="10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableUrusan"></tbody>
                                </table>
                            </div>
                        `;
                    }

                    const tbody = document.getElementById('tableUrusan') || listUrusan.querySelector('tbody');
                    const rowCount = tbody.querySelectorAll('tr').length + 1;

                    // Check if user can delete (koordinator or owner)
                    const canDelete = isKoordinator || data.data.user_id === currentUserId;
                    const deleteButton = canDelete ?
                        `<button type="button" class="btn btn-sm btn-danger btn-hapus-urusan" data-id="${data.data.id}">
                                <i class="bx bx-trash"></i>
                            </button>` :
                        `<span class="text-muted small">-</span>`;

                    const newRow = `
                        <tr class="urusan-item" data-id="${data.data.id}">
                            <td class="text-center">${rowCount}</td>
                            <td><strong class="text-primary">${namaUrusan}</strong></td>
                            <td>${data.data.catatan_masukan}</td>
                            <td><small class="text-muted">${data.data.user ? data.data.user.name : '-'}<br><span class="text-secondary">${data.data.created_at}</span></small></td>
                            <td class="text-center">
                                ${deleteButton}
                            </td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', newRow);

                    // Update badge count
                    updateBadgeCount('urusan');

                    showToast('success', data.message);
                } else {
                    showToast('error', data.error || 'Gagal menambahkan item');
                }
            } catch (error) {
                showToast('error', 'Terjadi kesalahan: ' + error.message);
            }
        });

        // Event delegation untuk tombol hapus
        document.addEventListener('click', async function(e) {
            // Hapus Sistematika
            if (e.target.closest('.btn-hapus-sistematika')) {
                const btn = e.target.closest('.btn-hapus-sistematika');
                const id = btn.dataset.id;

                if (confirm('Hapus item sistematika ini?')) {
                    try {
                        const response = await fetch(`/hasil-fasilitasi/${permohonanId}/sistematika/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            const row = document.querySelector(`.sistematika-item[data-id="${id}"]`);
                            row.remove();

                            // Update nomor urut
                            const tbody = document.getElementById('tableSistematika') || document.querySelector(
                                '#listSistematika tbody');
                            if (tbody) {
                                const rows = tbody.querySelectorAll('tr');
                                rows.forEach((row, index) => {
                                    row.querySelector('td:first-child').textContent = index + 1;
                                });
                            }

                            // Update badge count
                            updateBadgeCount('sistematika');

                            // Tampilkan pesan kosong jika tidak ada item
                            const listSistematika = document.getElementById('listSistematika');
                            if (!listSistematika.querySelector('.sistematika-item')) {
                                listSistematika.innerHTML =
                                    '<div class="alert alert-info" id="emptySistematika"><i class="bx bx-info-circle"></i> Belum ada item sistematika.</div>';
                            }

                            showToast('success', data.message);
                        } else {
                            showToast('error', data.error || 'Gagal menghapus item');
                        }
                    } catch (error) {
                        showToast('error', 'Terjadi kesalahan: ' + error.message);
                    }
                }
            }

            // Hapus Urusan
            if (e.target.closest('.btn-hapus-urusan')) {
                const btn = e.target.closest('.btn-hapus-urusan');
                const id = btn.dataset.id;

                if (confirm('Hapus item urusan ini?')) {
                    try {
                        const response = await fetch(`/hasil-fasilitasi/${permohonanId}/urusan/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            const row = document.querySelector(`.urusan-item[data-id="${id}"]`);
                            row.remove();

                            // Update nomor urut
                            const tbody = document.getElementById('tableUrusan') || document.querySelector(
                                '#listUrusan tbody');
                            if (tbody) {
                                const rows = tbody.querySelectorAll('tr');
                                rows.forEach((row, index) => {
                                    row.querySelector('td:first-child').textContent = index + 1;
                                });
                            }

                            // Update badge count
                            updateBadgeCount('urusan');

                            // Tampilkan pesan kosong jika tidak ada item
                            const listUrusan = document.getElementById('listUrusan');
                            if (!listUrusan.querySelector('.urusan-item')) {
                                listUrusan.innerHTML =
                                    '<div class="alert alert-info" id="emptyUrusan"><i class="bx bx-info-circle"></i> Belum ada item urusan.</div>';
                            }

                            showToast('success', data.message);
                        } else {
                            showToast('error', data.error || 'Gagal menghapus item');
                        }
                    } catch (error) {
                        showToast('error', 'Terjadi kesalahan: ' + error.message);
                    }
                }
            }
        });

        // Update badge count
        function updateBadgeCount(type) {
            const items = document.querySelectorAll(`.${type}-item`);
            const badge = document.getElementById(`badge-${type}`);
            if (badge) {
                badge.textContent = items.length;
            }
        }

        // Show toast notification
        function showToast(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className =
                `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible position-fixed top-0 start-50 translate-middle-x mt-3`;
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }
    </script>
@endpush
