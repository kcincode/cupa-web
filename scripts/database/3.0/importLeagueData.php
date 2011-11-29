<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$userTable = new Cupa_Model_DbTable_User();

$leagueTable = new Cupa_Model_DbTable_League();
$leagueInformationTable = new Cupa_Model_DbTable_LeagueInformation();
$leagueLimitTable = new Cupa_Model_DbTable_LeagueLimit();
$leagueLocationTable = new Cupa_Model_DbTable_LeagueLocation();
$leagueTeamTable = new Cupa_Model_DbTable_LeagueTeam();
$leagueMemberTable = new Cupa_Model_DbTable_LeagueMember();
$leagueGameTable = new Cupa_Model_DbTable_LeagueGame();
$leagueGameDataTable = new Cupa_Model_DbTable_LeagueGameData();
$leagueQuestionTable = new Cupa_Model_DbTable_LeagueQuestion();
$leagueQuestionListTable = new Cupa_Model_DbTable_LeagueQuestionList();
$leagueAnswerTable = new Cupa_Model_DbTable_LeagueAnswer();

$season = array(
    1 => 'Winter',
    2 => 'Spring',
    3 => 'Summer',
    4 => 'Fall',
);

$colorLookupTable = array(
    'sapphire blue' => '#48a0c7',
    'sapphire' => '#48a0c7',
    'oceana blue' => '#737ca1',
    'dark chocolate' => '#663033',
    'lime green' => '#b1fb17',
    'carolina blue' => '#539dc2',
    'black' => '#000',
    'sport grey' => '#808080',
    'texas orange' => '#cc5500',
    'forest green' => '#254117',
    'forest' => '#254117',
    'azalea' => '#e6679a',
    'pink' => '#e6679a',
    'red' => '#c00',
    'maroon' => '#800000',
    'green/maroon' => '#800000',
    'jade dome' => '#3ea99f',
    'gold' => '#d4a017',
    'vegas gold' => '#d4a017',
    'irish green' => '#4cc417',
    'daisy' => '#fffc17',
    'purple' => '#800080',
    'safety orange' => '#ffa500',
    'yellow' => '#ffff66',
    'natural' => '#ffe7cc',
    'dark blue' => '#000066',
    'orange' => '#ff9900',
    'tangerine' => '#ff9900',
    'royal blue' => '#2b60de',
    'royal' => '#2b60de',
    'violet' => '#b5b5f2',
    'kelly green' => '#4cc496',
    'white' => '#fff',
    'green' => '#0c0',
    'dark purple' => '#330066',
    'metro blue' => '#153e7e',
    'blue' => '#3273dc',
    'col. blue' => '#38acec',
    'cardinal' => '#900',
    'silver' => '#c0c0c0',
    'clear' => '#fff',
    'white or dark' => '#fff',
    'all' => '#fff',
);


echo "    Importing `League` data:\n";

