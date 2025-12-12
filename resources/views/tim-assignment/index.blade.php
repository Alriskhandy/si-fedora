@extends('layouts.app')

@section('title', 'Tim Assignment')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            Tim FEDORA (Fasilitasi / Evaluasi Dokumen Perencanaan)
        </h4>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Button Tambah -->
        <div class="mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#timModal">
                <i class="bx bx-plus me-1"></i> Tambah Tim
            </button>

        </div>

        <div class="row">
            <!-- List Tim by Kabkota -->
            <div class="col-12">
                <!-- Filter Card -->
                <div class="card mb-3">
                    <div class="card-body py-2">
                        <form method="GET">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Kabupaten/Kota</label>
                                    <select name="kabkota" class="form-select form-select-sm">
                                        <option value="">Semua</option>
                                        @foreach ($allKabkota as $kabkota)
                                            <option value="{{ $kabkota->id }}"
                                                {{ request('kabkota') == $kabkota->id ? 'selected' : '' }}>
                                                {{ $kabkota->getFullNameAttribute() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Jenis Dokumen</label>
                                    <select name="jenis_dokumen" class="form-select form-select-sm">
                                        <option value="">Semua</option>
                                        @foreach ($jenisDokumenList as $jenisDokumen)
                                            <option value="{{ $jenisDokumen->id }}"
                                                {{ request('jenis_dokumen') == $jenisDokumen->id ? 'selected' : '' }}>
                                                {{ $jenisDokumen->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Tahun</label>
                                    <select name="tahun" class="form-select form-select-sm">
                                        <option value="">Semua</option>
                                        @for ($year = date('Y'); $year >= 2020; $year--)
                                            <option value="{{ $year }}"
                                                {{ request('tahun') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Status</label>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="">Semua</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif
                                        </option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                            Nonaktif</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="bx bx-search-alt"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tim Assignment Cards by Kabkota -->
                @forelse ($kabkotaList as $kabkota)
                    @php
                        $assignments = $kabkota->userAssignments;
                        if ($assignments->isEmpty()) {
                            continue;
                        }

                        // Group assignments by tahun and jenis_dokumen_id
                        $groupedTims = $assignments->groupBy(function ($item) {
                            return $item->tahun . '_' . ($item->jenis_dokumen_id ?? 'null');
                        });
                    @endphp

                    <div class="card mb-3" id="kabkota_{{ $kabkota->id }}">
                        <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                            data-bs-target="#collapse_{{ $kabkota->id }}" style="cursor: pointer;">
                            <div>
                                <h6 class="mb-0">
                                    <i class="bx bx-map-pin text-primary me-2"></i>
                                    {{ $kabkota->getFullNameAttribute() }}
                                </h6>
                                <small class="text-muted">
                                    <i class="bx bx-briefcase"></i> {{ $groupedTims->count() }} Tim
                                </small>
                            </div>
                            <i class="bx bx-chevron-down chevron-icon"></i>
                        </div>
                        <div class="collapse show" id="collapse_{{ $kabkota->id }}">
                            <div class="card-body p-2">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0" style="font-size: 0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 6%;">Tahun</th>
                                                <th style="width: 16%;">Jenis Dokumen</th>
                                                <th style="width: 18%;">PIC Verifikator</th>
                                                <th style="width: 26%;">Anggota Fasilitator</th>
                                                <th style="width: 10%;" class="text-center">SK Tim</th>
                                                <th style="width: 10%;" class="text-center">Status</th>
                                                <th style="width: 14%;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($groupedTims as $timGroup)
                                                @php
                                                    $firstAssignment = $timGroup->first();
                                                    $pic = $timGroup->where('is_pic', true)->first();
                                                    $anggota = $timGroup->where('is_pic', false);
                                                    $allActive = $timGroup->every(fn($a) => $a->is_active);
                                                @endphp
                                                <tr>
                                                    <td class="fw-bold text-center">{{ $firstAssignment->tahun }}</td>
                                                    <td>
                                                        @if ($firstAssignment->jenisDokumen)
                                                            <span
                                                                class="badge bg-label-info">{{ $firstAssignment->jenisDokumen->nama }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($pic)
                                                            <div class="d-flex align-items-center">
                                                                <i class="bx bx-star text-warning me-2"></i>
                                                                <div>
                                                                    <strong>{{ $pic->user->name }}</strong><br>
                                                                    <small
                                                                        class="text-muted">{{ $pic->user->email }}</small>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">Tidak ada PIC</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($anggota->count() > 0)
                                                            <div style="max-height: 100px; overflow-y: auto;">
                                                                @foreach ($anggota as $member)
                                                                    <div class="mb-1">
                                                                        <i class="bx bx-user text-primary"
                                                                            style="font-size: 0.85rem;"></i>
                                                                        <small>
                                                                            {{ $member->user->name }}
                                                                            <span
                                                                                class="text-muted">({{ $member->user->email }})</span>
                                                                        </small>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span class="text-muted">Tidak ada anggota</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($firstAssignment->file_sk && file_exists(public_path($firstAssignment->file_sk)))
                                                            <a href="{{ route('tim-assignment.download-sk', $firstAssignment->id) }}"
                                                                target="_blank" class="btn btn-sm btn-outline-info"
                                                                title="Lihat SK Tim">
                                                                <i class="bx bx-file-blank"></i> Lihat
                                                            </a>
                                                        @elseif($firstAssignment->file_sk)
                                                            <small class="text-warning">File tidak ada</small>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <div
                                                            class="form-check form-switch d-flex justify-content-center mb-0">
                                                            <input class="form-check-input" type="checkbox"
                                                                {{ $allActive ? 'checked' : '' }}
                                                                onchange="toggleTimStatus('{{ $kabkota->id }}', '{{ $firstAssignment->tahun }}', '{{ $firstAssignment->jenis_dokumen_id ?? '' }}', this)"
                                                                title="{{ $allActive ? 'Aktif' : 'Nonaktif' }}">
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($pic)
                                                            <button type="button"
                                                                class="btn btn-sm btn-icon btn-outline-primary"
                                                                onclick="editAssignment({{ $pic->id }})"
                                                                title="Edit Tim">
                                                                <i class="bx bx-edit"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bx bx-info-circle bx-lg text-muted mb-3"></i>
                            <p class="text-muted mb-0">Tidak ada data Tim FEDORA</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Modal Form Tim Assignment -->
    <div class="modal fade" id="timModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formTitle">Tambah Tim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('tim-assignment.store') }}" method="POST" enctype="multipart/form-data"
                    id="timForm">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="assignment_id" id="assignmentId">

                    <div class="modal-body">
                        <!-- Inputan Wajib -->
                        <div class="mb-3">
                            <h6 class="text-primary mb-3">
                                <i class="bx bx-info-circle"></i> Inputan Wajib
                            </h6>
                            <div class="border-start border-primary border-3 ps-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <!-- Jenis Dokumen -->
                                        <div class="mb-3">
                                            <label class="form-label" for="jenis_dokumen_id">Jenis Dokumen <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select @error('jenis_dokumen_id') is-invalid @enderror"
                                                id="jenis_dokumen_id" name="jenis_dokumen_id" required>
                                                <option value="">Pilih Jenis Dokumen</option>
                                                @foreach ($jenisDokumenList as $jenisDokumen)
                                                    <option value="{{ $jenisDokumen->id }}"
                                                        {{ old('jenis_dokumen_id') == $jenisDokumen->id ? 'selected' : '' }}>
                                                        {{ $jenisDokumen->nama }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('jenis_dokumen_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <!-- Kabupaten/Kota -->
                                        <div class="mb-3">
                                            <label class="form-label" for="kabupaten_kota_id">Kabupaten/Kota <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select @error('kabupaten_kota_id') is-invalid @enderror"
                                                id="kabupaten_kota_id" name="kabupaten_kota_id" required>
                                                <option value="">Pilih Kabupaten/Kota</option>
                                                @foreach ($kabkotaListForm as $kabkota)
                                                    <option value="{{ $kabkota->id }}"
                                                        {{ old('kabupaten_kota_id') == $kabkota->id ? 'selected' : '' }}>
                                                        {{ $kabkota->getFullNameAttribute() }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('kabupaten_kota_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <!-- Tahun -->
                                        <div class="mb-3">
                                            <label class="form-label" for="tahun">Tahun <span
                                                    class="text-danger">*</span></label>
                                            <input type="number"
                                                class="form-control @error('tahun') is-invalid @enderror" id="tahun"
                                                name="tahun" required min="2020" max="2100"
                                                value="{{ old('tahun', date('Y')) }}">
                                            @error('tahun')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- PIC Verifikator -->
                                <div class="mb-3">
                                    <label class="form-label" for="verifikator_id">PIC / Verifikator <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select @error('verifikator_id') is-invalid @enderror"
                                        id="verifikator_id" name="verifikator_id" required>
                                        <option value="">Pilih Verifikator</option>
                                        @foreach ($verifikators as $verifikator)
                                            <option value="{{ $verifikator->id }}"
                                                {{ old('verifikator_id') == $verifikator->id ? 'selected' : '' }}>
                                                {{ $verifikator->name }} - {{ $verifikator->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('verifikator_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Anggota Fasilitator -->
                                <div class="mb-3">
                                    <label class="form-label">Anggota Fasilitator <span
                                            class="text-danger">*</span></label>
                                    <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                        @foreach ($fasilitators as $fasilitator)
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" name="evaluator_ids[]"
                                                    value="{{ $fasilitator->id }}" id="evaluator_{{ $fasilitator->id }}"
                                                    {{ is_array(old('evaluator_ids')) && in_array($fasilitator->id, old('evaluator_ids')) ? 'checked' : '' }}>
                                                <label class="form-check-label small"
                                                    for="evaluator_{{ $fasilitator->id }}">
                                                    {{ $fasilitator->name }} - {{ $fasilitator->email }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="text-muted">Pilih minimal 1 fasilitator</small>
                                </div>
                            </div>
                        </div>

                        <!-- Inputan Opsional -->
                        <div class="mb-2">
                            <h6 class="text-secondary mb-3">
                                <i class="bx bx-list-ul"></i> SK Tim (Opsional)
                            </h6>
                            <div class="border-start border-secondary border-3 ps-3">
                                <div class="row">
                                    <!-- Nomor Surat -->
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="nomor_surat">Nomor Surat</label>
                                            <input type="text"
                                                class="form-control @error('nomor_surat') is-invalid @enderror"
                                                id="nomor_surat" name="nomor_surat" value="{{ old('nomor_surat') }}">
                                            @error('nomor_surat')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Periode -->
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="assigned_from">Tanggal Mulai</label>
                                            <input type="date"
                                                class="form-control @error('assigned_from') is-invalid @enderror"
                                                id="assigned_from" name="assigned_from"
                                                value="{{ old('assigned_from') }}">
                                            @error('assigned_from')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="assigned_until">Tanggal Akhir</label>
                                            <input type="date"
                                                class="form-control @error('assigned_until') is-invalid @enderror"
                                                id="assigned_until" name="assigned_until"
                                                value="{{ old('assigned_until') }}">
                                            @error('assigned_until')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- File SK -->
                                <div>
                                    <label class="form-label" for="file_sk">File SK Tim (PDF)</label>
                                    <input type="file" class="form-control @error('file_sk') is-invalid @enderror"
                                        id="file_sk" name="file_sk" accept=".pdf">
                                    @error('file_sk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Maks. 10MB, format PDF</small>
                                    <div id="currentFile" class="mt-2" style="display: none;">
                                        <small class="text-info">
                                            <i class="bx bx-file"></i> <span id="fileName"></span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x me-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> <span id="btnSubmitText">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Edit Assignment (will be implemented via AJAX)
        function editAssignment(id) {
            fetch(`/tim-assignment/${id}/edit`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Open modal
                    const modal = new bootstrap.Modal(document.getElementById('timModal'));
                    modal.show();

                    // Update form
                    document.getElementById('formTitle').textContent = 'Edit Tim Assignment';
                    document.getElementById('btnSubmitText').textContent = 'Update';
                    document.getElementById('formMethod').value = 'PUT';
                    document.getElementById('assignmentId').value = id;
                    document.getElementById('timForm').action = `/tim-assignment/${id}`;

                    // Populate form fields
                    document.getElementById('jenis_dokumen_id').value = data.jenis_dokumen_id || '';
                    document.getElementById('kabupaten_kota_id').value = data.kabupaten_kota_id;
                    document.getElementById('tahun').value = data.tahun;
                    document.getElementById('nomor_surat').value = data.nomor_surat || '';
                    document.getElementById('verifikator_id').value = data.verifikator_id || '';
                    document.getElementById('assigned_from').value = data.assigned_from || '';
                    document.getElementById('assigned_until').value = data.assigned_until || '';

                    // Populate evaluator checkboxes
                    document.querySelectorAll('input[name="evaluator_ids[]"]').forEach(cb => {
                        cb.checked = data.evaluator_ids && data.evaluator_ids.includes(parseInt(cb.value));
                    });

                    // Show current file if exists
                    if (data.file_sk) {
                        document.getElementById('currentFile').style.display = 'block';
                        document.getElementById('fileName').textContent = data.file_sk.split('/').pop();
                    }
                })
                .catch(error => {
                    alert('Gagal memuat data assignment: ' + error.message);
                    console.error('Edit error:', error);
                });
        }

        // Toggle Status
        function toggleStatus(id, checkbox) {
            const isActive = checkbox.checked ? 1 : 0;

            fetch(`/tim-assignment/${id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        is_active: isActive
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Optional: Show success notification
                    } else {
                        checkbox.checked = !checkbox.checked;
                        alert('Gagal mengubah status');
                    }
                })
                .catch(error => {
                    checkbox.checked = !checkbox.checked;
                    alert('Terjadi kesalahan');
                    console.error(error);
                });
        }

        // Toggle Tim Status (all members of a tim)
        function toggleTimStatus(kabkotaId, tahun, jenisDokumenId, checkbox) {
            const isActive = checkbox.checked ? 1 : 0;

            fetch(`/tim-assignment/toggle-tim-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        kabupaten_kota_id: kabkotaId,
                        tahun: tahun,
                        jenis_dokumen_id: jenisDokumenId || null,
                        is_active: isActive
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Optional: Show success notification
                        console.log(`Tim status updated: ${data.updated} assignments`);
                    } else {
                        checkbox.checked = !checkbox.checked;
                        alert('Gagal mengubah status tim');
                    }
                })
                .catch(error => {
                    checkbox.checked = !checkbox.checked;
                    alert('Terjadi kesalahan');
                    console.error(error);
                });
        }

        // Collapsible chevron rotation
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(element => {
            const target = document.querySelector(element.getAttribute('data-bs-target'));
            target.addEventListener('show.bs.collapse', function() {
                element.querySelector('.chevron-icon').style.transform = 'rotate(180deg)';
            });
            target.addEventListener('hide.bs.collapse', function() {
                element.querySelector('.chevron-icon').style.transform = 'rotate(0deg)';
            });
        });

        // Reset modal on close
        document.getElementById('timModal').addEventListener('hidden.bs.modal', function() {
            // Only reset form fields, not the entire form structure
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('assignmentId').value = '';
            document.getElementById('formTitle').textContent = 'Tambah Tim';
            document.getElementById('btnSubmitText').textContent = 'Simpan';
            document.getElementById('timForm').action = "{{ route('tim-assignment.store') }}";
            document.getElementById('currentFile').style.display = 'none';

            // Reset input values
            document.getElementById('jenis_dokumen_id').value = '';
            document.getElementById('kabupaten_kota_id').value = '';
            document.getElementById('tahun').value = '{{ date('Y') }}';
            document.getElementById('nomor_surat').value = '';
            document.getElementById('verifikator_id').value = '';
            document.getElementById('assigned_from').value = '';
            document.getElementById('assigned_until').value = '';
            document.getElementById('file_sk').value = '';

            // Uncheck all checkboxes
            document.querySelectorAll('input[name="evaluator_ids[]"]').forEach(cb => cb.checked = false);
        });

        // Auto open modal if there are validation errors
        @if ($errors->any())
            const modal = new bootstrap.Modal(document.getElementById('timModal'));
            modal.show();
        @endif
    </script>
@endpush
