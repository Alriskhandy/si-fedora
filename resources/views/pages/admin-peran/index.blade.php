@extends('layouts.app')

@section('title', 'Admin PERAN Dashboard')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
   <div class="row">
      <div class="col-lg-12 mb-4">
         <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
               <h5 class="card-title mb-0">Admin PERAN Dashboard</h5>
            </div>
            <div class="card-body">
               <!-- Filter Status -->
               <div class="row mb-3">
                  <div class="col-md-3">
                     <select name="status" class="form-select" onchange="window.location.href='?status='+this.value">
                        <option value="">Semua Status</option>
                        @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" {{ $status==$key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-6">
                     <form method="GET" action="{{ route('admin-peran.index') }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <div class="input-group">
                           <input type="text" class="form-control" name="search" placeholder="Cari permohonan..."
                              value="{{ request('search') }}">
                           <button class="btn btn-outline-secondary" type="submit">
                              <i class="bx bx-search"></i>
                           </button>
                        </div>
                     </form>
                  </div>
               </div>

               <!-- Table -->
               <div class="table-responsive">
                  <table class="table table-striped">
                     <thead>
                        <tr>
                           <th>No</th>
                           <th>Kabupaten/Kota</th>
                           <th>Jenis Dokumen</th>
                           <th>Verifikator</th>
                           <th>Tim Pokja</th>
                           <th>Status</th>
                           <th>Aksi</th>
                        </tr>
                     </thead>
                     <tbody>
                        @forelse($permohonan as $index => $item)
                        <tr>
                           <td>{{ $index + $permohonan->firstItem() }}</td>
                           <td>{{ $item->kabupatenKota->getFullNameAttribute() ?? '-' }}</td>
                           <td>{{ $item->jenisDokumen->nama ?? '-' }}</td>
                           <td>
                              @if($item->verifikator_id)
                              {{ $item->verifikator->name ?? '-' }}
                              <form action="{{ route('admin-peran.unassign', $item) }}" method="POST" class="d-inline">
                                 @csrf
                                 <input type="hidden" name="assign_type" value="verifikator">
                                 <button type="submit" class="btn btn-sm btn-outline-danger ms-2"
                                    onclick="return confirm('Yakin ingin menghapus assignment?')">
                                    <i class="bx bx-x"></i>
                                 </button>
                              </form>
                              @else
                              <form action="{{ route('admin-peran.assign', $item) }}" method="POST">
                                 @csrf
                                 <input type="hidden" name="assign_type" value="verifikator">
                                 <select name="user_id" class="form-select form-select-sm d-inline" style="width: auto;"
                                    onchange="this.form.submit()">
                                    <option value="">Pilih Verifikator</option>
                                    @foreach($verifikatorList as $verifikator)
                                    <option value="{{ $verifikator->id }}">{{ $verifikator->name }}</option>
                                    @endforeach
                                 </select>
                              </form>
                              @endif
                           </td>
                           {{-- <td>
                              @if($item->pokja_id)
                              {{ $item->pokja->name ?? '-' }}
                              <form action="{{ route('admin-peran.unassign', $item) }}" method="POST" class="d-inline">
                                 @csrf
                                 <input type="hidden" name="assign_type" value="pokja">
                                 <button type="submit" class="btn btn-sm btn-outline-danger ms-2"
                                    onclick="return confirm('Yakin ingin menghapus assignment?')">
                                    <i class="bx bx-x"></i>
                                 </button>
                              </form>
                              @else
                              <form action="{{ route('admin-peran.assign', $item) }}" method="POST">
                                 @csrf
                                 <input type="hidden" name="assign_type" value="pokja">
                                 <select name="user_id" class="form-select form-select-sm d-inline" style="width: auto;"
                                    onchange="this.form.submit()">
                                    <option value="">Pilih Tim Pokja</option>
                                    @foreach($pokjaList as $pokja)
                                    <option value="{{ $pokja->id }}">{{ $pokja->name }}</option>
                                    @endforeach
                                 </select>
                              </form>
                              @endif
                           </td> --}}
                           <!-- Di kolom Tim Pokja -->
                           <td>
                              @if($item->pokja_id)
                              {{ $item->timPokja->nama ?? '-' }}
                              <!-- Tampilkan nama Tim Pokja -->
                              <form action="{{ route('admin-peran.unassign', $item) }}" method="POST" class="d-inline">
                                 @csrf
                                 <input type="hidden" name="assign_type" value="pokja">
                                 <button type="submit" class="btn btn-sm btn-outline-danger ms-2">
                                    <i class="bx bx-x"></i>
                                 </button>
                              </form>
                              @else
                              <form action="{{ route('admin-peran.assign', $item) }}" method="POST">
                                 @csrf
                                 <input type="hidden" name="assign_type" value="pokja">
                                 <select name="user_id" class="form-select form-select-sm d-inline" style="width: auto;"
                                    onchange="this.form.submit()">
                                    <option value="">Pilih Anggota Pokja</option>
                                    @foreach($pokjaList as $pokja)
                                    <option value="{{ $pokja->id }}">{{ $pokja->name }} ({{ $pokja->timPokja?->nama ??
                                       'Tanpa Tim' }})</option>
                                    @endforeach
                                 </select>
                              </form>
                              @endif
                           </td>
                           <td>
                              <span class="badge bg-label-{{ $item->status_badge_class }}">{{ $item->status_label
                                 }}</span>
                           </td>
                           <td>
                              <a href="{{ route('permohonan.show', $item) }}" class="btn btn-sm btn-primary">
                                 <i class="bx bx-show me-1"></i> Detail
                              </a>
                           </td>
                        </tr>
                        @empty
                        <tr>
                           <td colspan="7" class="text-center text-muted">Tidak ada permohonan</td>
                        </tr>
                        @endforelse
                     </tbody>
                  </table>
               </div>

               <div class="d-flex justify-content-center">
                  {{ $permohonan->appends(request()->query())->links() }}
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection