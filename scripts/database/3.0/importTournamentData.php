<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$tournamentTable = new Model_DbTable_Tournament();
$tournamentInformationTable = new Model_DbTable_TournamentInformation();
$tournamentTeamTable = new Model_DbTable_TournamentTeam();
$tournamentDivisionTable = new Model_DbTable_TournamentDivision();
$tournamentUpdateTable = new Model_DbTable_TournamentUpdate();
$tournamentMemberTable = new Model_DbTable_TournamentMember();



$stmt = $origDb->prepare('SELECT * FROM tournaments');
$stmt->execute();
$results = $stmt->fetchAll();
$totalTournaments = count($results);

if(DEBUG) {
    echo "    Importing `Tournament` data:\n";
} else {
    echo "    Importing $totalTournaments Tournaments:\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 100, $totalTournaments);    
}

$i = 0;
foreach($results as $row) {
    if(DEBUG) {
        echo "        Importing tournament `{$row['name']}`...";
    } else {
        $progressBar->update($i);
    }
    $tournament = $tournamentTable->createRow();
    $tournament->name = $row['link'];
    $tournament->year = $row['year'];
    $tournament->display_name = $row['name'];
    $tournament->email = $row['coordinator_email'];
    $tournament->is_visible = $row['visible'];
    $tournament->save();
    $tournamentInfo = $tournamentInformationTable->createRow();
    $tournamentInfo->tournament_id = $tournament->id;

    $matches = array();
    preg_match('/(\w*) (\d\d)(-\d\d)?/', $row['dates'], $matches);
    if(sizeof($matches) == 3) {
        $start = date('Y-m-d', strtotime($matches[1] . ' ' . $matches[2] . ', ' . $row['year']));
        $end = date('Y-m-d', strtotime($matches[1] . ' ' . $matches[2] . ', ' . $row['year']));
    } else {
        $matches[3] = abs($matches[3]);
        $start = date('Y-m-d', strtotime($matches[1] . ' ' . $matches[2] . ', ' . $row['year']));
        $end = date('Y-m-d', strtotime($matches[1] . ' ' . $matches[3] . ', ' . $row['year']));
    }
    $tournamentInfo->start = $start;
    $tournamentInfo->end = $end;
    $tournamentInfo->bid_due = date('Y-m-d H:i:s', strtotime($row['bid_due'] . '23:59:59'));
    $tournamentInfo->cost = $row['fee'];
    $tournamentInfo->paypal = (empty($row['paypal'])) ? null : $row['paypal'];
    $tournamentInfo->description = $row['homepage'];
    $tournamentInfo->schedule_text = $row['schedule_info'];
    $tournamentInfo->scorereporter_link = $row['results_link'];
    $tournamentInfo->location = $row['location'];
    $tournamentInfo->location_map = $row['location_link'];

    $matches = array();
    preg_match('/(.*), (.*), ([A-Z][A-Z]) (\d\d\d\d\d)/', $row['address'], $matches);

    $tournamentInfo->location_street = $matches[1];
    $tournamentInfo->location_city = $matches[2];
    $tournamentInfo->location_state = $matches[3];
    $tournamentInfo->location_zip = $matches[4];
    $tournamentInfo->hotel_link = null;
    $tournamentInfo->photo_link = null;
    $tournamentInfo->save();

    if(DEBUG) {
       echo "Done\n";
    }

    $i++;
}

if(DEBUG) {
    echo "    Done\n";
} else {
    $progressBar->update($totalTournaments);
    echo "\n";
}


$stmt = $origDb->prepare('SELECT * FROM tournament_teams');
$stmt->execute();
$results = $stmt->fetchAll();
$totalTeams = count($results);

if(DEBUG) {
    echo "    Importing `TournamentTeam` data:\n";
} else {
    echo "    Importing $totalTeams Tournament Teams:\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 100, $totalTeams);    
}

$i = 0;
foreach($results as $row) {
    if(DEBUG) {
        echo "        Importing tournament team `{$row['team']}`...";
    } else {
        $progressBar->update($i);
    }

    $tournamentTeam = $tournamentTeamTable->createRow();
    $tournamentTeam->tournament_id = $row['tournament_id'];
    $tournamentTeam->name = $row['team'];
    list($city, $state) = explode(',', $row['location']);
    $tournamentTeam->city = trim($city);
    $tournamentTeam->state = trim($state);
    $tournamentTeam->contact_name = $row['contact_name'];
    $tournamentTeam->contact_phone = $row['contact_phone'];
    $tournamentTeam->contact_email = $row['contact_email'];
    $division = $tournamentDivisionTable->fetchByName(strtolower($row['division']));
    //Zend_Debug::dump($division);
    $tournamentTeam->division = $division->id;
    $tournamentTeam->accepted = $row['accepted'];
    $tournamentTeam->paid = $row['paid'];
    $tournamentTeam->save();

    if(DEBUG) {
       echo "Done\n";
    }

    $i++;
}

if(DEBUG) {
    echo "    Done\n";
} else {
    $progressBar->update($totalTeams);
    echo "\n";
}

$stmt = $origDb->prepare('SELECT * FROM tournament_updates');
$stmt->execute();
$results = $stmt->fetchAll();
$totalUpdates = count($results);

if(DEBUG) {
    echo "    Importing `TournamentTeam` data:\n";
} else {
    echo "    Importing $totalUpdates Tournament Updates:\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 100, $totalUpdates);    
}

$i = 0;
foreach($results as $row) {
    if(DEBUG) {
        echo "        Importing tournament update `{$row['title']}`...";
    } else {
        $progressBar->update($i);
    }

    $tournamentUpdate = $tournamentUpdateTable->createRow();
    $tournamentUpdate->tournament_id = $row['tournament_id'];
    $tournamentUpdate->posted = $row['date'];
    $tournamentUpdate->title = $row['title'];
    $tournamentUpdate->content = $row['body'];
    $tournamentUpdate->save();

    if(DEBUG) {
       echo "Done\n";
    }

    $i++;
}

if(DEBUG) {
    echo "    Done\n";
} else {
    $progressBar->update($totalUpdates);
    echo "\n";
}


$stmt = $origDb->prepare('SELECT * FROM tournament_people');
$stmt->execute();
$results = $stmt->fetchAll();
$totalPeople = count($results);

if(DEBUG) {
    echo "    Importing `TournamentMember` data:\n";
} else {
    echo "    Importing $totalPeople Tournament Members:\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 100, $totalPeople);    
}

$i = 0;
foreach($results as $row) {
    if(DEBUG) {
        echo "        Importing tournament people `{$row['name']}`...";
    } else {
        $progressBar->update($i);
    }

    $tournamentMember = $tournamentMemberTable->createRow();
    $tournamentMember->tournament_id = $row['tournament_id'];
    $tournamentMember->name = $row['name'];
    $tournamentMember->type = $row['type'];
    $tournamentMember->email = $row['email'];
    $tournamentMember->weight = $row['order'];
    $tournamentMember->save();

    if(DEBUG) {
       echo "Done\n";
    }

    $i++;
}

if(DEBUG) {
    echo "    Done\n";
} else {
    $progressBar->update($totalPeople);
    echo "\n";
}

