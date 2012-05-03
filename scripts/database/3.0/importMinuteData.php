<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$minuteTable = new Model_DbTable_Minute();

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
        'when' => '2010-11-16 19:00:00',
        'location' => 'Moeller HS Library',
        'pdf' => 'minutes/2010-11-16.pdf',
        'is_visible' => 1,
    ),
    array(
        'when' => '2011-02-08 19:00:00',
        'location' => 'Moeller HS Library',
        'pdf' => 'minutes/2011-02-08.pdf',
        'is_visible' => 1,
    ),
    array(
        'when' => '2011-03-12 12:00:00',
        'location' => "Peter's House",
        'pdf' => 'minutes/2011-03-12.pdf',
        'is_visible' => 1,
    ),
    array(
        'when' => '2011-08-22 19:00:00',
        'location' => "Moeller HS Library",
        'pdf' => 'minutes/2011-08-22.pdf',
        'is_visible' => 1,
    ),
    array(
        'when' => '2011-10-24 19:00:00',
        'location' => "Moeller HS Library",
        'pdf' => 'minutes/2011-10-24.pdf',
        'is_visible' => 1,
    ),
    array(
        'when' => '2012-01-09 19:00:00',
        'location' => "Moeller HS Library",
        'pdf' => 'minutes/2012-01-09.pdf',
        'is_visible' => 1,
    ),
);

$totalMinutes = count($data);

if(DEBUG) {
    echo "    Importing `Minutes` data:\n";
} else {
    echo "    Importing $totalMinutes Minutes:\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 50, $totalMinutes);
}

$i = 0;
$minuteTable->getAdapter()->beginTransaction();
foreach($data as $row) {
    $filesize = filesize(__DIR__ . '/' . $row['pdf']);
    $fp = fopen(__DIR__ . '/' . $row['pdf'], 'r');

    if(DEBUG) {
        echo "        Importing minutes `{$row['when']} {$row['location']}`...";
    } else {
        $progressBar->update($i);
    }

    $minute = $minuteTable->createRow();
    $minute->when = $row['when'];
    $minute->location = $row['location'];
    $minute->pdf = addslashes(fread($fp, $filesize));
    $minute->is_visible = $row['is_visible'];
    $minute->save();
    fclose($fp);

    if(DEBUG) {
        echo "Done\n";
    }

    $i++;
}
$minuteTable->getAdapter()->commit();

if(DEBUG) {
    echo "    Done\n";
} else {
    $progressBar->update($totalMinutes);
    echo "\n";
}
