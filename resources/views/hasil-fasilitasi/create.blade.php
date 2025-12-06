@extends('layouts.app')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Input Hasil Fasilitasi</h4>
            <div>
                @if ($hasilFasilitasi && ($hasilFasilitasi->hasilSistematika->count() > 0 || $hasilFasilitasi->hasilUrusan->count() > 0))
                    <a href="{{ route('hasil-fasilitasi.generate', $permohonan->id) }}" class="btn btn-success me-2">
                        <i class="bx bx-file"></i> Generate Word
                    </a>
                    <a href="{{ route('hasil-fasilitasi.generate-pdf', $permohonan->id) }}" class="btn btn-primary me-2">
                        <i class="bx bxs-file-pdf"></i> Generate PDF
                    </a>
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

                            <dt class="col-sm-5">No. Permohonan</dt>
                            <dd class="col-sm-7">{{ $permohonan->no_permohonan }}</dd>

                            <dt class="col-sm-5">Tanggal</dt>
                            <dd class="col-sm-7">{{ $permohonan->created_at->format('d M Y') }}</dd>
                        </dl>
                    </div>
                </div>
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
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Bab / Sub Bab <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="bab_sub_bab"
                                                            placeholder="Contoh: BAB I, BAB II.1" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <label class="form-label">Catatan Penyempurnaan <span
                                                                class="text-danger">*</span></label>
                                                        <textarea class="form-control" id="catatan_penyempurnaan" rows="2" required></textarea>
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
                                                        <th width="65%">Catatan Penyempurnaan</th>
                                                        <th width="10%">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($hasilFasilitasi->hasilSistematika as $item)
                                                        <tr class="sistematika-item" data-id="{{ $item->id }}">
                                                            <td class="text-center">{{ $loop->iteration }}</td>
                                                            <td><strong
                                                                    class="text-primary">{{ $item->bab_sub_bab }}</strong>
                                                            </td>
                                                            <td>{{ $item->catatan_penyempurnaan }}</td>
                                                            <td class="text-center">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger btn-hapus-sistematika"
                                                                    data-id="{{ $item->id }}">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
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
                                                <div class="col-md-4">
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
                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <label class="form-label">Catatan Masukan / Saran <span
                                                                class="text-danger">*</span></label>
                                                        <textarea class="form-control" id="catatan_masukan" rows="2" required></textarea>
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
                                                        <th width="60%">Catatan Masukan / Saran</th>
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
                                                            <td>{{ $item->catatan_masukan }}</td>
                                                            <td class="text-center">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger btn-hapus-urusan"
                                                                    data-id="{{ $item->id }}">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
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
    <script>
        const permohonanId = {{ $permohonan->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Submit Form Sistematika
        document.getElementById('formSistematika').addEventListener('submit', async function(e) {
            e.preventDefault();

            const babSubBab = document.getElementById('bab_sub_bab').value;
            const catatanPenyempurnaan = document.getElementById('catatan_penyempurnaan').value;

            try {
                const response = await fetch(`/hasil-fasilitasi/${permohonanId}/sistematika`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        bab_sub_bab: babSubBab,
                        catatan_penyempurnaan: catatanPenyempurnaan
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Reset form
                    document.getElementById('formSistematika').reset();

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
                                            <th width="65%">Catatan Penyempurnaan</th>
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

                    const newRow = `
                        <tr class="sistematika-item" data-id="${data.data.id}">
                            <td class="text-center">${rowCount}</td>
                            <td><strong class="text-primary">${data.data.bab_sub_bab}</strong></td>
                            <td>${data.data.catatan_penyempurnaan}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger btn-hapus-sistematika" data-id="${data.data.id}">
                                    <i class="bx bx-trash"></i>
                                </button>
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
            const catatanMasukan = document.getElementById('catatan_masukan').value;
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
                                            <th width="60%">Catatan Masukan / Saran</th>
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

                    const newRow = `
                        <tr class="urusan-item" data-id="${data.data.id}">
                            <td class="text-center">${rowCount}</td>
                            <td><strong class="text-primary">${namaUrusan}</strong></td>
                            <td>${data.data.catatan_masukan}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger btn-hapus-urusan" data-id="${data.data.id}">
                                    <i class="bx bx-trash"></i>
                                </button>
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
