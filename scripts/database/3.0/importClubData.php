<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$clubTable = new Cupa_Model_DbTable_Club();
$clubCaptainTable = new Cupa_Model_DbTable_ClubCaptain();

echo "    Importing `Club` data:\n";

$clubs = array(
    array(
        'name' => 'Steamboat',
        'type' => 'Club Mixed',
        'begun' => 'Summer 2008',
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
        'type' => 'Club Open',
        'begun' => 'Summer 2011',
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
        'type' => 'Masters',
        'begun' => 'Summer 1990',
        'end' => null,
        'email' => 'gwhite15@cinci.rr.com',
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

foreach($clubs as $club) {
    echo "        Importing club item `{$club['name']}`...";
    $clubObject = $clubTable->createRow();
    $clubObject->name = $club['name'];
    $clubObject->type = $club['type'];
    $clubObject->begun = $club['begun'];
    $clubObject->end = $club['end'];
    $clubObject->email = $club['email'];
    $clubObject->website = $club['website'];
    $clubObject->content = $club['content'];
    $clubObject->updated_at = date('Y-m-d H:i:s');
    $clubObject->last_updated_by = 1;
    $clubObject->save();
    echo "Done\n";
}

echo "        Importing captains...";
foreach($captains as $captain) {
    $captainObject = $clubCaptainTable->createRow();
    $captainObject->club_id = $captain['club_id'];
    $captainObject->user_id = $captain['user_id'];
    $captainObject->save();
}
echo "Done.\n";

echo "    Done\n";
