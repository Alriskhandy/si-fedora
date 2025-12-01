<?php

namespace App\Http\Controllers;

use App\Models\PermohonanDokumen;
use App\Models\Permohonan;
use App\Models\PersyaratanDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PermohonanDokumenController extends Controller
{
    public function index(Request $request)
    {
        $query = PermohonanDokumen::with(['permohonan', 'persyaratanDokumen']);

        if (Auth::user()->hasRole('kabupaten_kota')) {
            // Kabupaten/Kota hanya bisa liat dokumen permohonan miliknya sendiri
            $query->whereHas('permohonan', function($q) {
                $q->where('created_by', Auth::id());
            });
        }

        $permohonanDokumen = $query->latest()->paginate(10);

        return view('permohonan_dokumen.index', compact('permohonanDokumen'));
    }

    public function create(Request $request)
    {
        $permohonanId = $request->query('permohonan_id');
        
        $permohonan = Permohonan::where('id', $permohonanId)->firstOrFail();
        
        // Hanya bisa buat dokumen untuk permohonan milik sendiri (kalo kabkota)
        if (Auth::user()->hasRole('kabupaten_kota')) {
            if ($permohonan->created_by !== Auth::id()) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }

        $persyaratanDokumen = PersyaratanDokumen::where('jenis_dokumen_id', $permohonan->jenis_dokumen_id)->get();

        return view('permohonan_dokumen.create', compact('permohonan', 'persyaratanDokumen'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'permohonan_id' => 'required|exists:permohonan,id',
            'persyaratan_dokumen_id' => 'required|exists:persyaratan_dokumen,id|unique:permohonan_dokumen,permohonan_id,NULL,id,persyaratan_dokumen_id,' . $request->persyaratan_dokumen_id,
            'is_ada' => 'required|boolean',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB
        ]);

        $permohonan = Permohonan::findOrFail($request->permohonan_id);

        // Cek akses
        if (Auth::user()->hasRole('kabupaten_kota')) {
            if ($permohonan->created_by !== Auth::id()) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->store('permohonan_dokumen/' . $permohonan->id, 'public');
        }

        PermohonanDokumen::create([
            'permohonan_id' => $request->permohonan_id,
            'persyaratan_dokumen_id' => $request->persyaratan_dokumen_id,
            'is_ada' => $request->is_ada,
            'file_path' => $filePath,
            'file_name' => $filePath ? $fileName : null,
            'file_size' => $filePath ? $file->getSize() : null,
            'file_type' => $filePath ? $file->getMimeType() : null,
        ]);

        return redirect()->route('permohonan.show', $permohonan)->with('success', 'Dokumen persyaratan berhasil ditambahkan.');
    }

    public function show(PermohonanDokumen $permohonanDokumen)
    {
        $this->authorizeView($permohonanDokumen);
        return view('permohonan_dokumen.show', compact('permohonanDokumen'));
    }

    public function edit(PermohonanDokumen $permohonanDokumen)
    {
        $this->authorizeView($permohonanDokumen);
        
        $persyaratanDokumen = PersyaratanDokumen::where('jenis_dokumen_id', $permohonanDokumen->permohonan->jenis_dokumen_id)->get();

        return view('permohonan_dokumen.edit', compact('permohonanDokumen', 'persyaratanDokumen'));
    }

    public function update(Request $request, PermohonanDokumen $permohonanDokumen)
    {
        $request->validate([
            'is_ada' => 'required|boolean',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        // Cek akses
        if (Auth::user()->hasRole('kabupaten_kota')) {
            if ($permohonanDokumen->permohonan->created_by !== Auth::id()) {
                abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
            }
        }

        $oldFilePath = $permohonanDokumen->file_path;

        if ($request->hasFile('file')) {
            // Hapus file lama
            if ($oldFilePath) {
                Storage::disk('public')->delete($oldFilePath);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $newFilePath = $file->store('permohonan_dokumen/' . $permohonanDokumen->permohonan_id, 'public');

            $permohonanDokumen->update([
                'is_ada' => $request->is_ada,
                'file_path' => $newFilePath,
                'file_name' => $fileName,
                'file_size' => $file->getSize(),
                'file_type' => $file->getMimeType(),
            ]);
        } else {
            $permohonanDokumen->update([
                'is_ada' => $request->is_ada,
            ]);
        }

        return redirect()->route('permohonan.show', $permohonanDokumen->permohonan)->with('success', 'Dokumen persyaratan berhasil diperbarui.');
    }

    public function destroy(PermohonanDokumen $permohonanDokumen)
    {
        // Cek akses
        if (Auth::user()->hasRole('kabupaten_kota')) {
            if ($permohonanDokumen->permohonan->created_by !== Auth::id()) {
                abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
            }
        }

        // Hapus file
        if ($permohonanDokumen->file_path) {
            Storage::disk('public')->delete($permohonanDokumen->file_path);
        }

        $permohonanDokumen->delete();

        return redirect()->route('permohonan.show', $permohonanDokumen->permohonan)->with('success', 'Dokumen persyaratan berhasil dihapus.');
    }

    public function download(PermohonanDokumen $permohonanDokumen)
    {
        $this->authorizeView($permohonanDokumen);

        if (!$permohonanDokumen->file_path) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($permohonanDokumen->file_path, $permohonanDokumen->file_name);
    }

    private function authorizeView(PermohonanDokumen $permohonanDokumen)
    {
        $user = Auth::user();
        
        // Tambahin null check
        if (!$permohonanDokumen->permohonan) {
            abort(404, 'Permohonan tidak ditemukan.');
        }
    
        if ($user->hasRole('kabkota')) {
            if ($permohonanDokumen->permohonan->created_by !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
            }
        }  elseif ($user->hasRole('verifikator')) {
            if ($permohonanDokumen->permohonan->verifikator_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
            }
        } elseif ($user->hasRole('pokja')) {
            if ($permohonanDokumen->permohonan->pokja_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
            }
        }
    }
}