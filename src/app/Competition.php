<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code', 'areaName'
    ];

    /**
     * Get the teams associated with the competition.
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class);
    }

    /**
     * Get total players
     *
     * @param string $query
     * @param string $league_code
     *
     * @return Collection
     */
    public function scopePlayersCount($query, $league_code) {
        return $query->from('competition_team')
            ->join('competitions', function($join) {
                $join->on('competitions.id', '=', 'competition_team.competition_id');
            })->join('teams', function($join) {
                $join->on('teams.id', '=', 'competition_team.team_id');
            })->join('players', function($join) {
                $join->on('players.team_id', '=', 'teams.id');
            })->selectRaw('count(players.id) as totalPlayers')
            ->where('competitions.code', $league_code)
            ->first();
    }
}
