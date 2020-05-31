<?php

namespace App\Services;

use App\Competition;
use App\Player;
use App\Services\Interfaces\FootballAPIInterface;
use App\Team;
use Illuminate\Support\Facades\Http;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FootballDataApiService implements FootballAPIInterface
{
    const URL = 'https://api.football-data.org/v2';

    private $api_token;

    public function __construct()
    {
        $this->api_token = getenv('API_TOKEN');
    }

    /**
     * Import League
     *
     * @param string $league_code
     *
     * @throws Exception
     * @return boolean
     */
    public function import($league_code)
    {
        $response = Http::withHeaders([
            'X-Auth-Token' => $this->api_token
        ])->get(self::URL . '/competitions/' . $league_code . '/teams');

        if ($response->serverError()) {
            throw new Exception('Server error');
        }

        if ($response->clientError()) {
            throw new Exception('Client error - Code: ' . $response->status());
        }

        $competition = Competition::where('code', $response['competition']['code'])->first();

        if ($competition) {
            throw new Exception('League already imported.');
        }

        DB::transaction(function () use ($response) {
            try {
                $competition = new Competition;
                $competition->name = $response['competition']['name'];
                $competition->code = $response['competition']['code'];
                $competition->areaName = $response['competition']['area']['name'];
                $competition->save();
            } catch (Exception $e) {
                Log::error($e->getMessage() . PHP_EOL . 'Data: '.json_encode($response['competition']));
                throw new Exception('Server error');
            }

            foreach ($response['teams'] as $team_resp) {
                $team_db = Team::where('external_id', $team_resp['id'])->first();

                if ($team_db) {
                    $this->setTeamParticipation($competition, $team_db->id);
                    continue;
                }

                try {
                    $team = new Team;
                    $team->external_id = $team_resp['id'];
                    $team->name = $team_resp['name'];
                    $team->tla = $team_resp['tla'];
                    $team->shortName = $team_resp['shortName'];
                    $team->areaName = $team_resp['area']['name'];
                    $team->email = $team_resp['email'];
                    $team->save();
                } catch (Exception $e) {
                    Log::error($e->getMessage() . PHP_EOL . 'Data: '.json_encode($team_resp));
                    throw new Exception('Server error');
                }

                $this->setTeamParticipation($competition, $team->id);

                $players = $this->getTeamPlayers($team->external_id);

                foreach ($players as $player_resp) {
                    try {
                        $player = new Player;
                        $player->team_id = $team->id;
                        $player->name = $player_resp['name'];
                        $player->position = $player_resp['position'];
                        $player->dateOfBirth = Carbon::parse($player_resp['dateOfBirth'])->format('Y-m-d');
                        $player->countryOfBirth = $player_resp['countryOfBirth'];
                        $player->nationality = $player_resp['nationality'];
                        $player->save();
                    } catch (Exception $e) {
                        Log::error($e->getMessage() . PHP_EOL . 'Data: '.json_encode($player_resp));
                        throw new Exception('Server error');
                    }
                }
            }
        });

        return true;
    }

    /**
     * Set team participation
     *
     * @param Competition $competition
     * @param integer $team_id
     *
     * @throws Exception
     * @return boolean
     */
    private function setTeamParticipation($competition, $team_id)
    {
        $competition->teams()->attach($team_id);

        return true;
    }

    /**
     * Get Team Players
     *
     * @param string $team_id
     *
     * @throws Exception
     * @return array
     */
    private function getTeamPlayers($team_id)
    {
        $response = Http::withHeaders([
            'X-Auth-Token' => $this->api_token
        ])->retry(10000, 60)->get(self::URL . '/teams/' . $team_id);

        if ($response->failed()) {
            throw new Exception(json_decode($response->body(), true)['message']);
        }

        return $response['squad'];
    }
}
