<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$clubTable = new Model_DbTable_Club();
$clubCaptainTable = new Model_DbTable_ClubCaptain();

$clubs = array(
    array(
        'name' => 'Steamboat',
        'type' => 'Mixed',
        'facebook' => 'steamboatultimate',
        'twitter' => 'cincysfb',
        'begin' => '2008',
        'end' => null,
        'email' => 'cincymixedultimate@gmail.com',
        'website' => 'https://sites.google.com/site/steamboatultimate/',
        'content' => "<p>Steamboat is a mixed team that was started in 2008 and
            has continued to be the Cincinnati Mixed team since.  It is going on
            for the 3rd year in a row.  Steamboat practices at least once a week
            and goes to 4-6 tournaments during the summer/fall months.  The team
            is selected after a tryout for both the women and men.</p>
            <p>To view past tournaments or more information on which tournaments
            Steamboat has attended take a look at their website.</p>",
    ),
    array(
        'name' => 'Hustle',
        'type' => 'Open',
        'facebook' => null,
        'twitter' => null,
        'begin' => '2011',
        'end' => null,
        'email' => 'neil.narayan@gmail.com',
        'website' => null,
        'content' => '<p><a href="mailto:bsageccp@gmail.com">Ben Sage</a> and
            <a href="mailto:neil.narayan@gmail.com">Neil Narayan</a> would like
            to officially announce that we are starting a new open ultimate team
            for this 2011 season. Although we have not yet finalized our schedule,
            we are planning on holding practice once or twice per week, and
            competing in 4-5 tournaments over the summer. We are not planning on
            making cuts, so anybody who is interested in playing ultimate at a
            competitive level is welcome to participate.</p>',
    ),
    array(
        'name' => 'Age Against the Machine',
        'type' => 'Masters, Grand Masters',
        'facebook' => null,
        'twitter' => null,
        'begin' => '1990',
        'end' => '2010',
        'email' => null,
        'website' => null,
        'content' => "<p>Since rising from the ashes of their youth in the late
            1990's, Age Against the Machine has led the struggle for spirited
            Old Man Ultimate. With appearances at the 2001 and 2004 Masters
            Nationals, and 2009 and 2010 Grand Masters Nationals, this collection
            of crafty, crusty and creaky men continue to encourage their
            fellow aged to wake up, know your enemy, and settle for nothing.
            Like bulls on parade, these men, formerly perky and even approaching
            athletic, but now armed only with their wisdom and perseverance, and
            lacking in skill what they also lack in speed, will rise up one final
            time against the unrelenting Machine.</p>",
    ),
    array(
        'name' => 'Fine Young Callahans',
        'type' => 'Masters',
        'facebook' => null,
        'twitter' => null,
        'begin' => '2011',
        'end' => null,
        'email' => null,
        'website' => null,
        'content' => "<p>Starting up for the 2011 season, Fine Young Callahans
            intends to build up to Nationals-level caliber in the masters division.
            See Michael Rimler or Russ Johnson for more information.</p>",
    ),
    array(
        'name' => 'Dish',
        'type' => 'Mixed',
        'facebook' => null,
        'twitter' => null,
        'begin' => 'Unknown',
        'end' => 'Unknown',
        'email' => null,
        'website' => null,
        'content' => "<p>Cincinnati's co-ed team, perennial favorites at the Annual
            Co-ed Tournament Gender Blender.</p>",
    ),/*
    array(
        'name' => 'Cinister',
        'type' => 'College Open',
        'facebook' => 'pages/Cinister-Ultimate-Frisbee/195271320488338',
        'twitter' => 'CinisterUF',
        'begun' => '2008',
        'end' => null,
        'email' => 'cinisterultimate@gmail.com',
        'website' => 'http://www.uc.edu/groups/ultimatefrisbee/',
        'content' => "<p>This is one of Cincinnati's college teams from University of Cincinnati.</p>",
    ),
    array(
        'name' => 'BLOB',
        'type' => 'College Open',
        'facebook' => null,
        'twitter' => 'BLOB_ultimate',
        'begun' => 'Fall 2007',
        'end' => null,
        'website' => 'http://www.xavier.edu/clubsports/ultimate-frisbee',
        'content' => "<p>This is one of Cincinnati's college teams from Xavier University.</p>",
    ),*/
);

$captains = array(
    array(
        'club_id' => 1,
        'user_id' => 299,
    ),
    array(
        'club_id' => 1,
        'user_id' => 83,
    ),
    array(
        'club_id' => 1,
        'user_id' => 41,
    ),
    array(
        'club_id' => 2,
        'user_id' => 49,
    ),
    array(
        'club_id' => 2,
        'user_id' => 65,
    ),
    array(
        'club_id' => 3,
        'user_id' => 172,
    ),
);

$totalClubs = count($clubs);

if(DEBUG) {
    echo "    Importing `Club` data:\n";
} else {
    echo "    Importing $totalClubs Clubs:\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 50, $totalClubs);
}

$i = 0;
$clubTable->getAdapter()->beginTransaction();
foreach($clubs as $club) {
    if(DEBUG) {
        echo "        Importing club item `{$club['name']}`...";
    } else {
        $progressBar->update($i);
    }
    $clubObject = $clubTable->createRow();
    $clubObject->name = $club['name'];
    $clubObject->type = $club['type'];
    $clubObject->facebook = $club['facebook'];
    $clubObject->twitter = $club['twitter'];
    $clubObject->begin = $club['begin'];
    $clubObject->end = $club['end'];
    $clubObject->email = $club['email'];
    $clubObject->website = $club['website'];
    $clubObject->content = $club['content'];
    $clubObject->updated_at = date('Y-m-d H:i:s');
    $clubObject->last_updated_by = 1;
    $clubObject->save();
    if(DEBUG) {
        echo "Done\n";
    }
    $i++;
}
$clubTable->getAdapter()->commit();

if(!DEBUG) {
    $progressBar->update($totalClubs);
    echo "\n";
}

$totalCaptains = count($captains);

if(DEBUG) {
    echo "        Importing captains:\n";
} else {
    echo "    Importing $totalCaptains Club Captains:\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 50, $totalCaptains);
}

$i = 0;
$clubTable->getAdapter()->beginTransaction();
foreach($captains as $captain) {
    if(DEBUG) {
        echo "            Importing captain #{$captain['user_id']}...";
    } else {
        $progressBar->update($i);
    }

    $captainObject = $clubCaptainTable->createRow();
    $captainObject->club_id = $captain['club_id'];
    $captainObject->user_id = $captain['user_id'];
    $captainObject->save();

    if(DEBUG) {
        echo "Done.\n";
    }

    $i++;
}
$clubTable->getAdapter()->commit();

if(DEBUG) {
    echo "    Done\n";
} else {
    $progressBar->update($totalCaptains);
    echo "\n";
}