$stmt = $origDb->prepare('SELECT * FROM events e LEFT JOIN event_data ed ON ed.event_id = e.id');
$stmt->execute();
foreach($stmt->fetchAll() as $row) {
    echo "        Importing league `{$row['name']}`:\n";
    $league = $leagueTable->createRow();
    $league->year = $row['year'];
    $league->season = ($row['type'] < 5 and $row['type'] != 0) ? $season[$row['type']] : 'Other';
    $league->day = (empty($row['day'])) ? 'Sunday' : $row['day'];
    $league->name = generateName($row['name'], $season);
    $league->info = '';
    $league->registration_begin = $row['start'] . ' 00:00:00';
    $league->registration_end = $row['end'] . ' 23:59:59';
    $league->visible_from = $row['start'] . ' 00:00:00';
    $league->save();
    
    $leagueInformation = $leagueInformationTable->createRow();
    $leagueInformation->league_id = $league->id;
    $leagueInformation->is_youth = $row['youth'];
    $leagueInformation->user_teams = $row['user_teams'];
    $leagueInformation->is_pods = 0;
    $leagueInformation->contact_email = (empty($row['contact_email'])) ? null : $row['contact_email'];
    $leagueInformation->cost = $row['cost'];
    $leagueInformation->paypal_code = getPaypalId($row['confirm']);
    $leagueInformation->description = $row['description'];
    $leagueInformation->save();
    
    $leagueLimit = $leagueLimitTable->createRow();
    $leagueLimit->league_id = $league->id;
    $leagueLimit->male_players = null;
    $leagueLimit->female_players = null;
    $leagueLimit->total_players = ($row['max_players'] == 0) ? null : $row['max_players'];
    $leagueLimit->teams = $row['max_teams'];
    $leagueLimit->save();

    // do league location
    if(!empty($row['location_link_text'])) {
        $leagueLocation = $leagueLocationTable->createRow();
        $leagueLocation->league_id = $league->id;
        $leagueLocation->type = 'league';
        $leagueLocation->location = $row['location_text'];
        $leagueLocation->map_link = $row['location_link'];
        $leagueLocation->photo_link = null;
        $address = generateAddress($row['location_link_text']);
        $leagueLocation->address_street = $address['street'];
        $leagueLocation->address_city = $address['city'];
        $leagueLocation->address_state = $address['state'];
        $leagueLocation->address_zip = $address['zip'];
        $leagueLocation->start = $row['day_start'];
        $leagueLocation->end = $row['day_end'];
        $leagueLocation->save();
    }
    
    // do draft location
    if(!empty($row['draft_location_link_text'])) {
        $leagueLocation = $leagueLocationTable->createRow();
        $leagueLocation->league_id = $league->id;
        $leagueLocation->type = 'draft';
        $leagueLocation->location = $row['draft_location_text'];
        $leagueLocation->map_link = $row['draft_location_link'];
        $leagueLocation->photo_link = $row['draft_photos_link'];
        $address = generateAddress($row['draft_location_link_text']);
        $leagueLocation->address_street = $address['street'];
        $leagueLocation->address_city = $address['city'];
        $leagueLocation->address_state = $address['state'];
        $leagueLocation->address_zip = $address['zip'];
        $leagueLocation->start = $row['draft_start'];
        $leagueLocation->end = $row['draft_end'];
        $leagueLocation->save();
    }

    // do tournament location
    if(!empty($row['tournament_location_link_text'])) {
        $leagueLocation = $leagueLocationTable->createRow();
        $leagueLocation->league_id = $league->id;
        $leagueLocation->type = 'tournament';
        $leagueLocation->location = $row['tournament_location_text'];
        $leagueLocation->map_link = $row['tournament_location_link'];
        $leagueLocation->photo_link = null;
        $address = generateAddress($row['tournament_location_link_text']);
        $leagueLocation->address_street = $address['street'];
        $leagueLocation->address_city = $address['city'];
        $leagueLocation->address_state = $address['state'];
        $leagueLocation->address_zip = $address['zip'];
        $leagueLocation->start = $row['tournament_start'];
        $leagueLocation->end = $row['tournament_end'];
        $leagueLocation->save();
    }

    // insert the leagues director's
    $stmt2 = $origDb->prepare('SELECT * FROM event_directors ed WHERE ed.event_id = ?');
    $stmt2->execute(array($league->id));
    foreach($stmt2->fetchAll() as $row2) {
        // League Teams
        echo "            Importing league director  #{$row2['user_id']}...";
        $leagueMember = $leagueMemberTable->createRow();
        $leagueMember->league_id = $league->id;
        $leagueMember->user_id = $row2['user_id'];
        $leagueMember->position = 'director';
        $leagueMember->league_team_id = null;
        $leagueMember->save();
        echo "Done\n";
    }
}


$stmt = $origDb->prepare('SELECT * FROM event_teams et LEFT JOIN teams t ON et.team_id = t.id');
$stmt->execute();
foreach($stmt->fetchAll() as $row) {
    // League Teams
    echo "            Importing league team  `{$row['name']}`...";
    $leagueTeam = $leagueTeamTable->createRow();
    $leagueTeam->league_id = $row['event_id'];
    $leagueTeam->name = $row['name'];
    $leagueTeam->color = $row['color'];
    $codes = generateColorCodes(strtolower($row['color']), $colorLookupTable);
    $leagueTeam->color_code = $codes['color'];
    $leagueTeam->text_code = $codes['text'];
    $leagueTeam->final_rank = null;
    $leagueTeam->save();

    while($leagueTeam->id < $row['id']) {
        $leagueTeam->delete();

        $leagueTeam = $leagueTeamTable->createRow();
        $leagueTeam->league_id = $row['event_id'];
        $leagueTeam->name = $row['name'];
        $leagueTeam->color = $row['color'];
        $codes = generateColorCodes(strtolower($row['color']), $colorLookupTable);
        $leagueTeam->color_code = $codes['color'];
        $leagueTeam->text_code = $codes['text'];
        $leagueTeam->final_rank = null;
        $leagueTeam->save();
        echo "RESAVE #{$leagueTeam->id}...";
    }

    // insert the team's captain
    $leagueMember = $leagueMemberTable->createRow();
    $leagueMember->league_id = $row['event_id'];
    $leagueMember->user_id = $row['captain'];
    $leagueMember->position = 'captain';
    $leagueMember->league_team_id = null;
    $leagueMember->created_at = date('Y-m-d H:i:s');
    $leagueMember->modified_at = date('Y-m-d H:i:s');
    $leagueMember->save();
    echo "Done\n";
}

