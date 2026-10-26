<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master OPD lingkungan Provinsi, sesuai kamus data OPD (id = id_opd).
 *
 * Akun ber-role opd wajib terhubung ke satu OPD berstatus Aktif. Relasi urusan() (tabel opd_urusan)
 * menentukan urusan yang boleh diberi masukan pada hasil fasilitasi (PRD #2). Kolom kode_urusan dan
 * urusan_pemerintahan adalah keterangan urusan yang diampu (kamus data) dan tidak dipakai untuk hak akses.
 * Daftar rumpun urusan diambil dari MasterUrusan::rumpunList().
 */
class Opd extends Model
{
    use HasFactory;

    protected $table = 'opd';

    public const STATUS_AKTIF = 'Aktif';

    /**
     * Format kode OPD: 82.XX.XX, atau 82.XX.XX.XX untuk Biro/UPTD di bawah OPD induk.
     */
    public const KODE_PATTERN = '/^\d{2}(\.\d{2}){2,3}$/';

    /**
     * @var list<string>
     */
    public const STATUS = ['Aktif', 'Tidak Aktif'];

    /**
     * Bentuk kelembagaan perangkat daerah provinsi.
     *
     * @var list<string>
     */
    public const JENIS_OPD = ['Sekretariat', 'Inspektorat', 'Dinas', 'Badan', 'Biro', 'Satuan', 'Rumah Sakit', 'UPTD'];

    protected $fillable = [
        'kode_opd',
        'kode_wilayah',
        'nama_opd',
        'singkatan',
        'jenis_opd',
        'rumpun_urusan',
        'status',
        'tanggal_update',
        'kode_urusan',
        'urusan_pemerintahan',
        'induk_opd',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_update' => 'date',
        ];
    }

    /**
     * Urusan pemerintahan yang boleh diberi masukan oleh akun OPD ini.
     */
    public function urusan(): BelongsToMany
    {
        return $this->belongsToMany(MasterUrusan::class, 'opd_urusan', 'opd_id', 'master_urusan_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * OPD induk (untuk UPTD/Biro), lewat kode_opd.
     */
    public function induk(): BelongsTo
    {
        return $this->belongsTo(self::class, 'induk_opd', 'kode_opd');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_AKTIF;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF);
    }
}
