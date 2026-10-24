<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Laporan Verifikasi {{ $permohonan->kabupatenKota->nama ?? '' }} Tahun {{ $permohonan->tahun }}</title>
    <style>
        @page {
            margin: 21mm 25mm 23mm 19mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
        }
        .judul {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 16px;
        }
        .section-title {
            font-weight: bold;
            margin-top: 14px;
            margin-bottom: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table.info td {
            padding: 2px 4px;
            vertical-align: top;
        }
        table.dokumen th,
        table.dokumen td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }
        table.dokumen th {
            background: #eee;
        }
    </style>
</head>
<body>
    <div class="judul">
        Laporan Hasil Verifikasi Kelengkapan Dokumen<br>
        {{ $permohonan->jenisDokumen->nama ?? 'Dokumen' }} {{ $permohonan->kabupatenKota->nama ?? '' }}
        Tahun {{ $permohonan->tahun }}
    </div>

    <table class="info">
        <tr>
            <td width="35%">Tanggal Laporan</td>
            <td>: {{ $laporan->tanggal_laporan?->translatedFormat('d F Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td>Dibuat Oleh</td>
            <td>: {{ $laporan->pembuatLaporan->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Status Kelengkapan</td>
            <td>: {{ $laporan->status_kelengkapan === 'lengkap' ? 'Lengkap' : 'Tidak Lengkap' }}</td>
        </tr>
        <tr>
            <td>Jumlah Dokumen</td>
            <td>: {{ $laporan->total_dokumen }} (terverifikasi {{ $laporan->jumlah_dokumen_verified }}, perlu revisi {{ $laporan->jumlah_dokumen_revision }})</td>
        </tr>
    </table>

    <div class="section-title">Ringkasan Hasil Verifikasi</div>
    <div>{!! nl2br(e($laporan->ringkasan_verifikasi)) !!}</div>

    @if ($laporan->catatan_admin)
        <div class="section-title">Catatan Tambahan</div>
        <div>{!! nl2br(e($laporan->catatan_admin)) !!}</div>
    @endif

    <div class="section-title">Daftar Dokumen</div>
    <table class="dokumen">
        <thead>
            <tr>
                <th width="6%">No</th>
                <th>Nama Dokumen</th>
                <th width="18%">Status</th>
                <th width="34%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($permohonan->permohonanDokumen as $dokumen)
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td>{{ $dokumen->masterKelengkapan->nama_dokumen ?? $dokumen->file_name ?? '-' }}</td>
                    <td>{{ ucfirst($dokumen->status_verifikasi ?? '-') }}</td>
                    <td>{{ $dokumen->catatan_verifikasi ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Tidak ada dokumen</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
