{{-- Pengirim dan waktu masukan pada daftar input hasil fasilitasi. Variabel: $item, $userId, $formatWaktu. --}}
<small class="text-muted">
    @include('pages.fasilitasi.hasil-fasilitasi.partials.pengirim', ['user' => $item->user])
    @if ($item->user_id == $userId)
        <span class="badge bg-label-success">Anda</span>
    @endif
    <br><span class="text-secondary">{{ $formatWaktu($item->created_at) }}</span>
</small>
