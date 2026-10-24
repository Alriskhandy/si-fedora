<?php

namespace App\Http\Controllers\Umum;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Notifikasi in-app milik pengguna yang sedang login.
 *
 * Akses: pengguna login (hanya notifikasinya sendiri).
 * View: resources/views/pages/notifikasi
 */
class NotifikasiController extends Controller
{
    /**
     * Daftar notifikasi pengguna dengan filter sudah/belum dibaca dan ringkasan jumlahnya.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Query notifikasi untuk user yang login
        $query = Notifikasi::where('user_id', $user->id)
            ->with('model')
            ->latest();

        // Filter berdasarkan status baca
        $status = $request->get('status');
        if ($status === 'belum_dibaca') {
            $query->where('is_read', false);
        } elseif ($status === 'sudah_dibaca') {
            $query->where('is_read', true);
        }

        // Filter berdasarkan jenis/type
        $jenis = $request->get('jenis');
        if ($jenis && $jenis !== 'semua') {
            $query->where('type', $jenis);
        }

        // Pagination
        $notifikasi = $query->paginate(20);

        // Hitung statistik
        $stats = [
            'total' => Notifikasi::where('user_id', $user->id)->count(),
            'belum_dibaca' => Notifikasi::where('user_id', $user->id)->where('is_read', false)->count(),
            'sudah_dibaca' => Notifikasi::where('user_id', $user->id)->where('is_read', true)->count(),
        ];

        return view('pages.notifikasi.index', compact('notifikasi', 'stats'));
    }

    /**
     * Hapus satu notifikasi milik pengguna.
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $notifikasi = Notifikasi::where('user_id', $user->id)->findOrFail($id);
        $notifikasi->delete();

        return redirect()->route('notifikasi.index')
            ->with('success', 'Notifikasi berhasil dihapus');
    }

    /**
     * Hapus semua notifikasi milik pengguna.
     */
    public function destroyAll()
    {
        $user = Auth::user();

        Notifikasi::where('user_id', $user->id)->delete();

        return redirect()->route('notifikasi.index')
            ->with('success', 'Semua notifikasi berhasil dihapus');
    }

    /**
     * Tandai semua notifikasi pengguna sebagai sudah dibaca.
     */
    public function markAllAsRead()
    {
        $user = Auth::user();

        Notifikasi::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return redirect()->route('notifikasi.index')
            ->with('success', 'Semua notifikasi ditandai sudah dibaca');
    }
}
