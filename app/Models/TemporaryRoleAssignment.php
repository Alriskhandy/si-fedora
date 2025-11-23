<?php

namespace App\Models;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model;

class TemporaryRoleAssignment extends Model
{
    protected $fillable = ['user_id', 'role_id', 'start_date', 'end_date', 'delegated_by'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function delegator() {
        return $this->belongsTo(User::class, 'delegated_by');
    }
}