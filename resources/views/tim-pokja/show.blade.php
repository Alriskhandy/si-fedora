@extends('layouts.app')

@section('title', 'Detail Tim Pokja')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Detail Tim Pokja</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="30%"><strong>Nama</strong></td>
                            <td width="5%">:</td>
                            <td>{{ $timPokja->nama }}</td>
                        </tr>
                        <tr>
                            <td><strong>Ketua</strong></td>
                            <td>:</td>
                            <td>{{ $timPokja->ketua->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Deskripsi</strong></td>
                            <td>:</td>
                            <td>{{ $timPokja->deskripsi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <td>:</td>
                            <td>
                                @if($timPokja->is_active)
                                    <span class="badge bg-label-success">Aktif</span>
                                @else
                                    <span class="badge bg-label-secondary">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Dibuat</strong></td>
                            <td>:</td>
                            <td>{{ $timPokja->created_at ? $timPokja->created_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Diupdate</strong></td>
                            <td>:</td>
                            <td>{{ $timPokja->updated_at ? $timPokja->updated_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                    </table>

                    <div class="mt-4">
                        <a href="{{ route('tim-pokja.edit', $timPokja) }}" class="btn btn-primary me-2">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>
                        <a href="{{ route('tim-pokja.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection