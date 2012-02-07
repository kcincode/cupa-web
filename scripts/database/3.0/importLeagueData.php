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
$userEmergencyTable = new Cupa_Model_DbTable_UserEmergency();

$colorLookupTable = array(
    'sapphire blue' => '#48a0c7',
    'sapphire' => '#48a0c7',
    'oceana blue' => '#737ca1',
    'dark chocolate' => '#663033',
    'lime green' => '#b1fb17',
    'carolina blue' => '#539dc2',
    'black' => '#000000',
    'sport grey' => '#808080',
    'texas orange' => '#cc5500',
    'forest green' => '#254117',
    'forest' => '#254117',
    'azalea' => '#e6679a',
    'pink' => '#e6679a',
    'red' => '#cc0000',
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
    'white' => '#ffffff',
    'green' => '#00cc00',
    'dark purple' => '#330066',
    'metro blue' => '#153e7e',
    'blue' => '#3273dc',
    'col. blue' => '#38acec',
    'cardinal' => '#990000',
    'silver' => '#c0c0c0',
    'clear' => '#ffffff',
    'white or dark' => '#ffffff',
    'all' => '#ffffff',
);

$seasons = array(
    array(
        'name' => 'winter',
        'when' => 'January - March',
        'information' => "<p>What could be better than playing ultimate in the 
            dead of winter?  Playing on two twin full-sized indoor fields.</p>
            <p>The indoor facility is called 
            <a href=\"http://www.wall2wallsoccer.com/\">Wall 2 Wall Soccer</a> 
            and it is located on Route 42 (Reading Rd) 3-4 miles north of I-275.
            Or if your coming from the north, about 1 mile south of Tylersville. 
            It's about 10 minutes from the summer league location. Click 
            <a href=\"http://www.wall2wallsoccer.com/directions.htm\">here</a> 
            for a map.</p><div 
            class=\"img-right\" style=\"float: right;\">
            <img src=\"http://cincyultimate.org/upload/turf.gif\" 
            alt=\"Indoor Turf\"/></div><p>The turf at the indoor facility is the 
            latest in technical innovation for synthetic turf. SportsTurf is 
            made up of a rubber base with a 2 inch pile of Thiolon Flex. This 
            surface closely mimics outdoor conditions such that you can wear 
            cleats or turf shoes without any problem. Typical problems with 
            artificial turf such as shin splints DO NOT apply with this surface.
            </p><p>It is a bit expensive to play. Why?!?, Field rental makes up 
            over 95% of the league fees. We are working very hard to get you the 
            most playing time for the least amount of money.</p>",
        'weight' => 0,
    ),
    array(
        'name' => 'spring',
        'when' => "March - April",
        'information' => "<p>Get ready for the summer season with our spring 
            session on stable artificial turf.</p><p>The weather is getting 
            warmer and is a great time to start getting ready for the CUPA 
            summer league.  We are trying to make it a co-ed league so females 
            are more than welcome.</p><p>The fields have lights so when it get 
            late and dark we are still able to play just fine.  Who doesn't like 
            playing under the lights and on some turf fields?</p><p>Space might 
            be limited depending upon the location so get registered as soon 
            as you can.</p><p>There is also a Mens League and a Women's Clinic 
            & League so there are plenty of oppourtunities to learn and play.</p>",
        'weight' => 1,
    ),
    array(
        'name' => 'summer',
        'when' => "June - August",
        'information' => "<p>The original and still the best.  Summer league is 
            our most popular set of leagues with several levels of experience to 
            fit everyones needs.</p><p>For those new to the game or not quite
            ready to play for real we have a Beginners league that usually has
            an experienced player coaching/leading the team.  This will help you
            understand the rules, how to play, and answer any questions you may
            have.</p><p>The next level is an intermediate league.  This league
            is one step up and has player just above beginners all the way to 
            some elite players.  The elite players are limited per team so that
            one team is not all elite players.</p><p>The elite league has been 
            re-developed recently to try to bring the most elite players from 
            around Cincinnati to come and play against all the other elite 
            players while still maintaining the fun of a league.  If you think 
            you are one of the best around, this is the the league for you.  
            It will challenge you and hopefully make you a better player.</p>",
        'weight' => 2,
    ),
    array(
        'name' => 'fall',
        'when' => "September - November",
        'information' => "<p>This is for those that just didn't get enough
            ultimate in the summer and are still looking to get the last little
            bit of ultimate in before the winter cold sets in.</p><p>It is 
            usually after the club season or near the end so the club level 
            players that would like a few last games to play can do so.  It also
            gets people out to play more ultimate which is always good.  Come
            on out and join for the Fall.</p>",
        'weight' => 3,
    ),
    
);

$totalSeasons = count($seasons);

if(DEBUG) {
    echo "    Importing `League` data:\n";
} else {
    echo "    Importing $totalSeasons League Seasons:\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 100, $totalSeasons);    
}

$seasonsArray = array();
$i = 0;
foreach($seasons as $season) {
    if(DEBUG) {
        echo "        Importing league season `{$season['name']}`:\n";    
    } else {
        $progressBar->update($i);
    }
    $leagueSeasonTable = new Cupa_Model_DbTable_LeagueSeason();
    $leagueSeason = $leagueSeasonTable->createRow();
    $leagueSeason->name = $season['name'];
    $leagueSeason->when = $season['when'];
    $leagueSeason->weight = $season['weight'];
    $leagueSeason->information = $season['information'];
    $leagueSeason->save();

    if(DEBUG) {
        echo "Done.\n";
    }
    
    $seasonsArray[] = $season['name'];
    $i++;
}

if(!DEBUG) {
    $progressBar->update($totalSeasons);
    echo "\n";
}



$stmt = $origDb->prepare('SELECT * FROM events e LEFT JOIN event_data ed ON ed.event_id = e.id');
$stmt->execute();
$results = $stmt->fetchAll();
$totalLeagues = count($results);
$prevYear = date('Y') - 1;

if(!DEBUG) {
    echo "    Importing $totalLeagues Leagues:\n";
    $progressBar->reset('    [%bar%] %percent%', '=>', '-', 100, $totalLeagues);    
}

$i = 0;
foreach($results as $row) {
    if(DEBUG) {
        echo "        Importing league `{$row['name']}`:\n";
    } else {
        $progressBar->update($i);
    }
    $league = $leagueTable->createRow();
    $league->year = $row['year'];
    $league->season = ($row['type'] < 5 and $row['type'] != 0) ? $row['type'] : null;
    $league->day = (empty($row['day'])) ? 'Sunday' : $row['day'];
    $league->name = generateName($row['name'], $seasonsArray);
    $league->info = $row['intro'];
    $league->registration_begin = $row['start'] . ' 00:00:00';
    $league->registration_end = $row['end'] . ' 23:59:59';
    $league->visible_from = $row['start'] . ' 00:00:00';
    $archived = 1;
    if($row['year'] == date('Y') or (date('m') <= 8 and $row['year'] == $prevYear)) {
        $archived = 0;
    }
    $league->is_archived = $archived;
    $league->save();
    
    $leagueInformation = $leagueInformationTable->createRow();
    $leagueInformation->league_id = $league->id;
    $leagueInformation->is_youth = $row['youth'];
    $leagueInformation->user_teams = $row['user_teams'];
    $leagueInformation->is_hat = (strstr(strtolower($league->name), 'hat')) ? 1 : 0;
    $leagueInformation->is_clinic = (strstr(strtolower($league->name), 'clinic')) ? 1 : 0;
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
        if(DEBUG) {
            echo "            Importing league director  #{$row2['user_id']}...";
        }
        $leagueMember = $leagueMemberTable->createRow();
        $leagueMember->league_id = $league->id;
        $leagueMember->user_id = $row2['user_id'];
        $leagueMember->position = 'director';
        $leagueMember->league_team_id = null;
        $leagueMember->created_at = $league->registration_begin;
        $leagueMember->modified_at = $league->registration_begin;
        $leagueMember->save();
        if(DEBUG) {
            echo "Done\n";
        }
    }

    $i++;
}

if(!DEBUG) {
    $progressBar->update($totalLeagues);
    echo "\n";
}


$stmt = $origDb->prepare('SELECT et.event_id, t.* FROM event_teams et LEFT JOIN teams t ON et.team_id = t.id ORDER BY t.id');
$stmt->execute();
$results = $stmt->fetchAll();
$totalTeams = count($results);

if(!DEBUG) {
    echo "    Importing $totalTeams League Teams:\n";
    $progressBar->reset('    [%bar%] %percent%', '=>', '-', 100, $totalTeams);    
}

