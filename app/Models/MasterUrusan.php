<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Master urusan pemerintahan, sesuai kamus data urusan (id = id_urusan).
 *
 * kode_kelompok, nama_kelompok, dan rumpun_urusan diturunkan dari digit pertama kode_urusan
 * (mis. 1.01 → kelompok 1), sedangkan opd_pengampu diturunkan dari kode_opd_pengampu, sehingga
 * kolom-kolom itu selalu konsisten. Urutan tampilan mengikuti kode_urusan.
 *
 * tampil_hasil_fasilitasi menentukan urusan yang muncul di bagian urusan hasil fasilitasi (dan pilihan
 * urusan masukan OPD); bawaan aktif untuk kelompok 1–3 saat urusan dibuat.
 */
class MasterUrusan extends Model
{
    use HasFactory;

    protected $table = 'master_urusan';

    public const STATUS_AKTIF = 'Aktif';

    /**
     * @var list<string>
     */
    public const STATUS = ['Aktif', 'Tidak Aktif'];

    /**
     * Kelompok yang bawaannya tampil di hasil fasilitasi: wajib dasar, wajib non dasar, pilihan.
     *
     * @var list<int>
     */
    public const KELOMPOK_HASIL_FASILITASI = [1, 2, 3];

    /**
     * Kelompok urusan (Permendagri 90/2019): kode → nama kelompok dan rumpun urusan.
     * Daftar rumpun ini juga dipakai master OPD.
     *
     * @var array<int, array{nama: string, rumpun: string}>
     */
    public const KELOMPOK = [
        1 => ['nama' => 'Urusan Pemerintahan Wajib yang Berkaitan dengan Pelayanan Dasar', 'rumpun' => 'Wajib Pelayanan Dasar'],
        2 => ['nama' => 'Urusan Pemerintahan Wajib yang Tidak Berkaitan dengan Pelayanan Dasar', 'rumpun' => 'Wajib Non Pelayanan Dasar'],
        3 => ['nama' => 'Urusan Pemerintahan Pilihan', 'rumpun' => 'Pilihan'],
        4 => ['nama' => 'Unsur Pendukung Urusan Pemerintahan', 'rumpun' => 'Pendukung'],
        5 => ['nama' => 'Unsur Penunjang Urusan Pemerintahan', 'rumpun' => 'Penunjang'],
        6 => ['nama' => 'Unsur Pengawasan Urusan Pemerintahan', 'rumpun' => 'Pengawasan'],
        7 => ['nama' => 'Unsur Kewilayahan', 'rumpun' => 'Kewilayahan'],
        8 => ['nama' => 'Unsur Pemerintahan Umum', 'rumpun' => 'Pemerintahan Umum'],
    ];

    protected $fillable = [
        'kode_urusan',
        'nama_urusan',
        'kode_opd_pengampu',
        'status',
        'tanggal_update',
        'tampil_hasil_fasilitasi',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_update' => 'date',
            'tampil_hasil_fasilitasi' => 'boolean',
        ];
    }

    /**
     * Isi kolom turunan dari kode_urusan dan kode_opd_pengampu.
     */
    protected static function booted(): void
    {
        static::creating(function (MasterUrusan $urusan): void {
            $urusan->tampil_hasil_fasilitasi ??= in_array((int) substr((string) $urusan->kode_urusan, 0, 1), self::KELOMPOK_HASIL_FASILITASI, true);
        });

        static::saving(function (MasterUrusan $urusan): void {
            $kelompok = self::KELOMPOK[(int) substr((string) $urusan->kode_urusan, 0, 1)] ?? null;

            if ($kelompok) {
                $urusan->kode_kelompok = substr($urusan->kode_urusan, 0, 1);
                $urusan->nama_kelompok = $kelompok['nama'];
                $urusan->rumpun_urusan = $kelompok['rumpun'];
            }

            if ($urusan->isDirty('kode_opd_pengampu')) {
                $kodes = self::splitDaftarKode($urusan->kode_opd_pengampu);
                $singkatan = Opd::whereIn('kode_opd', $kodes)->pluck('singkatan', 'kode_opd');

                $urusan->kode_opd_pengampu = $kodes === [] ? null : implode('; ', $kodes);
                $urusan->opd_pengampu = $kodes === [] ? null : collect($kodes)->map(fn (string $kode) => $singkatan[$kode] ?? $kode)->implode('; ');
            }
        });
    }

    /**
     * Pecah daftar kode (kode OPD atau kode urusan) yang dipisah titik koma.
     *
     * @return list<string>
     */
    public static function splitDaftarKode(?string $value): array
    {
        return collect(explode(';', (string) $value))->map(fn (string $kode) => trim($kode))->filter()->unique()->values()->all();
    }

    /**
     * Daftar rumpun urusan (sama dengan master OPD).
     *
     * @return list<string>
     */
    public static function rumpunList(): array
    {
        return array_column(self::KELOMPOK, 'rumpun');
    }

    /**
     * Angka urut dari kode urusan (1.01 → 101), untuk pengurutan numerik di tampilan.
     */
    public function getUrutanKodeAttribute(): int
    {
        return (int) str_replace('.', '', (string) $this->kode_urusan);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_AKTIF;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    /**
     * Urusan yang ditawarkan di bagian urusan hasil fasilitasi: berstatus Aktif dan diaktifkan untuk hasil fasilitasi.
     */
    public function scopeUntukHasilFasilitasi(Builder $query): Builder
    {
        return $query->active()->where('tampil_hasil_fasilitasi', true);
    }
}
