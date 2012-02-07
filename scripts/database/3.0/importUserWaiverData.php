<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$userWaiverTable = new Cupa_Model_DbTable_UserWaiver();

if(DEBUG) {
	echo "    Importing `UserWaivers` data:\n";
}

$stmt = $origDb->prepare('SELECT * FROM user_waivers');
$stmt->execute();

$i = 0;
$results = $stmt->fetchAll();
$totalWaivers = count($results);

if(!DEBUG) {
	echo "    Importing $totalWaivers User Waivers\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 100, $totalWaivers);
}

foreach($results as $row) {
	if(DEBUG) {
	    echo "        Importing user waivers for #{$row['user_id']}...";
	} else {
		$progressBar->update($i);
	}
    $userWaiver = $userWaiverTable->createRow();
    $userWaiver->user_id = $row['user_id'];
    $userWaiver->year = $row['year'];
    $userWaiver->modified_at = date('Y-m-d H:i:s');
    $userWaiver->modified_by = null;
    $userWaiver->save();
    if(DEBUG) {
    	echo "Done\n";
	}
	$i++;
}

if(DEBUG) {
	echo "    Done\n";
} else {
	$progressBar->update($totalWaivers);
	echo "\n";
}
