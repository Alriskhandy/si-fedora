@extends('layouts.app')

@section('title', 'Log Aktivitas Pengguna')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Log Aktivitas Pengguna</h4>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Filter Aktivitas</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('activity-log.index') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="user_id" class="form-label">Pengguna</label>
                            <select name="user_id" id="user_id" class="form-select">
                                <option value="">Semua Pengguna</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="subject_type" class="form-label">Model</label>
                            <select name="subject_type" id="subject_type" class="form-select">
                                <option value="">Semua Model</option>
                                @foreach ($subjectTypes as $type)
                                    <option value="{{ $type['value'] }}"
                                        {{ request('subject_type') == $type['value'] ? 'selected' : '' }}>
                                        {{ $type['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="date_from" class="form-label">Dari Tanggal</label>
                            <input type="date" name="date_from" id="date_from" class="form-control"
                                value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-2">
                            <label for="date_to" class="form-label">Sampai Tanggal</label>
                            <input type="date" name="date_to" id="date_to" class="form-control"
                                value="{{ request('date_to') }}">
                        </div>

                        <div class="col-md-2">
                            <label for="description" class="form-label">Aktivitas</label>
                            <input type="text" name="description" id="description" class="form-control"
                                placeholder="Cari aktivitas..." value="{{ request('description') }}">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class='bx bx-search-alt me-1'></i> Filter
                            </button>
                            <a href="{{ route('activity-log.index') }}" class="btn btn-outline-secondary">
                                <i class='bx bx-reset me-1'></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Activity Log Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Daftar Aktivitas</h5>
                <span class="badge bg-label-primary">Total: {{ $activities->total() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pengguna</th>
                                <th>Aktivitas</th>
                                <th>Modul</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($activities as $activity)
                                <tr>
                                    <td>
                                        <small class="text-muted">
                                            {{ $activity->created_at->format('d/m/Y H:i:s') }}
                                            <br>
                                            <span class="text-muted">{{ $activity->created_at->diffForHumans() }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        @if ($activity->causer)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                                        {{ substr($activity->causer->name, 0, 2) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <strong>{{ $activity->causer->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $activity->causer->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match ($activity->description) {
                                                'created', 'published' => 'bg-label-success',
                                                'updated' => 'bg-label-info',
                                                'deleted', 'cancelled' => 'bg-label-danger',
                                                'downloaded' => 'bg-label-warning',
                                                default => 'bg-label-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ ucfirst($activity->description) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-primary">
                                            {{ $activity->subject_type ? class_basename($activity->subject_type) : '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#detailModal{{ $activity->id }}">
                                            <i class='bx bx-show me-1'></i> Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class='bx bx-info-circle bx-lg text-muted'></i>
                                        <p class="text-muted mt-2">Tidak ada aktivitas yang ditemukan</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($activities->hasPages())
                    <div class="mt-4">
                        {{ $activities->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Modals -->
        @foreach ($activities as $activity)
            <div class="modal fade" id="detailModal{{ $activity->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Aktivitas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <!-- Activity Info -->
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title mb-3">Informasi Aktivitas</h6>
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr>
                                                    <th width="40%">Aktivitas</th>
                                                    <td>
                                                        @php
                                                            $badgeClass = match ($activity->description) {
                                                                'created', 'published' => 'bg-label-success',
                                                                'updated' => 'bg-label-info',
                                                                'deleted', 'cancelled' => 'bg-label-danger',
                                                                'downloaded' => 'bg-label-warning',
                                                                default => 'bg-label-secondary',
                                                            };
                                                        @endphp
                                                        <span class="badge {{ $badgeClass }}">
                                                            {{ ucfirst($activity->description) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Modul</th>
                                                    <td>
                                                        <span class="badge bg-label-primary">
                                                            {{ $activity->subject_type ? class_basename($activity->subject_type) : '-' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Waktu</th>
                                                    <td>
                                                        {{ $activity->created_at->format('d/m/Y H:i:s') }}
                                                        <br>
                                                        <small
                                                            class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- User Info -->
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title mb-3">Informasi Pengguna</h6>
                                            @if ($activity->causer)
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="avatar avatar-md me-3">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            {{ substr($activity->causer->name, 0, 2) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $activity->causer->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $activity->causer->email }}</small>
                                                    </div>
                                                </div>
                                                @if (isset($activity->causer->roles))
                                                    <div>
                                                        <strong>Role:</strong>
                                                        @foreach ($activity->causer->roles as $role)
                                                            <span class="badge bg-label-info">{{ $role->name }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @else
                                                <div class="text-center py-3">
                                                    <i class='bx bx-bot bx-lg text-muted'></i>
                                                    <p class="text-muted mt-2 mb-0">System</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Changes -->
                                @if ($activity->properties && $activity->properties->count() > 0)
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title mb-3">Detail Perubahan Data</h6>
                                                @if ($activity->description === 'updated' && isset($activity->properties['old']) && isset($activity->properties['attributes']))
                                                    <!-- Update: Side by side comparison -->
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th width="25%" class="bg-light">Field</th>
                                                                    <th width="37.5%" class="bg-danger-subtle text-danger">
                                                                        <i class='bx bx-x-circle'></i> Data Lama
                                                                    </th>
                                                                    <th width="37.5%" class="bg-success-subtle text-success">
                                                                        <i class='bx bx-check-circle'></i> Data Baru
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @php
                                                                    $allKeys = array_unique(array_merge(
                                                                        array_keys($activity->properties['old']),
                                                                        array_keys($activity->properties['attributes'])
                                                                    ));
                                                                @endphp
                                                                @foreach ($allKeys as $key)
                                                                    @php
                                                                        $oldValue = $activity->properties['old'][$key] ?? null;
                                                                        $newValue = $activity->properties['attributes'][$key] ?? null;
                                                                        $hasChanged = $oldValue !== $newValue;
                                                                    @endphp
                                                                    <tr class="{{ $hasChanged ? 'table-warning' : '' }}">
                                                                        <td class="fw-bold">
                                                                            {{ ucwords(str_replace('_', ' ', $key)) }}
                                                                            @if($hasChanged)
                                                                                <i class='bx bx-edit-alt text-warning ms-1'></i>
                                                                            @endif
                                                                        </td>
                                                                        <td class="{{ $hasChanged ? 'text-danger' : '' }}">
                                                                            @if(is_array($oldValue))
                                                                                <code>{{ json_encode($oldValue, JSON_UNESCAPED_UNICODE) }}</code>
                                                                            @elseif(is_null($oldValue))
                                                                                <span class="text-muted fst-italic">null</span>
                                                                            @elseif($oldValue === '')
                                                                                <span class="text-muted fst-italic">(kosong)</span>
                                                                            @else
                                                                                {{ $oldValue }}
                                                                            @endif
                                                                        </td>
                                                                        <td class="{{ $hasChanged ? 'text-success' : '' }}">
                                                                            @if(is_array($newValue))
                                                                                <code>{{ json_encode($newValue, JSON_UNESCAPED_UNICODE) }}</code>
                                                                            @elseif(is_null($newValue))
                                                                                <span class="text-muted fst-italic">null</span>
                                                                            @elseif($newValue === '')
                                                                                <span class="text-muted fst-italic">(kosong)</span>
                                                                            @else
                                                                                {{ $newValue }}
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <!-- Other actions: Simple list -->
                                                    <div class="bg-light p-3 rounded">
                                                        @foreach ($activity->properties as $key => $value)
                                                            @if(!in_array($key, ['old', 'attributes']))
                                                                <div class="mb-2">
                                                                    <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong>
                                                                    <div>
                                                                        @if(is_array($value))
                                                                            <code>{{ json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</code>
                                                                        @else
                                                                            {{ $value ?? '-' }}
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
