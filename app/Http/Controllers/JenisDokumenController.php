<?php

namespace App\Http\Controllers;

use App\Models\JenisDokumen;
use Illuminate\Http\Request;

class JenisDokumenController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('role:superadmin|admin_peran');
    // }

    public function index(Request $request)
    {
        $query = JenisDokumen::query();

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('kode', 'like', '%' . $request->search . '%');
        }

        $jenisDokumen = $query->latest()->paginate(10);

        return view('jenis-dokumen.index', compact('jenisDokumen'));
    }

    public function create()
    {
        return view('jenis-dokumen.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|unique:jenis_dokumen,kode',
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        JenisDokumen::create([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'is_active' => $request->is_active ?? false,
        ]);

        return redirect()->route('jenis-dokumen.index')->with('success', 'Jenis dokumen berhasil ditambahkan.');
    }

    public function show(JenisDokumen $jenisDokumen)
    {
        return view('jenis-dokumen.show', compact('jenisDokumen'));
    }

    public function edit(JenisDokumen $jenisDokumen)
    {
        return view('jenis-dokumen.edit', compact('jenisDokumen'));
    }

    public function update(Request $request, JenisDokumen $jenisDokumen)
    {
        $request->validate([
            'kode' => 'required|string|unique:jenis_dokumen,kode,' . $jenisDokumen->id,
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $jenisDokumen->update([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'is_active' => $request->is_active ?? false,
        ]);

        return redirect()->route('jenis-dokumen.index')->with('success', 'Jenis dokumen berhasil diperbarui.');
    }

    public function destroy(JenisDokumen $jenisDokumen)
    {
        $jenisDokumen->delete();
        return redirect()->route('jenis-dokumen.index')->with('success', 'Jenis dokumen berhasil dihapus.');
    }
}