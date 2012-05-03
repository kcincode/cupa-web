<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$officerTable = new Model_DbTable_Officer();

$stmt = $origDb->prepare('SELECT * FROM officers');
$stmt->execute();
$results = $stmt->fetchAll();
$totalOfficers = count($results);

if(DEBUG) {
    echo "    Importing `Officer` data:\n";
} else {
    echo "    Importing $totalOfficers Officers:\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 50, $totalOfficers);
}

$i = 0;
$officerTable->getAdapter()->beginTransaction();
foreach($results as $row) {
	if(DEBUG) {
    	echo "        Importing officer `{$row['position']} #{$row['user_id']}`...";
	} else {
		$progressBar->update($i);
	}

    if(!empty($row['image_data'])) {
        // save the users image if set.
        $imageData = $row['image_data'];
        $fp = fopen(APPLICATION_PATH . '/../public/images/officers/' . $row['user_id'] . '.jpg', 'w');
        fwrite($fp, $imageData);
        fclose($fp);
    }

    $officer = $officerTable->createRow();
    $officer->user_id = $row['user_id'];
    $officer->position = $row['position'];
    $officer->since = date('Y-m-d', strtotime('2010-05-14 00:00:00'));
    $officer->to = null;
    $officer->weight = $row['order'];
    $officer->description = $row['description'];
    $officer->save();

    if(APPLICATION_ENV != 'production') {
        $userRoleTable = new Model_DbTable_UserRole();
        $userRoleTable->addRole($row['user_id'], 'admin');
    }

    if(DEBUG) {
    	echo "Done\n";
	}

	$i++;
}
$officerTable->getAdapter()->commit();

if(DEBUG) {
	echo "    Done\n";
} else{
	$progressBar->update($totalOfficers);
	echo "\n";
}
