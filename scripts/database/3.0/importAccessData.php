<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$userAccessLogTable = new Model_DbTable_UserAccessLog();

echo "    Importing `UserAccessLog` data:\n";
$stmt = $origDb->prepare('SELECT * FROM login_failed');
$stmt->execute();

$userAccessLogTable->getAdapter()->beginTransaction();
foreach($stmt->fetchAll() as $row) {
    echo "Inserting failed login for user `{$row['username']}`\n";
    $userAccessLog = $userAccessLogTable->createRow();
    $userAccessLog->user = $row['username'];
    $userAccessLog->time = $row['time'];
    $userAccessLog->client = $row['client'];
    $userAccessLog->type = 'login-failed';
    $userAccessLog->comment = $row['message'];
    $userAccessLog->save();
}
$userAccessLogTable->getAdapter()->commit();

$stmt = $origDb->prepare('SELECT * FROM login_failed');
$stmt->execute();
$userAccessLogTable->getAdapter()->beginTransaction();
foreach($stmt->fetchAll() as $row) {
    echo "Inserting successful login for user `{$row['username']}`\n";
    $userAccessLog = $userAccessLogTable->createRow();
    $userAccessLog->user = $row['username'];
    $userAccessLog->time = $row['login'];
    $userAccessLog->client = 'Unknown';
    $userAccessLog->type = 'login-success';
    $userAccessLog->comment = null;
    $userAccessLog->save();

}
$userAccessLogTable->getAdapter()->commit();

echo "    Done\n";
