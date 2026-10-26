<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Master kabupaten/kota se-Provinsi Maluku Utara.
 */
class KabupatenKota extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kabupaten_kota';

    protected $fillable = [
        'kode',
        'nama',
        'jenis',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permohonan(): HasMany
    {
        return $this->hasMany(Permohonan::class);
    }

    public function suratPemberitahuan(): HasMany
    {
        return $this->hasMany(SuratPemberitahuan::class);
    }

    // Assignment Relations
    public function userAssignments(): HasMany
    {
        return $this->hasMany(UserKabkotaAssignment::class);
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_kabkota_assignments')
            ->withPivot(['role_type', 'is_pic', 'is_active', 'assigned_from', 'assigned_until', 'notes'])
            ->withTimestamps();
    }

    // Helper Methods untuk Assignment
    public function getVerifikators()
    {
        return $this->userAssignments()
            ->active()
            ->byRole('verifikator')
            ->with('user')
            ->get();
    }

    public function getFasilitators()
    {
        return $this->userAssignments()
            ->active()
            ->byRole('fasilitator')
            ->with('user')
            ->get();
    }

    public function getKoordinator()
    {
        return $this->userAssignments()
            ->active()
            ->byRole('koordinator')
            ->with('user')
            ->first();
    }

    public function getPic()
    {
        return $this->userAssignments()
            ->active()
            ->picOnly()
            ->with('user')
            ->first();
    }

    public function hasAssignedTeam(): bool
    {
        return $this->userAssignments()->active()->exists();
    }

    public function getFullNameAttribute(): string
    {
        return ucfirst($this->jenis).' '.$this->nama;
    }
}
