<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Report extends Model
{
    protected $fillable = [
        'user_id',
        'farm_id',
        'type',
        'name',
        'format',
        'start_date',
        'end_date',
        'file_path',
        'filters',
        'status',
        'file_size',
        'download_count',
        'last_downloaded_at',
        'error_message',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'filters' => 'array',
        'last_downloaded_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    // Accessors
    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) return 'Unknown';
        
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDisplayNameAttribute(): string
    {
        $typeNames = [
            'livestock-summary' => 'Ringkasan Ternak',
            'feeding-report' => 'Laporan Pemberian Pakan',
            'milking-report' => 'Laporan Produksi Susu',
            'weight-report' => 'Laporan Perkembangan Bobot',
            'health-report' => 'Laporan Kesehatan Ternak',
            'productivity-report' => 'Laporan Produktivitas',
            'financial-report' => 'Laporan Keuangan',
            'breeding-report' => 'Laporan Perkawinan',
        ];

        return $typeNames[$this->type] ?? $this->type;
    }

    // Methods
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
        $this->update(['last_downloaded_at' => now()]);
    }

    public function getDownloadUrl(): string
    {
        if (!$this->file_path || !Storage::exists($this->file_path)) {
            return '';
        }

        return route('reports.download', $this->id);
    }

    public function delete(): ?bool
    {
        // Delete the file when deleting the report record
        if ($this->file_path && Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }

        return parent::delete();
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForFarm($query, $farmId)
    {
        return $query->where('farm_id', $farmId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
