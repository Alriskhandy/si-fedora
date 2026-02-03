<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\KabupatenKota;
use App\Models\MasterJenisDokumen;
use App\Models\UserKabkotaAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TimAssignmentController extends Controller
{
    /**
     * Display listing of assignments
     */
    public function index(Request $request)
    {
        // Build query for kabupaten/kota list
        $kabkotaQuery = KabupatenKota::query();

        // Filter by specific kabupaten/kota (from filter form)
        if ($request->filled('kabkota')) {
            $kabkotaQuery->where('id', $request->kabkota);
        }

        // Get kabkota with their assignments
        $kabkotaList = $kabkotaQuery->with(['userAssignments' => function ($query) use ($request) {
            // Filter by jenis dokumen
            if ($request->filled('jenis_dokumen')) {
                $query->where('jenis_dokumen_id', $request->jenis_dokumen);
            }

            // Filter by tahun
            if ($request->filled('tahun')) {
                $query->where('tahun', $request->tahun);
            }

            // Filter by status
            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } else if ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }
            // No default filter - show all

            $query->with(['user', 'jenisDokumen'])
                ->orderByDesc('is_pic')
                ->orderBy('user_id');
        }])
            ->orderBy('nama')
            ->get()
            ->filter(function ($kabkota) {
                // Only show kabkota that have assignments
                return $kabkota->userAssignments->isNotEmpty();
            });

        $allKabkota = KabupatenKota::orderBy('nama')->get();

        // Data for form (all kabkota, not filtered)
        $kabkotaListForm = KabupatenKota::orderBy('nama')->get();
        $verifikators = User::role('verifikator')->orderBy('name')->get();
        $fasilitators = User::role('fasilitator')->orderBy('name')->get();
        $jenisDokumenList = MasterJenisDokumen::active()->orderBy('nama')->get();

        // dd($kabkotaListForm, $verifikators, $fasilitators);
        return view('pages.tim-assignment.index', compact(
            'kabkotaList',
            'allKabkota',
            'kabkotaListForm',
            'verifikators',
            'fasilitators',
            'jenisDokumenList'
        ));
    }

    /**
     * Store new assignment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kabupaten_kota_id' => 'required|exists:kabupaten_kota,id',
            'jenis_dokumen_id' => 'nullable|exists:master_jenis_dokumen,id',
            'tahun' => 'required|integer|min:2000|max:' . (date('Y') + 5),
            'nomor_surat' => 'nullable|string|max:255',
            'file_sk' => 'nullable|file|mimes:pdf|max:5120',
            'verifikator_id' => 'required|exists:users,id',
            'koordinator_fasilitator_id' => 'required|exists:users,id',
            'evaluator_ids' => 'required|array|min:1',
            'evaluator_ids.*' => 'exists:users,id',
            'assigned_from' => 'nullable|date',
            'assigned_until' => 'nullable|date|after:assigned_from',
        ], [
            'kabupaten_kota_id.required' => 'Kabupaten/Kota wajib dipilih.',
            'tahun.required' => 'Tahun wajib diisi.',
            'tahun.integer' => 'Tahun harus berupa angka.',
            'tahun.min' => 'Tahun minimal 2000.',
            'file_sk.mimes' => 'File SK harus berformat PDF.',
            'file_sk.max' => 'Ukuran file SK maksimal 2MB.',
            'verifikator_id.required' => 'PIC / Verifikator wajib dipilih.',
            'koordinator_fasilitator_id.required' => 'Koordinator Fasilitator wajib dipilih.',
            'evaluator_ids.required' => 'Minimal 1 Anggota Fasilitator wajib dipilih.',
            'evaluator_ids.min' => 'Minimal 1 Anggota Fasilitator wajib dipilih.',
            'assigned_until.after' => 'Tanggal akhir harus setelah tanggal mulai.',
        ]);

        try {
            DB::beginTransaction();

            $kabkotaId = $validated['kabupaten_kota_id'];
            $jenisDokumenId = $validated['jenis_dokumen_id'] ?? null;
            $tahun = $validated['tahun'];

            // Handle file upload
            $fileSk = null;
            if ($request->hasFile('file_sk')) {
                $file = $request->file('file_sk');
                $filename = time() . '_' . $file->getClientOriginalName();

                Log::info('Uploading SK file: ' . $filename);
                Log::info('File size: ' . $file->getSize() . ' bytes');
                Log::info('File mime: ' . $file->getMimeType());

                // Simpan ke public/sk-tim directory
                $directory = public_path('sk-tim');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                    Log::info('Created directory: ' . $directory);
                }

                $file->move($directory, $filename);
                Log::info('File stored at: ' . $directory . '/' . $filename);

                $fileSk = 'sk-tim/' . $filename;
                Log::info('File SK path saved to database: ' . $fileSk);
            }

            // Check if tim already exists for this kabkota, jenis dokumen, and tahun
            $existingTim = UserKabkotaAssignment::where('kabupaten_kota_id', $kabkotaId)
                ->where('tahun', $tahun)
                ->where(function ($q) use ($jenisDokumenId) {
                    if ($jenisDokumenId) {
                        $q->where('jenis_dokumen_id', $jenisDokumenId);
                    } else {
                        $q->whereNull('jenis_dokumen_id');
                    }
                })
                ->exists();

            if ($existingTim) {
                return back()
                    ->withInput()
                    ->withErrors(['kabupaten_kota_id' => 'Tim untuk Kabupaten/Kota, Jenis Dokumen, dan Tahun ini sudah ada.']);
            }

            // 1. Create assignment for PIC Verifikator
            UserKabkotaAssignment::create([
                'user_id' => $validated['verifikator_id'],
                'kabupaten_kota_id' => $kabkotaId,
                'jenis_dokumen_id' => $jenisDokumenId,
                'role_type' => 'verifikator',
                'is_pic' => true,
                'tahun' => $tahun,
                'nomor_surat' => $validated['nomor_surat'] ?? null,
                'file_sk' => $fileSk,
                'assigned_from' => $validated['assigned_from'] ?? null,
                'assigned_until' => $validated['assigned_until'] ?? null,
            ]);

            // 2. Create assignments for Fasilitators
            foreach ($validated['evaluator_ids'] as $evaluatorId) {
                // Skip if it's the same as Verifikator
                if ($evaluatorId == $validated['verifikator_id']) {
                    continue;
                }

                // Check if this is the koordinator fasilitator
                $isPicFasilitator = ($evaluatorId == $validated['koordinator_fasilitator_id']);

                UserKabkotaAssignment::create([
                    'user_id' => $evaluatorId,
                    'kabupaten_kota_id' => $kabkotaId,
                    'jenis_dokumen_id' => $jenisDokumenId,
                    'role_type' => 'fasilitator',
                    'is_pic' => $isPicFasilitator,
                    'tahun' => $tahun,
                    'nomor_surat' => $validated['nomor_surat'] ?? null,
                    'file_sk' => $fileSk,
                    'assigned_from' => $validated['assigned_from'] ?? null,
                    'assigned_until' => $validated['assigned_until'] ?? null,
                ]);
            }

            // Create koordinator fasilitator if not in evaluator list
            if (!in_array($validated['koordinator_fasilitator_id'], $validated['evaluator_ids'])) {
                UserKabkotaAssignment::create([
                    'user_id' => $validated['koordinator_fasilitator_id'],
                    'kabupaten_kota_id' => $kabkotaId,
                    'jenis_dokumen_id' => $jenisDokumenId,
                    'role_type' => 'fasilitator',
                    'is_pic' => true,
                    'tahun' => $tahun,
                    'nomor_surat' => $validated['nomor_surat'] ?? null,
                    'file_sk' => $fileSk,
                    'assigned_from' => $validated['assigned_from'] ?? null,
                    'assigned_until' => $validated['assigned_until'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('tim-assignment.index')
                ->with('success', 'Tim assignment berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating assignment: ' . $e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal menambahkan assignment: ' . $e->getMessage()]);
        }
    }

    /**
     * Show edit form or return JSON for AJAX
     */
    public function edit(Request $request, UserKabkotaAssignment $timAssignment)
    {
        try {
            // If request is AJAX/JSON, return data for form population
            if ($request->wantsJson() || $request->ajax()) {
                // Get all assignments for this tim (same kabkota, jenis_dokumen, tahun)
                $timMembers = UserKabkotaAssignment::where('kabupaten_kota_id', $timAssignment->kabupaten_kota_id)
                    ->where('tahun', $timAssignment->tahun)
                    ->where(function ($q) use ($timAssignment) {
                        if ($timAssignment->jenis_dokumen_id) {
                            $q->where('jenis_dokumen_id', $timAssignment->jenis_dokumen_id);
                        } else {
                            $q->whereNull('jenis_dokumen_id');
                        }
                    })
                    ->get();

                $pic = $timMembers->where('is_pic', true)->where('role_type', 'verifikator')->first();
                $koordinatorFasilitator = $timMembers->where('role_type', 'fasilitator')->where('is_pic', true)->first();
                $evaluators = $timMembers->where('role_type', 'fasilitator')
                    ->pluck('user_id')
                    ->toArray();

                return response()->json([
                    'id' => $timAssignment->id,
                    'kabupaten_kota_id' => $timAssignment->kabupaten_kota_id,
                    'jenis_dokumen_id' => $timAssignment->jenis_dokumen_id,
                    'tahun' => $timAssignment->tahun,
                    'nomor_surat' => $timAssignment->nomor_surat,
                    'file_sk' => $timAssignment->file_sk,
                    'verifikator_id' => $pic ? $pic->user_id : null,
                    'koordinator_fasilitator_id' => $koordinatorFasilitator ? $koordinatorFasilitator->user_id : null,
                    'evaluator_ids' => $evaluators,
                    'role_type' => $timAssignment->role_type,
                    'is_pic' => $timAssignment->is_pic,
                    'assigned_from' => $timAssignment->assigned_from?->format('Y-m-d'),
                    'assigned_until' => $timAssignment->assigned_until?->format('Y-m-d'),
                    'is_active' => $timAssignment->is_active,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in edit assignment: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'error' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }

        // Otherwise return edit view
        $users = User::role(['verifikator', 'fasilitator', 'koordinator'])
            ->orderBy('name')
            ->get();
        $kabkotaList = KabupatenKota::orderBy('nama')->get();

        return view('pages.tim-assignment.edit', compact('timAssignment', 'users', 'kabkotaList'));
    }

    /**
     * Update assignment (update entire tim)
     */
    public function update(Request $request, UserKabkotaAssignment $timAssignment)
    {
        $validated = $request->validate([
            'kabupaten_kota_id' => 'required|exists:kabupaten_kota,id',
            'jenis_dokumen_id' => 'nullable|exists:master_jenis_dokumen,id',
            'tahun' => 'required|integer|min:2000|max:' . (date('Y') + 5),
            'nomor_surat' => 'nullable|string|max:255',
            'file_sk' => 'nullable|file|mimes:pdf|max:10240',
            'verifikator_id' => 'required|exists:users,id',
            'koordinator_fasilitator_id' => 'required|exists:users,id',
            'evaluator_ids' => 'required|array|min:1',
            'evaluator_ids.*' => 'exists:users,id',
            'assigned_from' => 'nullable|date',
            'assigned_until' => 'nullable|date|after:assigned_from',
        ], [
            'verifikator_id.required' => 'PIC / Verifikator wajib dipilih.',
            'koordinator_fasilitator_id.required' => 'Koordinator Fasilitator wajib dipilih.',
            'evaluator_ids.required' => 'Minimal 1 Anggota Fasilitator wajib dipilih.',
            'evaluator_ids.min' => 'Minimal 1 Anggota Fasilitator wajib dipilih.',
        ]);

        try {
            DB::beginTransaction();

            // Get all current assignments for this tim
            $currentAssignments = UserKabkotaAssignment::where('kabupaten_kota_id', $timAssignment->kabupaten_kota_id)
                ->where('tahun', $timAssignment->tahun)
                ->where(function ($q) use ($timAssignment) {
                    if ($timAssignment->jenis_dokumen_id) {
                        $q->where('jenis_dokumen_id', $timAssignment->jenis_dokumen_id);
                    } else {
                        $q->whereNull('jenis_dokumen_id');
                    }
                })
                ->get();

            // Handle file upload
            $fileSk = $timAssignment->file_sk; // Keep old file by default
            if ($request->hasFile('file_sk')) {
                $file = $request->file('file_sk');
                $filename = time() . '_' . $file->getClientOriginalName();

                Log::info('Updating SK file: ' . $filename);
                Log::info('File size: ' . $file->getSize() . ' bytes');

                // Simpan ke public/sk-tim directory
                $directory = public_path('sk-tim');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                    Log::info('Created directory: ' . $directory);
                }

                $file->move($directory, $filename);
                Log::info('File updated at: ' . $directory . '/' . $filename);

                $fileSk = 'sk-tim/' . $filename;
                Log::info('New file SK path: ' . $fileSk);
            }

            // Delete all current assignments
            $currentAssignments->each->delete();

            // Create new PIC assignment
            UserKabkotaAssignment::create([
                'user_id' => $validated['verifikator_id'],
                'kabupaten_kota_id' => $validated['kabupaten_kota_id'],
                'jenis_dokumen_id' => $validated['jenis_dokumen_id'] ?? null,
                'role_type' => 'verifikator',
                'is_pic' => true,
                'tahun' => $validated['tahun'],
                'nomor_surat' => $validated['nomor_surat'] ?? null,
                'file_sk' => $fileSk,
                'assigned_from' => $validated['assigned_from'] ?? null,
                'assigned_until' => $validated['assigned_until'] ?? null,
            ]);

            // Create new fasilitator assignments
            foreach ($validated['evaluator_ids'] as $evaluatorId) {
                // Skip if same as Verifikator
                if ($evaluatorId == $validated['verifikator_id']) {
                    continue;
                }

                // Check if this is the koordinator fasilitator
                $isPicFasilitator = ($evaluatorId == $validated['koordinator_fasilitator_id']);

                UserKabkotaAssignment::create([
                    'user_id' => $evaluatorId,
                    'kabupaten_kota_id' => $validated['kabupaten_kota_id'],
                    'jenis_dokumen_id' => $validated['jenis_dokumen_id'] ?? null,
                    'role_type' => 'fasilitator',
                    'is_pic' => $isPicFasilitator,
                    'tahun' => $validated['tahun'],
                    'nomor_surat' => $validated['nomor_surat'] ?? null,
                    'file_sk' => $fileSk,
                    'assigned_from' => $validated['assigned_from'] ?? null,
                    'assigned_until' => $validated['assigned_until'] ?? null,
                ]);
            }

            // Create koordinator fasilitator if not in evaluator list
            if (!in_array($validated['koordinator_fasilitator_id'], $validated['evaluator_ids'])) {
                UserKabkotaAssignment::create([
                    'user_id' => $validated['koordinator_fasilitator_id'],
                    'kabupaten_kota_id' => $validated['kabupaten_kota_id'],
                    'jenis_dokumen_id' => $validated['jenis_dokumen_id'] ?? null,
                    'role_type' => 'fasilitator',
                    'is_pic' => true,
                    'tahun' => $validated['tahun'],
                    'nomor_surat' => $validated['nomor_surat'] ?? null,
                    'file_sk' => $fileSk,
                    'assigned_from' => $validated['assigned_from'] ?? null,
                    'assigned_until' => $validated['assigned_until'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('tim-assignment.index')
                ->with('success', 'Tim assignment berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating assignment: ' . $e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal mengupdate assignment: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle assignment status (AJAX)
     */
    public function toggleStatus(Request $request, UserKabkotaAssignment $timAssignment)
    {
        try {
            $validated = $request->validate([
                'is_active' => 'required|boolean',
            ]);

            $timAssignment->update([
                'is_active' => $validated['is_active'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diubah',
                'is_active' => $timAssignment->is_active,
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status',
            ], 500);
        }
    }

    /**
     * Toggle Tim status (all members) - AJAX
     */
    public function toggleTimStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'kabupaten_kota_id' => 'required|exists:kabupaten_kota,id',
                'tahun' => 'required|integer',
                'jenis_dokumen_id' => 'nullable|exists:master_jenis_dokumen,id',
                'is_active' => 'required|boolean',
            ]);

            // Find all assignments for this tim
            $query = UserKabkotaAssignment::where('kabupaten_kota_id', $validated['kabupaten_kota_id'])
                ->where('tahun', $validated['tahun']);

            if ($validated['jenis_dokumen_id']) {
                $query->where('jenis_dokumen_id', $validated['jenis_dokumen_id']);
            } else {
                $query->whereNull('jenis_dokumen_id');
            }

            // Update all assignments
            $updated = $query->update([
                'is_active' => $validated['is_active'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status tim berhasil diubah',
                'updated' => $updated,
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling tim status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status tim',
            ], 500);
        }
    }

    /**
     * Download SK Tim file
     */
    public function downloadSk(UserKabkotaAssignment $timAssignment)
    {
        if (!$timAssignment->file_sk) {
            Log::warning('File SK tidak ada di database untuk assignment ID: ' . $timAssignment->id);
            return response()->json(['error' => 'File SK tidak ditemukan'], 404);
        }

        $filePath = public_path($timAssignment->file_sk);

        Log::info('Mencoba akses file SK: ' . $filePath);

        if (!file_exists($filePath)) {
            Log::error('File SK tidak ditemukan di public: ' . $filePath);
            Log::error('File SK dari database: ' . $timAssignment->file_sk);
            return response()->json(['error' => 'File SK tidak ditemukan di server'], 404);
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
        ]);
    }
}
