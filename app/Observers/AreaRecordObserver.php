<?php

namespace App\Observers;

use App\Models\AreaRecord;

class AreaRecordObserver
{
    /**
     * Handle the AreaRecord "created" event.
     */
    public function creating(AreaRecord $areaRecord)
    {
        if ($areaRecord->recordable) {
            $areaRecord->recordable_name = $areaRecord->recordable->name;
        }
    }

    /**
     * Handle the AreaRecord "updated" event.
     */
    public function updating(AreaRecord $areaRecord)
    {
        if ($areaRecord->recordable) {
            $areaRecord->recordable_name = $areaRecord->recordable->name;
        }
    }

    /**
     * Handle the AreaRecord "deleted" event.
     */
    public function deleted(AreaRecord $areaRecord): void
    {
        //
    }

    /**
     * Handle the AreaRecord "restored" event.
     */
    public function restored(AreaRecord $areaRecord): void
    {
        //
    }

    /**
     * Handle the AreaRecord "force deleted" event.
     */
    public function forceDeleted(AreaRecord $areaRecord): void
    {
        //
    }
}
