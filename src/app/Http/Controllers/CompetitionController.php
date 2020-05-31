<?php

namespace App\Http\Controllers;

use App\Competition;
use App\Services\Interfaces\FootballAPIInterface;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Support\Facades\Log;

class CompetitionController extends Controller
{
    private $football_api;

    public function __construct(FootballAPIInterface $football_api)
    {
        $this->football_api = $football_api;
    }

    /**
     * Import League
     *
     * @param string $league_code
     *
     * @return Response
     */
    public function import($league_code)
    {
        try {
            $this->football_api->import($league_code);
        } catch (Exception $e) {
            return response(['Message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (RequestException $e) {
            return response(['Message' => $e->getMessage()], Response::HTTP_GATEWAY_TIMEOUT);
        }

        return response(['Message' => 'Successfully imported'], Response::HTTP_CREATED);
    }

    /**
     * Get total players
     *
     * @param string $league_code
     *
     * @return Response
     */
    public function getTotal($league_code)
    {
        try {
            $league = Competition::where('code', $league_code)->first();

            if (!$league) {
                response('League not found.', Response::HTTP_NOT_FOUND);
            }

            $total = $league->playersCount($league_code)->totalPlayers;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response(['Message' => 'Server error'], Response::HTTP_GATEWAY_TIMEOUT);
        }

        return response(['Total' => $total], Response::HTTP_OK);
    }
}
