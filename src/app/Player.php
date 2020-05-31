<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    /**
     * Get the team associated with the player
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
