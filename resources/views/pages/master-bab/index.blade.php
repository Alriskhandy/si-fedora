@extends('layouts.app')

@section('title', 'Master Bab')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Master Data /</span> Master Bab
        </h4>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Error Message -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Form Section -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 80px;">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <span id="form-title">Tambah Bab</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="babForm" action="{{ route('master-bab.store') }}" method="POST">
                            @csrf
                            <input type="hidden" id="form-method" name="_method" value="">
                            <input type="hidden" id="bab-id" name="bab_id" value="">

                            <div class="mb-3">
                                <label class="form-label" for="jenis_dokumen_id">Jenis Dokumen <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('jenis_dokumen_id') is-invalid @enderror"
                                    id="jenis_dokumen_id" name="jenis_dokumen_id" required>
                                    <option value="">Pilih Jenis Dokumen</option>
                                    @foreach ($jenisDokumenList as $jd)
                                        <option value="{{ $jd->id }}"
                                            {{ old('jenis_dokumen_id') == $jd->id ? 'selected' : '' }}>
                                            {{ $jd->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jenis_dokumen_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="nama_bab">Nama Bab <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_bab') is-invalid @enderror"
                                    id="nama_bab" name="nama_bab" value="{{ old('nama_bab') }}" required>
                                @error('nama_bab')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="urutan">Urutan</label>
                                <input type="number" class="form-control @error('urutan') is-invalid @enderror"
                                    id="urutan" name="urutan" value="{{ old('urutan') }}" min="0">
                                @error('urutan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="bx bx-save me-1"></i>
                                    <span id="submit-text">Simpan</span>
                                </button>
                                <button type="button" class="btn btn-secondary" id="resetBtn" style="display: none;"
                                    onclick="resetForm()">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- List Section -->
            <div class="col-lg-8">
                <!-- Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('master-bab.index') }}">
                            <select class="form-select" name="jenis_dokumen_id" onchange="this.form.submit()">
                                <option value="">Semua Jenis Dokumen</option>
                                @foreach ($jenisDokumenList as $jd)
                                    <option value="{{ $jd->id }}"
                                        {{ request('jenis_dokumen_id') == $jd->id ? 'selected' : '' }}>
                                        {{ $jd->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Bab Cards Grouped by Jenis Dokumen -->
                @php
                    $groupedBabs = $babs->groupBy('jenis_dokumen_id');
                @endphp

                @forelse ($groupedBabs as $jenisDokumenId => $babsByJenis)
                    @php
                        $jenisDokumen = $babsByJenis->first()->jenisDokumen;
                    @endphp

                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between" style="cursor: pointer;"
                            data-bs-toggle="collapse" data-bs-target="#collapse-{{ $jenisDokumenId }}"
                            aria-expanded="true">
                            <h5 class="mb-0">
                                <i class="bx bx-file text-primary me-2"></i>
                                {{ $jenisDokumen ? $jenisDokumen->nama : 'Tanpa Jenis Dokumen' }}
                            </h5>
                            <div>
                                <span class="badge bg-label-primary me-2">{{ $babsByJenis->count() }} Bab</span>
                                <i class="bx bx-chevron-down"></i>
                            </div>
                        </div>
                        <div id="collapse-{{ $jenisDokumenId }}" class="collapse show">
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    @foreach ($babsByJenis->sortBy('urutan') as $bab)
                                        <div
                                            class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                            <div class="small">
                                                @if ($bab->urutan)
                                                    <span class="badge bg-label-secondary me-2"
                                                        style="font-size: 0.7rem;">{{ $bab->urutan }}</span>
                                                @endif
                                                <strong>{{ $bab->nama_bab }}</strong>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                    onclick="editBab({{ $bab->id }}, '{{ $bab->nama_bab }}', {{ $bab->jenis_dokumen_id }}, {{ $bab->urutan ?? 'null' }})"
                                                    title="Edit">
                                                    <i class="bx bx-edit-alt" style="font-size: 0.875rem;"></i>
                                                </button>
                                                <form action="{{ route('master-bab.destroy', $bab) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin menghapus bab ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        title="Hapus">
                                                        <i class="bx bx-trash" style="font-size: 0.875rem;"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bx bx-folder-open text-muted" style="font-size: 64px;"></i>
                            <h5 class="text-muted mt-3">Tidak ada data bab</h5>
                            <p class="text-muted">Silakan tambahkan bab baru</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function editBab(id, nama, jenisId, urutan) {
                // Update form
                document.getElementById('form-title').textContent = 'Edit Bab';
                document.getElementById('submit-text').textContent = 'Update';
                document.getElementById('babForm').action = `/master-bab/${id}`;
                document.getElementById('form-method').value = 'PUT';
                document.getElementById('bab-id').value = id;

                // Fill form
                document.getElementById('nama_bab').value = nama;
                document.getElementById('jenis_dokumen_id').value = jenisId;
                document.getElementById('urutan').value = urutan || '';

                // Show reset button
                document.getElementById('resetBtn').style.display = 'block';

                // Scroll to form
                document.querySelector('.sticky-top').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            function resetForm() {
                // Reset form
                document.getElementById('form-title').textContent = 'Tambah Bab';
                document.getElementById('submit-text').textContent = 'Simpan';
                document.getElementById('babForm').action = '{{ route('master-bab.store') }}';
                document.getElementById('form-method').value = '';
                document.getElementById('bab-id').value = '';
                document.getElementById('babForm').reset();

                // Hide reset button
                document.getElementById('resetBtn').style.display = 'none';
            }

            // Reset form on page load if no errors
            @if (!$errors->any())
                document.addEventListener('DOMContentLoaded', function() {
                    resetForm();
                });
            @endif

            // Rotate chevron icon on collapse toggle
            document.addEventListener('DOMContentLoaded', function() {
                const collapseElements = document.querySelectorAll('[data-bs-toggle="collapse"]');
                collapseElements.forEach(element => {
                    const targetId = element.getAttribute('data-bs-target');
                    const target = document.querySelector(targetId);
                    const chevron = element.querySelector('.bx-chevron-down');

                    target.addEventListener('shown.bs.collapse', () => {
                        chevron.style.transform = 'rotate(180deg)';
                        chevron.style.transition = 'transform 0.2s';
                    });

                    target.addEventListener('hidden.bs.collapse', () => {
                        chevron.style.transform = 'rotate(0deg)';
                        chevron.style.transition = 'transform 0.2s';
                    });
                });
            });
        </script>
    @endpush
@endsection
