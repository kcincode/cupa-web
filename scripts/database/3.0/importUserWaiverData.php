<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$userWaiverTable = new Cupa_Model_DbTable_UserWaiver();

echo "    Importing `UserWaivers` data:\n";

$stmt = $origDb->prepare('SELECT * FROM user_waivers');
$stmt->execute();
foreach($stmt->fetchAll() as $row) {
    echo "        Importing user waivers for #{$row['user_id']}...";
    $userWaiver = $userWaiverTable->createRow();
    $userWaiver->user_id = $row['user_id'];
    $userWaiver->year = $row['year'];
    $userWaiver->modified_at = date('Y-m-d H:i:s');
    $userWaiver->modified_by = null;
    $userWaiver->save();
    echo "Done\n";
}

echo "    Done\n";
