<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        // Data notifikasi dummy
        $allNotifikasi = [
            [
                'id' => 1,
                'judul' => 'Permohonan Baru',
                'pesan' => 'Ada permohonan baru dari Kota Ternate untuk RKPD Tahun 2026',
                'jenis' => 'info',
                'icon' => 'bx-file',
                'tanggal' => now()->subHours(2),
                'dibaca' => false
            ],
            [
                'id' => 2,
                'judul' => 'Verifikasi Selesai',
                'pesan' => 'Verifikasi dokumen untuk Kota Tidore Kepulauan telah selesai',
                'jenis' => 'success',
                'icon' => 'bx-check-circle',
                'tanggal' => now()->subHours(5),
                'dibaca' => false
            ],
            [
                'id' => 3,
                'judul' => 'Revisi Diperlukan',
                'pesan' => 'Dokumen Kab. Halmahera Utara memerlukan revisi pada Bab II',
                'jenis' => 'warning',
                'icon' => 'bx-error',
                'tanggal' => now()->subDay(),
                'dibaca' => true
            ],
            [
                'id' => 4,
                'judul' => 'Jadwal Fasilitasi',
                'pesan' => 'Jadwal fasilitasi untuk Kota Ternate telah ditetapkan pada 15 Februari 2026',
                'jenis' => 'info',
                'icon' => 'bx-calendar',
                'tanggal' => now()->subDays(2),
                'dibaca' => true
            ],
            [
                'id' => 5,
                'judul' => 'Dokumen Disetujui',
                'pesan' => 'Dokumen RKPD Kab. Halmahera Selatan telah disetujui',
                'jenis' => 'success',
                'icon' => 'bx-check-double',
                'tanggal' => now()->subDays(3),
                'dibaca' => true
            ],
            [
                'id' => 6,
                'judul' => 'Perpanjangan Waktu',
                'pesan' => 'Permintaan perpanjangan waktu dari Kab. Pulau Morotai telah disetujui',
                'jenis' => 'info',
                'icon' => 'bx-time',
                'tanggal' => now()->subDays(4),
                'dibaca' => true
            ],
            [
                'id' => 7,
                'judul' => 'Hasil Fasilitasi',
                'pesan' => 'Hasil fasilitasi untuk Kota Tidore telah diupload',
                'jenis' => 'success',
                'icon' => 'bx-upload',
                'tanggal' => now()->subDays(5),
                'dibaca' => true
            ],
            [
                'id' => 8,
                'judul' => 'Pengumuman',
                'pesan' => 'Sistem akan maintenance pada tanggal 20 Februari 2026',
                'jenis' => 'warning',
                'icon' => 'bx-info-circle',
                'tanggal' => now()->subDays(7),
                'dibaca' => true
            ],
        ];

        // Simpan ke session jika belum ada
        if (!session()->has('notifikasi')) {
            session(['notifikasi' => $allNotifikasi]);
        }

        // Ambil notifikasi dari session
        $notifikasi = session('notifikasi', []);

        // Filter berdasarkan status
        $status = $request->get('status');
        if ($status === 'belum_dibaca') {
            $notifikasi = array_filter($notifikasi, fn($item) => !$item['dibaca']);
        } elseif ($status === 'sudah_dibaca') {
            $notifikasi = array_filter($notifikasi, fn($item) => $item['dibaca']);
        }

        // Filter berdasarkan jenis
        $jenis = $request->get('jenis');
        if ($jenis && $jenis !== 'semua') {
            $notifikasi = array_filter($notifikasi, fn($item) => $item['jenis'] === $jenis);
        }

        // Hitung statistik
        $allNotif = session('notifikasi', []);
        $stats = [
            'total' => count($allNotif),
            'belum_dibaca' => count(array_filter($allNotif, fn($item) => !$item['dibaca'])),
            'sudah_dibaca' => count(array_filter($allNotif, fn($item) => $item['dibaca'])),
        ];

        return view('notifikasi.index', compact('notifikasi', 'stats'));
    }

    public function destroy($id)
    {
        $notifikasi = session('notifikasi', []);
        
        // Hapus notifikasi berdasarkan ID
        $notifikasi = array_filter($notifikasi, fn($item) => $item['id'] != $id);
        
        // Re-index array
        $notifikasi = array_values($notifikasi);
        
        session(['notifikasi' => $notifikasi]);

        return redirect()->route('notifikasi.index')
            ->with('success', 'Notifikasi berhasil dihapus');
    }

    public function destroyAll()
    {
        session(['notifikasi' => []]);

        return redirect()->route('notifikasi.index')
            ->with('success', 'Semua notifikasi berhasil dihapus');
    }

    public function markAllAsRead()
    {
        $notifikasi = session('notifikasi', []);
        
        // Tandai semua sebagai sudah dibaca
        $notifikasi = array_map(function($item) {
            $item['dibaca'] = true;
            return $item;
        }, $notifikasi);
        
        session(['notifikasi' => $notifikasi]);

        return redirect()->route('notifikasi.index')
            ->with('success', 'Semua notifikasi ditandai sudah dibaca');
    }
}
