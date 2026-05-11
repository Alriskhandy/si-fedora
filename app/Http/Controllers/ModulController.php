<?php

namespace App\Http\Controllers;

use App\Models\Modul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ModulController extends Controller
{
    private const ROLES = [
        'all'         => 'Semua Role',
        'pemohon'     => 'Pemohon (Kab/Kota)',
        'verifikator' => 'Verifikator',
        'fasilitator' => 'Fasilitator',
        'admin_peran' => 'Admin Peran',
        'kaban'       => 'Kepala Badan',
        'auditor'     => 'Auditor',
    ];

    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('superadmin')) {
            // Superadmin lihat semua modul
            $modul = Modul::with('uploader')->latest()->get();
        } else {
            // Role lain hanya lihat modul yang sesuai rolenya
            $roleName = $user->getRoleNames()->first() ?? '';
            $modul = Modul::with('uploader')->forRole($roleName)->latest()->get();
        }

        $roles = self::ROLES;
        $isSuperadmin = $user->hasRole('superadmin');

        return view('pages.modul.index', compact('modul', 'roles', 'isSuperadmin'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'   => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'role'    => 'required|string|in:' . implode(',', array_keys(self::ROLES)),
            'file'    => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:51200',
        ], [
            'file.required' => 'File modul wajib diupload.',
            'file.mimes'    => 'Format file: PDF, Word, Excel, atau PowerPoint.',
            'file.max'      => 'Ukuran file maksimal 50MB.',
        ]);

        $file     = $request->file('file');
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs('modul', $filename, 'public');

        Modul::create([
            'judul'       => $request->judul,
            'deskripsi'   => $request->deskripsi,
            'file_path'   => $path,
            'file_name'   => $file->getClientOriginalName(),
            'file_size'   => $file->getSize(),
            'file_type'   => $file->getMimeType(),
            'role'        => $request->role,
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->route('modul.index')->with('success', 'Modul berhasil diupload.');
    }

    public function download(Modul $modul)
    {
        $user     = Auth::user();
        $roleName = $user->getRoleNames()->first() ?? '';

        // Cek akses: superadmin bisa download semua, role lain sesuai modul
        if (!$user->hasRole('superadmin') && $modul->role !== 'all' && $modul->role !== $roleName) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh modul ini.');
        }

        $fullPath = storage_path('app/public/' . $modul->file_path);

        if (!file_exists($fullPath)) {
            Log::warning('Modul file not found', ['modul_id' => $modul->id, 'path' => $fullPath]);
            return redirect()->route('modul.index')->with('error', 'File tidak ditemukan.');
        }

        activity()
            ->performedOn($modul)
            ->causedBy($user)
            ->log('Mengunduh modul: ' . $modul->judul);

        return response()->download($fullPath, $modul->file_name);
    }

    public function destroy(Modul $modul)
    {
        Storage::disk('public')->delete($modul->file_path);
        $modul->delete();

        return redirect()->route('modul.index')->with('success', 'Modul berhasil dihapus.');
    }
}
