<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminPeranController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index(Request $request)
    {
        $query = Permohonan::with(['kabupatenKota', 'jenisDokumen', 'verifikator', 'pokja']);

        // Filter berdasarkan status
        $status = $request->get('status', 'verified');
        if (in_array($status, ['verified', 'in_evaluation', 'draft_recommendation'])) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['verified', 'in_evaluation', 'draft_recommendation']);
        }

        if ($request->filled('search')) {
            $query->whereHas('kabupatenKota', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            })
            ->orWhereHas('jenisDokumen', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        $permohonan = $query->latest()->paginate(10);
        $statusOptions = [
            'verified' => 'Terverifikasi',
            'in_evaluation' => 'Sedang Dievaluasi',
            'draft_recommendation' => 'Draft Rekomendasi'
        ];
        // Tambahin ini
        $verifikatorList = User::whereHas('roles', function($q) {
            $q->where('name', 'verifikator');
        })->get();

        $pokjaList = User::whereHas('roles', function($q) {
            $q->where('name', 'fasilitator');
        })->get();

        return view('pages.admin-peran.index', compact('permohonan', 'statusOptions', 'status', 'verifikatorList', 'pokjaList'));

    }
    
    public function assign(Request $request, Permohonan $permohonan)
{
    $request->validate([
        'assign_type' => 'required|in:verifikator,pokja',
        'user_id' => 'required|exists:users,id', // Tetap pake user_id di form
    ]);

    $user = User::findOrFail($request->user_id);
    
    if ($request->assign_type == 'verifikator') {
        // Verifikator tetap assign ke user
        if (!$user->hasRole('verifikator')) {
            return back()->withErrors(['user_id' => 'User bukan Tim Verifikasi.']);
        }
        $permohonan->update([
            'verifikator_id' => $user->id,
            'status' => 'submitted',
        ]);
    } else {
        // Untuk Pokja: cari Tim Pokja yang punya user ini sebagai anggota
        $timPokja = \App\Models\TimPokja::whereHas('anggota', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->first();

        if (!$timPokja) {
            return back()->withErrors(['user_id' => 'User tidak terdaftar di Tim Pokja mana pun.']);
        }

        if (!$user->hasRole('pokja')) {
            return back()->withErrors(['user_id' => 'User bukan anggota Tim Pokja.']);
        }

        $permohonan->update([
            'pokja_id' => $timPokja->id, // Assign ke ID Tim Pokja
            'status' => 'in_evaluation',
        ]);
    }

    return back()->with('success', 'Permohonan berhasil diassign ke ' . $user->name);
}

    public function unassign(Request $request, Permohonan $permohonan)
    {
        $request->validate([
            'assign_type' => 'required|in:verifikator,pokja',
        ]);

        if ($request->assign_type == 'verifikator') {
            $permohonan->update([
                'verifikator_id' => null,
            ]);
        } else {
            $permohonan->update([
                'pokja_id' => null,
                'status' => 'verified', // Kembali ke status terverifikasi
            ]);
        }

        return back()->with('success', 'Assignment berhasil dihapus.');
    }
}