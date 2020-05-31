<?php

use Illuminate\Support\Facades\Route;

Route::get('/import-league/{league_code}', 'CompetitionController@import')->name('import_league');

Route::get('/total-players/{league_code}', 'CompetitionController@getTotal')->name('total_players');
