<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$pickupTable = new Cupa_Model_DbTable_Pickup();
$userTable = new Cupa_Model_DbTable_User();

echo "    Importing `Pickup` data:\n";

$pickups = array(
    array(
        'title' => 'Winton Woods',
        'day' => 'Sundays',
        'time' => '12:30pm - ??',
        'info' => null,
        'user_id' => 1,
        'email' => null,
        'where' => 'Winton Woods Park',
        'map' => "http://maps.google.com/maps?f=d&source=s_d&saddr=39.256366,-84.501139&daddr=&hl=en&geocode=&mra=mi&mrsp=0&sz=17&sll=39.257122,-84.500045&sspn=0.006505,0.010697&ie=UTF8&ll=39.256108,-84.502963&spn=0.006505,0.014784&t=h&z=17",
        'is_visible' => 1,
    ),
    array(
        'title' => "Women's Beginner Pickup",
        'day' => 'Sundays',
        'time' => '11:00am',
        'info' => "<p>Find us on Facebook: Cincinnati Women's Ultimate Frisbee Sundays</p>",
        'user_id' => 1,
        'email' => null,
        'where' => 'Salway Park (across from Spring Grove Cemetery)',
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
        'where' => 'Linwood Park',
        'map' => null,
        'is_visible' => 1,
    ),
);

foreach($pickups as $pickup) {
    echo "        Importing pickup item `{$pickup['title']}`...";
    $pickupObject = $pickupTable->createRow();
    $pickupObject->title = $pickup['title'];
    $pickupObject->day = $pickup['day'];
    $pickupObject->time = $pickup['time'];
    $pickupObject->info = $pickup['info'];
    $pickupObject->user_id = $pickup['user_id'];
    $pickupObject->email = $pickup['email'];
    $pickupObject->is_visible = $pickup['is_visible'];
    $pickupObject->save();
    echo "Done\n";
}


echo "    Done\n";