// insert the league players
$stmt = $origDb->prepare('SELECT * FROM event_players');
$stmt->execute();
foreach($stmt->fetchAll() as $row) {
    
    $league = $leagueTable->find($row['event_id'])->current();
    
    if(strstr($row['user_id'], '-')) {
        list($parentId, $minorId) = explode('-', $row['user_id']);
        
        // get minor data
        $stmt = $origDb->prepare('SELECT * FROM user_minors WHERE id = ?');
        $stmt->execute(array($minorId));
        $minor = $stmt->fetch();
        
        // get new user id
        $user = $userTable->fetchMinor($parentId, $minor['first_name'], $minor['last_name']);

        if($user) {
            // save data
            echo "            Importing minor player #{$user->id}...";
            $leagueMember = $leagueMemberTable->createRow();
            $leagueMember->league_id = $row['event_id'];
            $leagueMember->user_id = $user->id;
            $leagueMember->position = 'player';

            if($leagueTeamTable->find($row['team_id'])->current()) {
                $leagueMember->league_team_id = ($row['team_id'] == 0) ? null : $row['team_id'];
            } else {
                $leagueMember->league_team_id = null;

            }
            
            $leagueMember->created_at = $league->registration_end;
            $leagueMember->modified_at = $league->registration_end;

            $leagueMember->save();
            echo "Done\n";
        }
    } else {
        // League Teams
        echo "            Importing player #{$row['user_id']}...";
        $leagueMember = $leagueMemberTable->createRow();
        $leagueMember->league_id = $row['event_id'];
        $leagueMember->user_id = $row['user_id'];
        $leagueMember->position = 'player';
        $leagueMember->league_team_id = ($row['team_id'] == 0) ? null : $row['team_id'];
        
        $leagueMember->created_at = $league->registration_end;
        $leagueMember->modified_at = $league->registration_end;

        $leagueMember->save();
        echo "Done\n";
    }
    
}


// insert the league players
$stmt = $origDb->prepare('SELECT * FROM event_games');
$stmt->execute();
foreach($stmt->fetchAll() as $row) {
        echo "            Importing game #{$row['team2_id']} vs #{$row['team1_id']}...";
        
        // fetch or create the game
        $leagueGame = $leagueGameTable->fetchGame($row['date'], $row['week'], $row['field']);
        if(!$leagueGame) {
            $leagueGame = $leagueGameTable->createRow();
            $leagueGame->league_id = $row['event_id'];
            $leagueGame->day = $row['date'];
            $leagueGame->week = $row['week'];
            $leagueGame->field = $row['field'];
            $leagueGame->save();
        }
        
        if($leagueTeamTable->find($row['team2_id'])->current()) {
            // insert the game data for home and away teams
            $leagueGameData = $leagueGameDataTable->createRow();
            $leagueGameData->league_game_id = $leagueGame->id;
            $leagueGameData->type = 'home';
            $leagueGameData->league_team_id = $row['team2_id'];
            $leagueGameData->score = $row['team2_score'];
            $leagueGameData->save();
        }

        if($leagueTeamTable->find($row['team1_id'])->current()) {
            $leagueGameData = $leagueGameDataTable->createRow();
            $leagueGameData->league_game_id = $leagueGame->id;
            $leagueGameData->type = 'away';
            $leagueGameData->league_team_id = $row['team1_id'];
            $leagueGameData->score = $row['team1_score'];
            $leagueGameData->save();
        }
        
        echo "Done\n";    
}

