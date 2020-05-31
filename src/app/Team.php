<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    /**
     * Get the competitions associated with the team
     */
    public function competitions()
    {
        return $this->belongsToMany(Competition::class);
    }
}
