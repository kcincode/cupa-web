<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$pickupTable = new Cupa_Model_DbTable_Pickup();
$userTable = new Cupa_Model_DbTable_User();

$pickups = array(
    array(
        'title' => 'Winton Woods',
        'day' => 'Sundays',
        'time' => '12:30pm - ??',
        'info' => null,
        'user_id' => null,
        'email' => null,
        'location' => 'Winton Woods Park',
        'map' => "http://maps.google.com/maps?f=d&source=s_d&saddr=39.256366,-84.501139&daddr=&hl=en&geocode=&mra=mi&mrsp=0&sz=17&sll=39.257122,-84.500045&sspn=0.006505,0.010697&ie=UTF8&ll=39.256108,-84.502963&spn=0.006505,0.014784&t=h&z=17",
        'is_visible' => 1,
    ),
    array(
        'title' => "Women's Beginner Pickup",
        'day' => 'Sundays',
        'time' => '11:00am',
        'info' => "<p>Find us on Facebook: Cincinnati Women's Ultimate Frisbee Sundays</p>",
        'user_id' => null,
        'email' => null,
        'location' => 'Salway Park (across from Spring Grove Cemetery)',
        'map' => "http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=Salway+Park,+Cincinnati,+OH+45232&aq=&sll=37.230328,-95.712891&sspn=30.077032,86.220703&ie=UTF8&hq=Salway+Park,+Cincinnati,+OH+45232&hnear=Salway+Park,+Cincinnati,+Ohio+45232&ll=39.163542,-84.524517&spn=0.027085,0.0842&z=14",
        'is_visible' => 1,
    ),
    array(
        'title' => "Competitive Open Pick-up",
        'day' => 'Mondays',
        'time' => '6:30pm - Dark',
        'info' => '<p>A pickup game for experienced and competitive level ultimate players.</p>',
        'user_id' => 253,
        'email' => null,
        'location' => 'Linwood Park',
        'map' => null,
        'is_visible' => 1,
    ),
    array(
        'title' => "Lunchtime Ultimate in Evendale",
        'day' => 'Thursdays',
        'time' => 'approx 11:45am - 12:45pm',
        'info' => '<p>Convienent to those working or living in Evendale, Springdale, Blue Ash, Wyoming, Sharonville and other nearby communities.  We will most likely try to use field #2 which is the one you can see from the road (<a href="http://www.evendaleohio.org/Pages/EvendaleOH_Recreation/facilities/mapofcomplex.pdf">Map</a>).  Anyone that wants to play ultimate are welcome, no experience needed.</p>',
        'user_id' => 107,
        'email' => null,
        'location' => 'Baxter Park',
        'map' => 'http://www.evendaleohio.org/Pages/EvendaleOH_Recreation/programs/soccer/soccerdirections',
        'is_visible' => 1,
    ),
    array(
        'title' => "Downtown Pickup",
        'day' => 'Wednesdays & Fridays',
        'time' => '12pm',
        'info' => '<p>Cincinnati downtown games are played at noon normally Wednesday and Friday.  Come join us along the beautiful Ohio River next to the Serpentine Wall at Yeatmans Cove Park.  All levels of play are welcome, games are informal.  On occasion we celebrate the good life with Friday 4PM happy hour Ultimate.</p>',
        'user_id' => 75,
        'email' => null,
        'location' => 'Yeatmans Cove Park (next to the Serpentine Wall)',
        'map' => null,
        'is_visible' => 1,
    ),
    array(
        'title' => "Otto Armleder Pickup",
        'day' => 'Thursdays',
        'time' => '6pm -  Dark',
        'info' => '<p>Every Thursday until the Fall time change.  Co-ed of all skill levels, no experience necessary</p>',
        'user_id' => 1168,
        'email' => null,
        'location' => 'Otto Armleder Park',
        'map' => 'http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=5080+Wooster+Pike,+Cincinnati,+OH&aq=0&sll=37.0625,-95.677068&sspn=47.885545,78.662109&ie=UTF8&hq=&hnear=5080+Wooster+Pike,+Cincinnati,+Ohio+45226&z=16',
        'is_visible' => 1,
    ),
);

$totalPickups = count($pickups);

if(DEBUG) {
    echo "    Importing `Page` data:\n";
} else {
    echo "    Importing $totalPickups Pickups:\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 100, $totalPickups);    
}

$i = 0;
foreach($pickups as $pickup) {
    if(DEBUG) {
        echo "        Importing pickup item `{$pickup['title']}`...";
    } else {
        $progressBar->update($i);
    }

    $pickupObject = $pickupTable->createRow();
    $pickupObject->title = $pickup['title'];
    $pickupObject->day = $pickup['day'];
    $pickupObject->time = $pickup['time'];
    $pickupObject->info = $pickup['info'];
    $pickupObject->user_id = $pickup['user_id'];
    $pickupObject->email = $pickup['email'];
    $pickupObject->location = $pickup['location'];
    $pickupObject->map = $pickup['map'];
    $pickupObject->weight = $pickupTable->fetchHighestWeight();
    $pickupObject->is_visible = $pickup['is_visible'];
    $pickupObject->save();

    if(DEBUG) {
        echo "Done\n";
    }

    $i++;
}

if(DEBUG) {
    echo "    Done\n";
} else {
    $progressBar->update($totalPickups);
    echo "\n";        
}