$i = 0;
foreach($results as $row) {
    // League Teams
    if(DEBUG) {
        echo "            Importing league team #{$row['id']}:`{$row['name']}`...";
    } else {
        $progressBar->update($i);
    }

    $leagueTeam = $leagueTeamTable->createRow();
    $leagueTeam->league_id = $row['event_id'];
    $leagueTeam->name = $row['name'];
    $leagueTeam->color = $row['color'];
    $codes = generateColorCodes(strtolower($row['color']), $colorLookupTable);
    $leagueTeam->color_code = $codes['color'];
    $leagueTeam->text_code = $codes['text'];
    $leagueTeam->final_rank = null;
    $leagueTeam->save();

    if(DEBUG) {
        echo "as #{$leagueTeam->id}...";
    }

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

        if(DEBUG) {
            echo "RESAVE #{$leagueTeam->id}...";
        }
    }

    // insert the team's captain
    $leagueMember = $leagueMemberTable->createRow();
    $leagueMember->league_id = $row['event_id'];
    $leagueMember->user_id = $row['captain'];
    $leagueMember->position = 'captain';
    $leagueMember->league_team_id = $leagueTeam->id;
    $leagueMember->created_at = date('Y-m-d H:i:s');
    $leagueMember->modified_at = date('Y-m-d H:i:s');
    $leagueMember->save();

    if(DEBUG) {
        echo "Done\n";
    }

    $i++;
}

if(!DEBUG) {
    $progressBar->update($totalTeams);
    echo "\n";
}

// insert the league players
$stmt = $origDb->prepare('SELECT * FROM event_players');
$stmt->execute();
$results = $stmt->fetchAll();
$totalPlayers = count($results);

if(!DEBUG) {
    echo "    Importing $totalPlayers League Players:\n";
    $progressBar->reset('    [%bar%] %percent%', '=>', '-', 100, $totalPlayers);    
}

$i = 0;
foreach($results as $row) {
    
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
            if(DEBUG) {
                echo "            Importing minor player #{$user->id}...";
            } else {
                $progressBar->update($i);            
            }

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

            if(DEBUG) {
                echo "Done\n";
            }
        }
    } else {
        // League Teams
        if(DEBUG) {
            echo "            Importing player #{$row['user_id']}...";
        } else {
            $progressBar->update($i);
        }
        $leagueMember = $leagueMemberTable->createRow();
        $leagueMember->league_id = $row['event_id'];
        $leagueMember->user_id = $row['user_id'];
        $leagueMember->position = 'player';
        $leagueMember->league_team_id = ($row['team_id'] == 0) ? null : $row['team_id'];
        
        $leagueMember->created_at = $league->registration_end;
        $leagueMember->modified_at = $league->registration_end;

        $leagueMember->save();

        if(DEBUG) {
            echo "Done\n";
        }
    }

    $i++;
}

if(!DEBUG) {
    $progressBar->update($totalPlayers);
    echo "\n";
}


$stmt = $origDb->prepare('SELECT * FROM event_games');
$stmt->execute();
$results = $stmt->fetchAll();
$totalGames = count($results);

if(!DEBUG) {
    echo "    Importing $totalGames League Games:\n";
    $progressBar->reset('    [%bar%] %percent%', '=>', '-', 100, $totalGames);    
}

// insert the league players
$i = 0;
foreach($results as $row) {
    if(DEBUG) {
        echo "            Importing game #{$row['team2_id']} vs #{$row['team1_id']}...";        
    } else {
        $progressBar->update($i);
    }

    $leagueLocation = $leagueLocationTable->fetchByType($row['event_id'], 'league');
    if($leagueLocation) {
        $row['date'] = $row['date'] . ' ' . date('H:i:s', strtotime($leagueLocation->start));
    }

    //create the game
    $leagueGame = $leagueGameTable->createRow();
    $leagueGame->league_id = $row['event_id'];
    $leagueGame->day = $row['date'];
    $leagueGame->week = $row['week'];
    $leagueGame->field = $row['field'];
    $leagueGame->save();

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
    
    if(DEBUG) {
        echo "Done\n"; 
    }

    $i++;   
}

if(!DEBUG) {
    $progressBar->update($totalGames);
    echo "\n";
}

// insert the league players
$stmt = $origDb->prepare('SELECT * FROM event_questions');
$stmt->execute();
$results = $stmt->fetchAll();
$totalQuestions = count($results);

if(!DEBUG) {
    echo "    Importing $totalQuestions League Questions:\n";
    $progressBar->reset('    [%bar%] %percent%', '=>', '-', 100, $totalQuestions);    
}

