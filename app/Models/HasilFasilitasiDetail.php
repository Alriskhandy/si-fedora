<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class HasilFasilitasiDetail extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'hasil_fasilitasi_detail';

    protected $fillable = [
        'hasil_fasilitasi_id',
        'tipe',
        'master_bab_id',
        'sub_bab',
        'master_urusan_id',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['tipe', 'master_bab_id', 'master_urusan_id', 'catatan'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function hasilFasilitasi()
    {
        return $this->belongsTo(HasilFasilitasi::class);
    }

    public function masterBab()
    {
        return $this->belongsTo(MasterBab::class);
    }

    public function masterUrusan()
    {
        return $this->belongsTo(MasterUrusan::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByTipe($query, $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    public function scopeSistematika($query)
    {
        return $query->where('tipe', 'sistematika');
    }

    public function scopeUrusan($query)
    {
        return $query->where('tipe', 'urusan');
    }

    public function scopeByHasilFasilitasi($query, $hasilFasilitasiId)
    {
        return $query->where('hasil_fasilitasi_id', $hasilFasilitasiId);
    }

    public function scopeByBab($query, $babId)
    {
        return $query->where('master_bab_id', $babId);
    }

    public function scopeByUrusan($query, $urusanId)
    {
        return $query->where('master_urusan_id', $urusanId);
    }

    // Accessors
    public function getTipeBadgeAttribute()
    {
        $badges = [
            'sistematika' => '<span class="badge bg-primary">Sistematika</span>',
            'urusan' => '<span class="badge bg-info">Urusan</span>',
        ];

        return $badges[$this->tipe] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    // Helper methods
    public function isSistematika(): bool
    {
        return $this->tipe === 'sistematika';
    }

    public function isUrusan(): bool
    {
        return $this->tipe === 'urusan';
    }

    public function getTitle(): string
    {
        if ($this->isSistematika() && $this->masterBab) {
            $title = 'BAB ' . $this->masterBab->nomor_bab . ' - ' . $this->masterBab->nama_bab;
            if ($this->sub_bab) {
                $title .= ' (' . $this->sub_bab . ')';
            }
            return $title;
        }

        if ($this->isUrusan() && $this->masterUrusan) {
            return $this->masterUrusan->kode_urusan . ' - ' . $this->masterUrusan->nama_urusan;
        }

        return 'Detail Fasilitasi';
    }

    public function getDescription(): string
    {
        return $this->catatan ?? '';
    }

    // Static methods
    public static function createSistematika(
        int $hasilFasilitasiId,
        int $masterBabId,
        ?string $subBab = null,
        ?string $catatan = null,
        ?int $createdBy = null
    ): self {
        return self::create([
            'hasil_fasilitasi_id' => $hasilFasilitasiId,
            'tipe' => 'sistematika',
            'master_bab_id' => $masterBabId,
            'sub_bab' => $subBab,
            'catatan' => $catatan,
            'created_by' => $createdBy ?? auth()->id,
        ]);
    }

    public static function createUrusan(
        int $hasilFasilitasiId,
        int $masterUrusanId,
        ?string $catatan = null,
        ?int $createdBy = null
    ): self {
        return self::create([
            'hasil_fasilitasi_id' => $hasilFasilitasiId,
            'tipe' => 'urusan',
            'master_urusan_id' => $masterUrusanId,
            'catatan' => $catatan,
            'created_by' => $createdBy ?? auth()->id,
        ]);
    }

    public static function getSistematika(int $hasilFasilitasiId)
    {
        return self::where('hasil_fasilitasi_id', $hasilFasilitasiId)
            ->where('tipe', 'sistematika')
            ->with('masterBab')
            ->get();
    }

    public static function getUrusan(int $hasilFasilitasiId)
    {
        return self::where('hasil_fasilitasi_id', $hasilFasilitasiId)
            ->where('tipe', 'urusan')
            ->with('masterUrusan')
            ->get();
    }

    public static function getSistematikaByBab(int $hasilFasilitasiId, int $babId)
    {
        return self::where('hasil_fasilitasi_id', $hasilFasilitasiId)
            ->where('tipe', 'sistematika')
            ->where('master_bab_id', $babId)
            ->get();
    }
}
