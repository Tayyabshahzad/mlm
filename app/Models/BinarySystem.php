<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BinarySystem extends Model
{
    use HasFactory;

    protected $table = 'binary_system';

    protected $fillable = [
        'user_id',
        'system_type',
        'current_level',
        'investment_amount',
        'total_earned',
        'current_limit',
        'auto_next_level',
        'is_active',
        'completed_at',
        'level_history'
    ];

    protected $casts = [
        'investment_amount' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'current_limit' => 'decimal:2',
        'auto_next_level' => 'boolean',
        'is_active' => 'boolean',
        'completed_at' => 'datetime',
        'level_history' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeType($query, $type)
    {
        return $query->where('system_type', $type);
    }

    public function canProgress()
    {
        return $this->total_earned >= $this->current_limit && $this->is_active;
    }

    public function getNextLevel()
    {
        $levels = $this->getLevels();
        $nextLevel = $this->current_level + 1;

        return $levels[$this->system_type][$nextLevel] ?? null;
    }

    public static function getLevels()
    {
        return [
            '2x' => [
                1 => ['limit' => 200, 'investment' => 100],
                2 => ['limit' => 400, 'investment' => 200],
                3 => ['limit' => 800, 'investment' => 400],
                4 => ['limit' => 1600, 'investment' => 800],
                5 => ['limit' => 3200, 'investment' => 1600],
                6 => ['limit' => 6400, 'investment' => 3200],
                7 => ['limit' => 12800, 'investment' => 6400],
            ],
            '7x' => [
                1 => ['limit' => 700, 'investment' => 100],
                2 => ['limit' => 1400, 'investment' => 200],
                3 => ['limit' => 2800, 'investment' => 400],
                4 => ['limit' => 5600, 'investment' => 800],
                5 => ['limit' => 11200, 'investment' => 1600],
                6 => ['limit' => 22400, 'investment' => 3200],
                7 => ['limit' => 44800, 'investment' => 6400],
            ]
        ];
    }

    public function progressToNextLevel()
    {
        if (!$this->canProgress()) {
            return false;
        }

        $nextLevel = $this->getNextLevel();
        if (!$nextLevel) {
            $this->update(['completed_at' => now()]);
            return true;
        }

        $history = $this->level_history ?? [];
        $history[] = [
            'level' => $this->current_level,
            'completed_at' => now(),
            'earned' => $this->total_earned,
            'limit' => $this->current_limit
        ];

        $this->update([
            'current_level' => $this->current_level + 1,
            'current_limit' => $nextLevel['limit'],
            'investment_amount' => $nextLevel['investment'],
            'total_earned' => 0,
            'level_history' => $history
        ]);

        return true;
    }
}