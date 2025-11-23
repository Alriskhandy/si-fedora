<?php

namespace App\Http\Controllers;

use App\Models\TimPokja;
use App\Models\User;
use Illuminate\Http\Request;

class TimPokjaController extends Controller
{
    public function index(Request $request)
    {
        $query = TimPokja::with(['ketua']);

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $timPokja = $query->latest()->paginate(10);

        return view('tim-pokja.index', compact('timPokja'));
    }

    public function create()
    {
        $users = User::whereHas('roles', function($q) {
            $q->where('name', 'pokja');
        })->get();
        
        return view('tim-pokja.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'ketua_id' => 'required|exists:users,id',
            'is_active' => 'boolean',
        ]);

        TimPokja::create([
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'ketua_id' => $request->ketua_id,
            'is_active' => $request->is_active ?? false,
        ]);

        return redirect()->route('tim-pokja.index')->with('success', 'Tim Pokja berhasil ditambahkan.');
    }

    public function show(TimPokja $timPokja)
    {
        $anggota = $timPokja->anggota()->with('user')->get();
        return view('tim-pokja.show', compact('timPokja', 'anggota'));
    }

    public function edit(TimPokja $timPokja)
    {
        $users = User::whereHas('roles', function($q) {
            $q->where('name', 'pokja');
        })->get();
        
        return view('tim-pokja.edit', compact('timPokja', 'users'));
    }

    public function update(Request $request, TimPokja $timPokja)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'ketua_id' => 'required|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $timPokja->update([
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'ketua_id' => $request->ketua_id,
            'is_active' => $request->is_active ?? false,
        ]);

        return redirect()->route('tim-pokja.index')->with('success', 'Tim Pokja berhasil diperbarui.');
    }

    public function destroy(TimPokja $timPokja)
    {
        $timPokja->delete();
        return redirect()->route('tim-pokja.index')->with('success', 'Tim Pokja berhasil dihapus.');
    }
}