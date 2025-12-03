<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\JenisDokumen;
use Illuminate\Http\Request;
use App\Models\KabupatenKota;
use App\Models\TahunAnggaran;
use App\Models\JadwalFasilitasi;
use App\Models\PermohonanDokumen;
use App\Models\PersyaratanDokumen;
use Illuminate\Support\Facades\Auth;

class PermohonanController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'jenisDokumen', 'tahunAnggaran']);

        // Filter berdasarkan role
        if (Auth::user()->hasRole('pemohon')) {
            $query->where('created_by', Auth::id());
        } elseif (Auth::user()->hasRole('admin_peran')) {
            // Admin bisa liat semua permohonan
        } elseif (Auth::user()->hasRole('verifikator')) {
            $query->where('verifikator_id', Auth::id());
        } elseif (Auth::user()->hasRole('pokja')) {
            $query->where('pokja_id', Auth::id());
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nomor_permohonan', 'like', '%' . $request->search . '%')
                  ->orWhere('nama_dokumen', 'like', '%' . $request->search . '%')
                  ->orWhereHas('kabupatenKota', function($qq) use ($request) {
                      $qq->where('nama', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('jenisDokumen', function($qq) use ($request) {
                      $qq->where('nama', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by tahun
        if ($request->filled('tahun_anggaran_id')) {
            $query->where('tahun_anggaran_id', $request->tahun_anggaran_id);
        }

        $permohonan = $query->latest()->paginate(10);

        $filterOptions = [
            'tahunAnggaran' => TahunAnggaran::where('is_active', true)->get(),
            'statusOptions' => [
                'draft' => 'Draft',
                'submitted' => 'Menunggu Verifikasi',
                'verified' => 'Terverifikasi',
                'revision_required' => 'Perlu Revisi',
                'assigned' => 'Ditugaskan',
                'in_evaluation' => 'Sedang Dievaluasi',
                'draft_recommendation' => 'Draft Rekomendasi',
                'approved_by_kaban' => 'Disetujui Kaban',
                'letter_issued' => 'Surat Diterbitkan',
                'sent' => 'Terkirim',
                'follow_up' => 'Tindak Lanjut',
                'completed' => 'Selesai',
                'rejected' => 'Ditolak',
            ]
        ];

        return view('permohonan.index', compact('permohonan', 'filterOptions'));
    }

    public function create()
    {
        $tahunAnggaran = TahunAnggaran::where('is_active', true)->get();
        $jenisDokumen = JenisDokumen::where('is_active', true)->get();
        
        // Hanya jadwal yang published yang bisa dipilih
        $jadwalFasilitasi = JadwalFasilitasi::where('status', 'published')
            ->where('batas_permohonan', '>=', now())
            ->with(['tahunAnggaran', 'jenisDokumen'])
            ->get();

        return view('permohonan.create', compact('tahunAnggaran', 'jenisDokumen', 'jadwalFasilitasi'));
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'tahun_anggaran_id' => 'required|exists:tahun_anggaran,id',
    //         'jenis_dokumen_id' => 'required|exists:jenis_dokumen,id',
    //         'jadwal_fasilitasi_id' => 'required|exists:jadwal_fasilitasi,id',
    //         'nama_dokumen' => 'required|string|max:200',
    //         'tanggal_permohonan' => 'required|date',
    //         'keterangan' => 'nullable|string',
    //     ]);

    //     // Cek apakah jadwal masih aktif
    //     $jadwal = JadwalFasilitasi::find($request->jadwal_fasilitasi_id);
    //     if ($jadwal->batas_permohonan < now()) {
    //         return redirect()->back()->withErrors(['jadwal_fasilitasi_id' => 'Jadwal permohonan sudah ditutup.']);
    //     }

    //     $permohonan = Permohonan::create([
    //         'tahun_anggaran_id' => $request->tahun_anggaran_id,
    //         'jenis_dokumen_id' => $request->jenis_dokumen_id,
    //         'jadwal_fasilitasi_id' => $request->jadwal_fasilitasi_id,
    //         'nama_dokumen' => $request->nama_dokumen,
    //         'tanggal_permohonan' => $request->tanggal_permohonan,
    //         'keterangan' => $request->keterangan,
    //         'status' => 'draft',
    //         'created_by' => Auth::id(),
    //         'kabupaten_kota_id' => Auth::user()->kabupaten_kota_id,
    //     ]);

    //     return redirect()->route('permohonan.edit', $permohonan)->with('success', 'Permohonan berhasil dibuat. Silakan lengkapi dokumen persyaratan.');
    // }
//     public function store(Request $request)
// {
    // $request->validate([
    //     'tahun_anggaran_id' => 'required|exists:tahun_anggaran,id',
    //     'jenis_dokumen_id' => 'required|exists:jenis_dokumen,id',
    //     'jadwal_fasilitasi_id' => 'required|exists:jadwal_fasilitasi,id',
    //     'nama_dokumen' => 'required|string|max:200',
    //     'tanggal_permohonan' => 'required|date',
    //     'keterangan' => 'nullable|string',
    // ]);

    // // Cek apakah jadwal masih aktif
    // $jadwal = JadwalFasilitasi::find($request->jadwal_fasilitasi_id);
    // if ($jadwal->batas_permohonan < now()) {
    //     return redirect()->back()->withErrors(['jadwal_fasilitasi_id' => 'Jadwal permohonan sudah ditutup.']);
    // }

//     // Generate nomor permohonan
//     $tahun = now()->year;
//     $bulan = now()->format('m');
//     $counter = Permohonan::whereYear('created_at', $tahun)->count() + 1;
//     $nomor_permohonan = sprintf("%03d/%s/%s", $counter, $bulan, $tahun);

//     $permohonan = Permohonan::create([
//         'tahun_anggaran_id' => $request->tahun_anggaran_id,
//         'jenis_dokumen_id' => $request->jenis_dokumen_id,
//         'jadwal_fasilitasi_id' => $request->jadwal_fasilitasi_id,
//         'nama_dokumen' => $request->nama_dokumen,
//         'tanggal_permohonan' => $request->tanggal_permohonan,
//         'keterangan' => $request->keterangan,
//         'nomor_permohonan' => $nomor_permohonan, // <-- Tambahin ini
//         'status' => 'draft',
//         'created_by' => Auth::id(),
//         'kabupaten_kota_id' => Auth::user()->kabupaten_kota_id,
//     ]);

//     return redirect()->route('permohonan.edit', $permohonan)->with('success', 'Permohonan berhasil dibuat. Silakan lengkapi dokumen persyaratan.');
// }
public function store(Request $request)
{
    $request->validate([
        'tahun_anggaran_id' => 'required|exists:tahun_anggaran,id',
        'jenis_dokumen_id' => 'required|exists:jenis_dokumen,id',
        'jadwal_fasilitasi_id' => 'required|exists:jadwal_fasilitasi,id',
        'nama_dokumen' => 'required|string|max:200',
        'tanggal_permohonan' => 'required|date',
        'keterangan' => 'nullable|string',
    ]);

    // Cek apakah jadwal masih aktif
    $jadwal = JadwalFasilitasi::find($request->jadwal_fasilitasi_id);
    if ($jadwal->batas_permohonan < now()) {
        return redirect()->back()->withErrors(['jadwal_fasilitasi_id' => 'Jadwal permohonan sudah ditutup.']);
    }

    // Generate nomor permohonan
    $tahun = now()->year;
    $bulan = now()->format('m');
    $counter = Permohonan::whereYear('created_at', $tahun)->count() + 1;
    $nomor_permohonan = sprintf("%03d/%s/%s", $counter, $bulan, $tahun);

    // Buat permohonan
    $permohonan = Permohonan::create([
        'tahun_anggaran_id' => $request->tahun_anggaran_id,
        'jenis_dokumen_id' => $request->jenis_dokumen_id,
        'jadwal_fasilitasi_id' => $request->jadwal_fasilitasi_id,
        'nama_dokumen' => $request->nama_dokumen,
        'tanggal_permohonan' => $request->tanggal_permohonan,
        'keterangan' => $request->keterangan,
        'nomor_permohonan' => $nomor_permohonan,
        'status' => 'draft',
        'created_by' => Auth::id(),
        'kabupaten_kota_id' => Auth::user()->kabupaten_kota_id,
    ]);

    // Auto-generate dokumen persyaratan berdasarkan jenis dokumen
    $persyaratan = PersyaratanDokumen::where('jenis_dokumen_id', $request->jenis_dokumen_id)->get();
    foreach ($persyaratan as $item) {
        PermohonanDokumen::create([
            'permohonan_id' => $permohonan->id,
            'persyaratan_dokumen_id' => $item->id,
            'is_ada' => false, // Default: dokumen belum diupload
            'status_verifikasi' => 'pending',
        ]);
    }

    return redirect()->route('permohonan.edit', $permohonan)->with('success', 'Permohonan berhasil dibuat. Silakan lengkapi dokumen persyaratan.');
}
    public function show(Permohonan $permohonan)
    {
        // Cek hak akses
        $this->authorizeView($permohonan);

        return view('permohonan.show', compact('permohonan'));
    }

    public function edit(Permohonan $permohonan)
    {
        // Hanya bisa edit kalo status draft
        if ($permohonan->status !== 'draft') {
            return redirect()->route('permohonan.show', $permohonan)->with('error', 'Permohonan sudah dikirim dan tidak bisa diedit.');
        }

        // Cek hak akses
        $this->authorizeView($permohonan);

        $tahunAnggaran = TahunAnggaran::where('is_active', true)->get();
        $jenisDokumen = JenisDokumen::where('is_active', true)->get();
        $jadwalFasilitasi = JadwalFasilitasi::where('status', 'published')
            ->where('batas_permohonan', '>=', now())
            ->get();

        return view('permohonan.edit', compact('permohonan', 'tahunAnggaran', 'jenisDokumen', 'jadwalFasilitasi'));
    }

    public function update(Request $request, Permohonan $permohonan)
    {
        // Hanya bisa edit kalo status draft
        if ($permohonan->status !== 'draft') {
            return redirect()->route('permohonan.show', $permohonan)->with('error', 'Permohonan sudah dikirim dan tidak bisa diedit.');
        }

        $request->validate([
            'tahun_anggaran_id' => 'required|exists:tahun_anggaran,id',
            'jenis_dokumen_id' => 'required|exists:jenis_dokumen,id',
            'jadwal_fasilitasi_id' => 'required|exists:jadwal_fasilitasi,id',
            'nama_dokumen' => 'required|string|max:200',
            'tanggal_permohonan' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        // Cek apakah jadwal masih aktif
        $jadwal = JadwalFasilitasi::find($request->jadwal_fasilitasi_id);
        if ($jadwal->batas_permohonan < now()) {
            return redirect()->back()->withErrors(['jadwal_fasilitasi_id' => 'Jadwal permohonan sudah ditutup.']);
        }

        $permohonan->update([
            'tahun_anggaran_id' => $request->tahun_anggaran_id,
            'jenis_dokumen_id' => $request->jenis_dokumen_id,
            'jadwal_fasilitasi_id' => $request->jadwal_fasilitasi_id,
            'nama_dokumen' => $request->nama_dokumen,
            'tanggal_permohonan' => $request->tanggal_permohonan,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('permohonan.edit', $permohonan)->with('success', 'Permohonan berhasil diperbarui.');
    }

    // public function submit(Permohonan $permohonan)
    // {
    //     // Hanya bisa submit kalo status draft
    //     if ($permohonan->status !== 'draft') {
    //         return redirect()->route('permohonan.show', $permohonan)->with('error', 'Permohonan sudah dikirim sebelumnya.');
    //     }

    //     // Cek apakah semua dokumen persyaratan sudah diupload (ini bisa diimplementasiin nanti)
    //     // $dokumenBelumLengkap = $permohonan->permohonanDokumen()
    //     //     ->where('is_ada', false)
    //     //     ->exists();
        
    //     // if ($dokumenBelumLengkap) {
    //     //     return redirect()->back()->with('error', 'Silakan lengkapi semua dokumen persyaratan terlebih dahulu.');
    //     // }

    //     $permohonan->update([
    //         'status' => 'submitted',
    //         'submitted_at' => now(),
    //     ]);

    //     return redirect()->route('permohonan.show', $permohonan)->with('success', 'Permohonan berhasil dikirim dan sedang menunggu verifikasi.');
    // }
    public function submit(Permohonan $permohonan)
{
    if ($permohonan->status !== 'draft') {
        return redirect()->route('permohonan.show', $permohonan)->with('error', 'Permohonan sudah dikirim sebelumnya.');
    }

    // Cari verifikator pertama (bisa random atau pake round-robin)
    $verifikator = \App\Models\User::whereHas('roles', function($q) {
        $q->where('name', 'verifikator');
    })->first();

    if (!$verifikator) {
        return redirect()->back()->with('error', 'Tidak ada Tim Verifikasi yang tersedia.');
    }

    $permohonan->update([
        'status' => 'submitted',
        'submitted_at' => now(),
        'verifikator_id' => $verifikator->id, // <-- Tambahin ini
    ]);

    return redirect()->route('permohonan.show', $permohonan)->with('success', 'Permohonan berhasil dikirim dan sedang menunggu verifikasi.');
}

    public function destroy(Permohonan $permohonan)
    {
        // Hanya bisa hapus kalo status draft
        if ($permohonan->status !== 'draft') {
            return redirect()->route('permohonan.show', $permohonan)->with('error', 'Permohonan sudah dikirim dan tidak bisa dihapus.');
        }

        $permohonan->delete();
        return redirect()->route('permohonan.index')->with('success', 'Permohonan berhasil dihapus.');
    }

    private function authorizeView(Permohonan $permohonan)
    {
        $user = Auth::user();
        
        // Kabupaten/Kota hanya bisa lihat permohonan miliknya sendiri
        if ($user->hasRole('kabkota')) {
            if ($permohonan->created_by !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }
        // Verifikator hanya bisa lihat permohonan yang ditugaskan ke dia
        elseif ($user->hasRole('verifikator')) {
            if ($permohonan->verifikator_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }
        // Pokja hanya bisa lihat permohonan yang ditugaskan ke dia
        elseif ($user->hasRole('pokja')) {
            if ($permohonan->pokja_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }
    }
}