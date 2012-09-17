<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$userTable = new Model_DbTable_User();
$leagueAnswerTable = new Model_DbTable_LeagueAnswer();
$volunteerPoolTable = new Model_DbTable_VolunteerPool();


$progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 50, 100);
$userTable->getAdapter()->beginTransaction();
$volunteers = $leagueAnswerTable->fetchAllVolunteers();
$volunteerCount = count($volunteers);

$i = 0;
echo "    Importing $volunteerCount Volunteers:\n";
$progressBar->reset('    [%bar%] %percent%', '=>', '-', 50, $volunteerCount);
foreach($volunteers as $volunteer) {
    $userProfileTable = new Model_DbTable_UserProfile();
    $profile = $userProfileTable->find($volunteer['user_id'])->current();

    $tmp = $volunteerPoolTable->addVolunteer(array(
        'user_id' => $volunteer['user_id'],
        'phone' => (isset($profile['phone'])) ? $profile['phone'] : null,
        'experience' => 'Unknown',
        'primary_interest' => 'Unknown',
        'involvement' => '0-1 year',
    ));

    $progressBar->update($i);
    $i++;
}

$userTable->getAdapter()->commit();
$progressBar->update($volunteerCount);
echo "\n";
