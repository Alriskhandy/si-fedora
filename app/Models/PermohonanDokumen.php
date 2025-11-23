<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ==================== PERMOHONAN DOKUMEN ====================
class PermohonanDokumen extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'permohonan_dokumen';

    protected $fillable = [
        'permohonan_id',
        'persyaratan_dokumen_id',
        'is_ada',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'status_verifikasi',
        'catatan_verifikasi',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'is_ada' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function persyaratanDokumen()
    {
        return $this->belongsTo(PersyaratanDokumen::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function isVerified(): bool
    {
        return $this->status_verifikasi === 'verified';
    }
}

// ==================== EVALUASI ====================
class Evaluasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evaluasi';

    protected $fillable = [
        'permohonan_id',
        'pokja_id',
        'evaluator_id',
        'draft_rekomendasi',
        'file_draft',
        'catatan_evaluasi',
        'status',
        'started_at',
        'submitted_at',
        'approved_at',
        'feedback_kaban',
        'approved_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    const STATUS_ASSIGNED = 'assigned';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_REVISION = 'revision';
    const STATUS_APPROVED = 'approved';

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }
    

    public function pokja()
    {
        return $this->belongsTo(TimPokja::class, 'pokja_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function suratRekomendasi()
    {
        return $this->hasOne(SuratRekomendasi::class);
    }

    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_ASSIGNED, self::STATUS_IN_PROGRESS, self::STATUS_REVISION]);
    }

    public function canSubmit(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS && 
               !empty($this->draft_rekomendasi);
    }
}

// ==================== SURAT REKOMENDASI ====================
class SuratRekomendasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'surat_rekomendasi';

    protected $fillable = [
        'permohonan_id',
        'evaluasi_id',
        'nomor_surat',
        'tanggal_surat',
        'perihal',
        'isi_surat',
        'file_surat',
        'file_ttd',
        'file_lampiran',
        'status',
        'signed_by',
        'signed_at',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'signed_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_APPROVED = 'approved';
    const STATUS_SIGNED = 'signed';
    const STATUS_SENT = 'sent';

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function evaluasi()
    {
        return $this->belongsTo(Evaluasi::class);
    }

    public function signedBy()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canSign(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canSend(): bool
    {
        return $this->status === self::STATUS_SIGNED;
    }

    public static function generateNomorSurat(): string
    {
        $tahun = date('Y');
        
        $lastNumber = self::whereYear('created_at', $tahun)->count() + 1;
        
        return sprintf('%03d/PERAN/REKOMENDASI/%s', $lastNumber, $tahun);
    }
}

// ==================== ACTIVITY LOG ====================
class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_log';

    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
        'action',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function model()
    {
        return $this->morphTo();
    }

    public static function log($action, $description, $model = null, $properties = []): void
    {
        self::create([
            'user_id' => auth()->id(),
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

// ==================== NOTIFIKASI ====================
class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'model_type',
        'model_id',
        'action_url',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}