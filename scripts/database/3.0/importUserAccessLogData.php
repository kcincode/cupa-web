<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$userAccessLogTable = new Model_DbTable_UserAccessLog();

$stmt = $origDb->prepare('SELECT * FROM login_failed');
$stmt->execute();
$results = $stmt->fetchAll();
$totalLogs = count($results);
$i = 0;

if(DEBUG) {
    echo "    Importing `UserAccessLog` data:\n";
} else {
    echo "    Importing $totalLogs Logs:\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 50, $totalLogs);
}

$userAccessLogTable->getAdapter()->beginTransaction();
foreach($results as $row) {
    if(DEBUG) {
        echo "        Importing log `{$row['time']}`...";
    } else {
        $progressBar->update($i);
    }

    $userAccessLogTable->insert(array(
        'user' => $row['username'],
        'time' => $row['time'],
        'client' => $row['client'],
        'session' => Zend_Session::getId(),
        'type' => 'login-failed',
        'comment' => $row['message'],
    ));

    if(DEBUG) {
       echo "Done\n";
    }

    $i++;
}
$userAccessLogTable->getAdapter()->commit();

if(DEBUG) {
    echo "    Done\n";
} else {
    $progressBar->update($totalLogs);
    echo "\n";
}
