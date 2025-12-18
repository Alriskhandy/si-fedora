<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Http\Request;
use App\Models\JadwalFasilitasi;
use App\Models\PermohonanDokumen;
use App\Models\MasterKelengkapanVerifikasi;
use Illuminate\Support\Facades\Auth;

class PermohonanController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'jadwalFasilitasi']);

        // Filter berdasarkan role
        if (Auth::user()->hasRole('pemohon')) {
            $query->where('user_id', Auth::id());
        } elseif (Auth::user()->hasRole('admin_peran')) {
            // Admin bisa liat semua permohonan
        } elseif (Auth::user()->hasRole('verifikator')) {
            // Verifikator sekarang lewat assignment table
            $permohonanIds = \App\Models\TimVerifikasiAssignment::where('user_id', Auth::id())
                ->pluck('permohonan_id');
            $query->whereIn('id', $permohonanIds);
        } elseif (Auth::user()->hasRole('fasilitator')) {
            // Fasilitator sekarang lewat assignment table
            $permohonanIds = \App\Models\TimFasilitasiAssignment::where('user_id', Auth::id())
                ->pluck('permohonan_id');
            $query->whereIn('id', $permohonanIds);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('tahun', 'like', '%' . $request->search . '%')
                    ->orWhere('jenis_dokumen', 'like', '%' . $request->search . '%')
                    ->orWhereHas('kabupatenKota', function ($qq) use ($request) {
                        $qq->where('nama', 'like', '%' . $request->search . '%');
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status_akhir', $request->status);
        }

        // Filter by tahun
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        $permohonan = $query->latest()->paginate(10);

        $filterOptions = [
            'tahunList' => Permohonan::distinct('tahun')->orderBy('tahun', 'desc')->pluck('tahun'),
            'statusOptions' => [
                'belum' => 'Belum Dimulai',
                'proses' => 'Dalam Proses',
                'revisi' => 'Perlu Revisi',
                'selesai' => 'Selesai',
            ]
        ];

        return view('permohonan.index', compact('permohonan', 'filterOptions'));
    }

    public function create(Request $request)
    {
        // Hanya jadwal yang published yang bisa dipilih
        $jadwalFasilitasi = JadwalFasilitasi::where('status', 'published')
            ->where('batas_permohonan', '>=', now())->with(['jenisDokumen'])
            ->get();

        // Pre-select jadwal if jadwal_id provided
        $selectedJadwal = null;
        if ($request->filled('jadwal_id')) {
            $selectedJadwal = JadwalFasilitasi::find($request->jadwal_id);
        }

        return view('permohonan.create', compact('jadwalFasilitasi', 'selectedJadwal'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jadwal_fasilitasi_id' => 'required|exists:jadwal_fasilitasi,id',
        ]);

        // Cek apakah jadwal masih aktif
        $jadwal = JadwalFasilitasi::find($request->jadwal_fasilitasi_id);
        if ($jadwal->batas_permohonan < now()) {
            return redirect()->back()->withErrors(['jadwal_fasilitasi_id' => 'Jadwal permohonan sudah ditutup.']);
        }

        // Cek apakah user sudah pernah membuat permohonan untuk jadwal ini
        $existingPermohonan = Permohonan::where('jadwal_fasilitasi_id', $request->jadwal_fasilitasi_id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingPermohonan) {
            return redirect()->route('permohonan.show', $existingPermohonan)
                ->with('info', 'Anda sudah memiliki permohonan untuk jadwal ini.');
        }

        // Buat permohonan dengan data dari jadwal
        $permohonan = Permohonan::create([
            'user_id' => Auth::id(),
            'kab_kota_id' => Auth::user()->kabupaten_kota_id,
            'jadwal_fasilitasi_id' => $request->jadwal_fasilitasi_id,
            'tahun' => $jadwal->tahun_anggaran,
            'jenis_dokumen_id' => $jadwal->jenis_dokumen,
            'status_akhir' => 'belum',
        ]);

        // Auto-generate dokumen persyaratan berdasarkan master_kelengkapan_verifikasi
        $kelengkapanList = MasterKelengkapanVerifikasi::orderBy('urutan')->get();
        foreach ($kelengkapanList as $kelengkapan) {
            PermohonanDokumen::create([
                'permohonan_id' => $permohonan->id,
                'master_kelengkapan_id' => $kelengkapan->id,
                'is_ada' => false,
                'status_verifikasi' => 'pending',
            ]);
        }

        return redirect()->route('permohonan.show', $permohonan)->with('success', 'Permohonan berhasil dibuat. Silakan lengkapi dokumen persyaratan.');
    }
    public function show(Permohonan $permohonan)
    {
        // Cek hak akses
        $this->authorizeView($permohonan);

        return view('permohonan.show', compact('permohonan'));
    }

    public function edit(Permohonan $permohonan)
    {
        // Hanya bisa edit kalo status belum
        if ($permohonan->status_akhir !== 'belum') {
            return redirect()->route('permohonan.show', $permohonan)->with('error', 'Permohonan sudah dalam proses dan tidak bisa diedit.');
        }

        // Cek hak akses
        $this->authorizeView($permohonan);

        return view('permohonan.edit', compact('permohonan'));
    }

    public function update(Request $request, Permohonan $permohonan)
    {
        // Hanya bisa edit kalo status belum
        if ($permohonan->status_akhir !== 'belum') {
            return redirect()->route('permohonan.show', $permohonan)->with('error', 'Permohonan sudah dalam proses dan tidak bisa diedit.');
        }

        // Update logic here (for now just redirect)
        return redirect()->route('permohonan.edit', $permohonan)->with('success', 'Permohonan berhasil diperbarui.');
    }

    public function submit(Permohonan $permohonan)
    {
        if ($permohonan->status_akhir !== 'belum') {
            return redirect()->route('permohonan.show', $permohonan)->with('error', 'Permohonan sudah dikirim sebelumnya.');
        }

        // Validasi: Cek apakah semua dokumen wajib sudah diupload
        $dokumenBelumLengkap = PermohonanDokumen::where('permohonan_id', $permohonan->id)
            ->where('is_ada', false)
            ->exists();

        if ($dokumenBelumLengkap) {
            return redirect()->route('permohonan.show', $permohonan)
                ->with('error', 'Tidak dapat mengirim permohonan. Harap lengkapi semua dokumen persyaratan terlebih dahulu.');
        }

        // Update status ke proses
        $permohonan->update([
            'status_akhir' => 'proses',
            'submitted_at' => now(),
        ]);

        // Buat tahapan Permohonan (tahapan pertama sudah selesai)
        $masterTahapanPermohonan = \App\Models\MasterTahapan::where('nama_tahapan', 'Permohonan')->first();
        if ($masterTahapanPermohonan) {
            \App\Models\PermohonanTahapan::updateOrCreate(
                [
                    'permohonan_id' => $permohonan->id,
                    'tahapan_id' => $masterTahapanPermohonan->id,
                ],
                [
                    'status' => 'selesai',
                    'tgl_mulai' => $permohonan->created_at,
                    'tgl_selesai' => now(),
                    'catatan' => 'Permohonan dibuat dan diajukan',
                ]
            );
        }

        // Buat tahapan Verifikasi (tahapan berikutnya dimulai)
        $masterTahapanVerifikasi = \App\Models\MasterTahapan::where('nama_tahapan', 'Verifikasi')->first();
        if ($masterTahapanVerifikasi) {
            \App\Models\PermohonanTahapan::updateOrCreate(
                [
                    'permohonan_id' => $permohonan->id,
                    'tahapan_id' => $masterTahapanVerifikasi->id,
                ],
                [
                    'status' => 'proses',
                    'tgl_mulai' => now(),
                    'tgl_selesai' => null,
                    'catatan' => 'Menunggu verifikasi dokumen',
                ]
            );
        }

        // Log activity atau kirim notifikasi ke verifikator (opsional)
        // TODO: Implement notification system

        return redirect()->route('permohonan.show', $permohonan)->with('success', 'Permohonan berhasil dikirim dan sedang menunggu verifikasi.');
    }

    public function destroy(Permohonan $permohonan)
    {
        // Hanya bisa hapus kalo status belum
        if ($permohonan->status_akhir !== 'belum') {
            return redirect()->route('permohonan.show', $permohonan)->with('error', 'Permohonan sudah dalam proses dan tidak bisa dihapus.');
        }

        $permohonan->delete();
        return redirect()->route('permohonan.index')->with('success', 'Permohonan berhasil dihapus.');
    }

    private function authorizeView(Permohonan $permohonan)
    {
        $user = Auth::user();

        // Pemohon (Kabupaten/Kota) hanya bisa lihat permohonan miliknya sendiri
        if ($user->hasRole('pemohon')) {
            if ($permohonan->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }
        // Verifikator bisa lihat permohonan yang di-assign
        elseif ($user->hasRole('verifikator')) {
            $hasAccess = \App\Models\TimVerifikasiAssignment::where('permohonan_id', $permohonan->id)
                ->where('user_id', $user->id)
                ->exists();
            if (!$hasAccess) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }
        // Fasilitator bisa lihat permohonan yang di-assign
        elseif ($user->hasRole('fasilitator')) {
            $hasAccess = \App\Models\TimFasilitasiAssignment::where('permohonan_id', $permohonan->id)
                ->where('user_id', $user->id)
                ->exists();
            if (!$hasAccess) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }
    }
}
