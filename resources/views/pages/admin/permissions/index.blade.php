@extends('layouts.app')

@section('title', 'Katalog Hak Akses')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Katalog Hak Akses</h5>
            <small class="text-muted">Daftar hak akses yang tersedia di aplikasi. Pemberian ke role diatur di halaman <a href="{{ route('admin.roles.index') }}">Role & Hak Akses</a>.</small>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Hak Akses</th>
                        <th>Dimiliki Role</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($catalog as $modul => $items)
                        <tr class="table-light">
                            <th colspan="2">{{ $modul }}</th>
                        </tr>
                        @foreach ($items as $name => $item)
                            <tr>
                                <td>{{ $item['label'] }}</td>
                                <td>
                                    @forelse ($rolesByPermission[$name] ?? [] as $roleName)
                                        <span class="badge bg-label-primary">{{ $roleName }}</span>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
