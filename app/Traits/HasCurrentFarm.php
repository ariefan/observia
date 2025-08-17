<?php

namespace App\Traits;

trait HasCurrentFarm
{
    /**
     * Get the current user's farm ID
     */
    protected function getCurrentFarmId(): ?string
    {
        return auth()->user()->current_farm_id;
    }

    /**
     * Get the current user's farm model
     */
    protected function getCurrentFarm(): ?\App\Models\Farm
    {
        $farmId = $this->getCurrentFarmId();
        
        if (!$farmId) {
            return null;
        }

        return \App\Models\Farm::find($farmId);
    }

    /**
     * Check if user has a current farm
     */
    protected function hasCurrentFarm(): bool
    {
        return !is_null($this->getCurrentFarmId());
    }

    /**
     * Scope query to current farm
     */
    protected function scopeToCurrentFarm($query)
    {
        return $query->where('farm_id', $this->getCurrentFarmId());
    }
}