// insert the league players
$stmt = $origDb->prepare('SELECT * FROM event_questions');
$stmt->execute();
foreach($stmt->fetchAll() as $row) {
    echo "            Importing league question '{$row['name']}'...";
    $leagueQuestion = $leagueQuestionTable->createRow();
    $leagueQuestion->name = $row['name'];
    $leagueQuestion->title = $row['title'];
    $leagueQuestion->type = $row['type'];
    $leagueQuestion->answers = $row['answers'];
    $leagueQuestion->save();
    
    $notEvents = array();
    if($row['event_id'] == 0) {
        $notEvents = explode(',', $row['not_events']);

        foreach($leagueTable->fetchAll() as $league) {
            if(!in_array($league->id, $notEvents)) {
                $leagueQuestionList = $leagueQuestionListTable->createRow();
                $leagueQuestionList->league_id = $league->id;
                $leagueQuestionList->league_question_id = $leagueQuestion->id;
                $leagueQuestionList->required = $row['required'];
                $leagueQuestionList->weight = $row['order'];
                $leagueQuestionList->save();
            }
        }
    } else {
        $leagueQuestionList = $leagueQuestionListTable->createRow();
        $leagueQuestionList->league_id = $league->id;
        $leagueQuestionList->league_question_id = $leagueQuestion->id;
        $leagueQuestionList->required = $row['required'];
        $leagueQuestionList->weight = $row['order'];
        $leagueQuestionList->save();
    }
    
    echo "Done.\n";
}

// insert the league players
$stmt = $origDb->prepare('SELECT * FROM event_info');
$stmt->execute();
foreach($stmt->fetchAll() as $row) {
    
    $user = null;
    
    if(strstr($row['user_id'], '-')) {
        list($parentId, $minorId) = explode('-', $row['user_id']);

        // get minor data
        $stmt = $origDb->prepare('SELECT * FROM user_minors WHERE id = ?');
        $stmt->execute(array($minorId));
        $minor = $stmt->fetch();

        // get new user id
        $user = $userTable->fetchMinor($parentId, $minor['first_name'], $minor['last_name']);
    }

    if($user) {
        $row['user_id'] =  $user->id;
    }
    
    $leagueMember = $leagueMemberTable->fetchMember($row['event_id'], $row['user_id']);


    if($leagueMember) {
        echo "            Importing league answers for user #'{$row['user_id']}'...";
        $leagueMember->paid = $row['paid'];
        $leagueMember->release = $row['release'];
        $leagueMember->save();

        foreach(Zend_Json::decode($row['data']) as $question => $answer) {
            //echo "                Importing league answer for '$question'...";
            $leagueQuestion = $leagueQuestionTable->fetchQuestion($question);
            if($leagueQuestion) {
                $leagueAnswer = $leagueAnswerTable->createRow();
                $leagueAnswer->league_member_id = $leagueMember->id;
                $leagueAnswer->league_question_id = $leagueQuestion->id;
                $leagueAnswer->answer = $answer;
                $leagueAnswer->save();
            }
            //echo "Done.\n";
        }

        echo "Done.\n";
    }
}



echo "        Done\n";


function generateName($name, $seasons)
{
    $name = strtolower($name);
    
    // remove the 'league'
    $name = str_replace('league', '', $name);
    
    // remove the seasons texts
    foreach($seasons as $season) {
        $name = str_replace(strtolower($season), '', $name);
    }
    
    // rename roman numerals to 3
    $name = str_replace('ii', 'II', $name);
    $name = str_replace('iii', 'III', $name);
    
    return ucwords(trim($name));
}

function getPaypalId($text)
{
    $matches = array();
    preg_match('/button_id\" value=\"(.*)\">/', $text, $matches);
    if(isset($matches[1])) {
        return $matches[1];
    }
    
    return null;
}

function generateAddress($data)
{
    $matches = array();
    
    preg_match('/(.*), (.*), ([A-Z][A-Z]) ([0-9][0-9][0-9][0-9][0-9])/', $data, $matches);

    return array(
        'street' => $matches[1],
        'city' => $matches[2],
        'state' => $matches[3],
        'zip' => $matches[4],
    );
}

function generateColorCodes($color, $colorLookupTable)
{
    $colorCode = $colorLookupTable[$color];
    $textCode = calculateTextColor($colorCode);
    
    return array(
        'color' => $colorCode,
        'text' => $textCode,
    );
}

function calculateTextColor($color)
{
    if ($color[0] == '#') {
        $color = substr($color, 1);
    }

    if (strlen($color) == 6) {
        list($r, $g, $b) = array($color[0].$color[1],
        $color[2].$color[3],
        $color[4].$color[5]);
    } else if (strlen($color) == 3) {
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    } else {
        return false;
    }

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    $diffWhite = colorDiff($r, $g, $b, 255, 255, 255);
    $diffBlack = colorDiff($r, $g, $b, 0, 0, 0);

    if($diffBlack >= $diffWhite) {
        return '#000';
    } else {
        return '#fff';
    }
}

function colorDiff($R1,$G1,$B1,$R2,$G2,$B2)
{
    return max($R1,$R2) - min($R1,$R2) +
           max($G1,$G2) - min($G1,$G2) +
           max($B1,$B2) - min($B1,$B2);
}

