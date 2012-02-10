<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$minuteTable = new Cupa_Model_DbTable_Minute();

$data = array(
    array(
        'year' => '2010',
        'name' => 'waiver',
        'location' => 'forms/2010_waiver.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2010',
        'name' => 'youth_waiver',
        'location' => 'forms/2010_yuc_waiver.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2010',
        'name' => 'release',
        'location' => 'forms/2010_medical_authorization.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2011',
        'name' => 'waiver',
        'location' => 'forms/2011_waiver.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2011',
        'name' => 'youth_waiver',
        'location' => 'forms/2011_yuc_waiver.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2011',
        'name' => 'release',
        'location' => 'forms/2011_medical_authorization.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2012',
        'name' => 'waiver',
        'location' => 'forms/2012_waiver.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2012',
        'name' => 'release',
        'location' => 'forms/2012_medical_authorization.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2010',
        'name' => 'primary_outreach',
        'location' => 'forms/2010_primary_outreach_1.1.pdf',
        'type' => 'pdf',
    ),
    array(
        'year' => '2011',
        'name' => 'primary_outreach',
        'location' => 'forms/2011_primary_outreach_1.2.pdf',
        'type' => 'pdf',
    ),
);

$totalMinutes = count($data);

if(DEBUG) {
    echo "    Importing `Minutes` data:\n";
} else {
    echo "    Importing $totalMinutes Minutes:\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 100, $totalMinutes);    
}

$i = 0;
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

if(DEBUG) {
    echo "    Done\n";
} else {
    $progressBar->update($totalMinutes);
    echo "\n";
}
