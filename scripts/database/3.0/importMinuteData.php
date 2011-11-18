<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$minuteTable = new Cupa_Model_DbTable_Minute();

$data = array(
    array(
        'when' => '2009-01-20 19:00:00',
        'location' => 'Unknown',
        'pdf' => 'minutes/2009-01-20.pdf',
        'is_visible' => 1,
    ),
    array(
        'when' => '2009-04-29 19:00:00',
        'location' => 'Unknown',
        'pdf' => 'minutes/2009-04-29.pdf',
        'is_visible' => 1,
    ),
    array(
        'when' => '2009-08-11 19:00:00',
        'location' => 'Unknown',
        'pdf' => 'minutes/2009-08-11.pdf',
        'is_visible' => 1,
    ),
    array(
        'when' => '2010-05-11 19:00:00',
        'location' => 'Madera Library',
        'pdf' => 'minutes/2010-05-11.pdf',
        'is_visible' => 1,
    ),
    array(
        'when' => '2011-02-08 19:00:00',
        'location' => 'Moeller Library',
        'pdf' => 'minutes/2011-02-08.pdf',
        'is_visible' => 1,
    ),
    array(
        'when' => '2011-03-12 12:00:00',
        'location' => "Peter's House",
        'pdf' => 'minutes/2011-03-12.pdf',
        'is_visible' => 1,
    ),
);

echo "    Importing `Minutes` data:\n";

foreach($data as $row) {
    $filesize = filesize(__DIR__ . '/' . $row['pdf']);
    $fp = fopen(__DIR__ . '/' . $row['pdf'], 'r');
    echo "        Importing minutes `{$row['when']} {$row['location']}`...";
    $minute = $minuteTable->createRow();
    $minute->when = $row['when'];
    $minute->location = $row['location'];
    $minute->pdf = addslashes(fread($fp, $filesize));
    $minute->is_visible = $row['is_visible'];
    $minute->save();
    fclose($fp);
    echo "Done\n";
}

echo "    Done\n";