$i = 0;
foreach($results as $row) {
    if(DEBUG) {
        echo "            Importing league question '{$row['name']}'...\n";
    } else {
        $progressBar->update($i);
    }
    $leagueQuestion = $leagueQuestionTable->createRow();
    $leagueQuestion->name = $row['name'];
    $leagueQuestion->title = $row['title'];
    $leagueQuestion->type = $row['type'];
    $leagueQuestion->answers = (empty($row['answers'])) ? null : $row['answers'];
    $leagueQuestion->save();
        
    $notEvents = array();
    if($row['event_id'] == 0) {
        $notEvents = explode(',', $row['not_events']);

        foreach($leagueTable->fetchAll() as $league) {
            if(!in_array($league->id, $notEvents)) {
                if(DEBUG) {
                    echo "                Adding question '{$row['name']}' to League #{$league->id}\n";
                }
                $leagueQuestionList = $leagueQuestionListTable->createRow();
                $leagueQuestionList->league_id = $league->id;
                $leagueQuestionList->league_question_id = $leagueQuestion->id;
                $leagueQuestionList->required = $row['required'];
                $leagueQuestionList->weight = $row['order'];
                $leagueQuestionList->save();
            }
        }
    } else {
        if(DEBUG) {
            echo "                Adding question '{$row['name']}' to League #{$row['event_id']}\n";
        }
        $leagueQuestionList = $leagueQuestionListTable->createRow();
        $leagueQuestionList->league_id = $row['event_id'];
        $leagueQuestionList->league_question_id = $leagueQuestion->id;
        $leagueQuestionList->required = $row['required'];
        $leagueQuestionList->weight = $row['order'];
        $leagueQuestionList->save();
    }
    
    if(DEBUG) {
        echo "            Done.\n";        
    }
    $i++;
}

if(!DEBUG) {
    $progressBar->update($totalQuestions);
    echo "\n";
}

// insert the league players
$stmt = $origDb->prepare('SELECT * FROM event_info');
$stmt->execute();
$results = $stmt->fetchAll();
$totalAnswers = count($results);

if(!DEBUG) {
    echo "    Importing $totalAnswers League Answers:\n";
    $progressBar->reset('    [%bar%] %percent%', '=>', '-', 100, $totalAnswers);    
}

$i = 0;
foreach($results as $row) {
    
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
        if(DEBUG) {
            echo "            Importing league answers for user #'{$row['user_id']}'...";
        } else {
            $progressBar->update($i);
        }

        $leagueMember->paid = $row['paid'];
        $league = $leagueTable->find($row['event_id'])->current();
        $leagueMember->release = ($userProfileTable->isEighteenOrOver($row['user_id'], $league->registration_end)) ? 1 : $row['release'];
        $leagueMember->save();

        $emergencyContacts = array();
        foreach(Zend_Json::decode($row['data']) as $question => $answer) {
            if(in_array($question, array('primaryContactName', 'primaryContactPhone', 'secondaryContactName', 'secondaryContactPhone'))) {
                $num = (substr($question, 0, 3) == 'pri') ? 0 : 1;
                $key = (substr($question, -4) == 'Name') ? 'name' : 'phone';
                $emergencyContacts[$leagueMember->user_id][$num][$key] = $answer;
            } else {
                $leagueQuestion = $leagueQuestionTable->fetchQuestion($question);
                if($leagueQuestion) {
                    $leagueAnswer = $leagueAnswerTable->createRow();
                    $leagueAnswer->league_member_id = $leagueMember->id;
                    $leagueAnswer->league_question_id = $leagueQuestion->id;
                    $leagueAnswer->answer = $answer;
                    $leagueAnswer->save();
                }
            }
        }

        foreach($emergencyContacts as $userId => $data) {
            foreach($data as $weight => $info) {
                $nameData = explode(' ', $info['name']);
                if(count($nameData) == 1) {
                    $first = $info['name'];
                    $last = '';
                } else if(count($nameData) == 2) {
                    $first = $nameData[0];
                    $last = $nameData[1];
                }
                $userEmergencyTable->insert(array(
                    'user_id' => $userId,
                    'first_name' => ucwords(trim($first)),
                    'last_name' => ucwords(trim($last)),
                    'phone' => $info['phone'],
                    'weight' => $weight,
                ));
            }
        }

        if(DEBUG) {
            echo "Done.\n";
        }

        $i++;
    }
}

if(DEBUG) {
    echo "        Done\n";
} else {
    $progressBar->update($totalAnswers);
    echo "\n";
}


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
    
    // remove slashes
    $name = str_replace('/', '', $name);
    
    $name = ucwords(trim($name));
    return (empty($name)) ? null : $name;
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
        return '#000000';
    } else {
        return '#ffffff';
    }
}

function colorDiff($R1,$G1,$B1,$R2,$G2,$B2)
{
    return max($R1,$R2) - min($R1,$R2) +
           max($G1,$G2) - min($G1,$G2) +
           max($B1,$B2) - min($B1,$B2);
}

