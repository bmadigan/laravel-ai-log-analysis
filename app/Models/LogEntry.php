<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LogEntry extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'raw',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the vector embedding for this log entry.
     */
    public function vector(): HasOne
    {
        return $this->hasOne(LogVector::class);
    }

    /**
     * Get the incidents associated with this log entry.
     */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }
}
