@extends('layouts.app')

@section('title', 'Detail User')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Detail User</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-4">
                            <div class="avatar avatar-xl">
                                <img src="{{ asset('assets/img/avatars/1.png') }}" 
                                     alt="User Avatar" class="w-px-100 h-auto rounded-circle">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="30%"><strong>Nama</strong></td>
                                    <td width="5%">:</td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email</strong></td>
                                    <td>:</td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Role</strong></td>
                                    <td>:</td>
                                    <td>
                                        @if($user->roles->first())
                                            <span class="badge bg-label-primary">
                                                {{ $user->roles->first()->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-label-secondary">Tidak ada role</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Kab / Kota</strong></td>
                                    <td>:</td>
                                    <td>
                                        @if($user->kabupatenKota)
                                            {{ $user->kabupatenKota->getFullNameAttribute() }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Dibuat</strong></td>
                                    <td>:</td>
                                    <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Terakhir Diupdate</strong></td>
                                    <td>:</td>
                                    <td>{{ $user->updated_at->format('d M Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary me-2">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection