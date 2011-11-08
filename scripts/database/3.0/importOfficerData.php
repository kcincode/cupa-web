<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$officerTable = new Cupa_Model_DbTable_Officer();

echo "    Importing `Officer` data:\n";

$stmt = $origDb->prepare('SELECT * FROM officers');
$stmt->execute();
foreach($stmt->fetchAll() as $row) {
    echo "        Importing officer `{$row['position']} #{$row['user_id']}`...";
    
    $officer = $officerTable->createRow();
    $officer->user_id = $row['user_id'];
    $officer->position = $row['position'];
    $officer->since = date('Y-m-d', strtotime('2010-05-14 00:00:00'));
    $officer->to = null;
    $officer->weight = $row['order'];
    $officer->save();
    
    echo "Done\n";
}

echo "    Done\n";
