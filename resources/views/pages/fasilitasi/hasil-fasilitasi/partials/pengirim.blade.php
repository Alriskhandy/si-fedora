{{-- Nama pengirim masukan dan asal OPD-nya bila akun OPD (keputusan K8). Variabel: $user (boleh null). --}}
{{ $user->name ?? '-' }}
@if ($user?->opd)
    <span class="badge bg-label-info d-inline-block text-wrap text-start">{{ $user->opd->singkatan }}</span>
@endif
