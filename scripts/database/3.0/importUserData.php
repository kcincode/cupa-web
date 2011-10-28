<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$userTable = new Cupa_Model_DbTable_User();
$userRoleTable = new Cupa_Model_DbTable_UserRole();
$userProfileTable = new Cupa_Model_DbTable_UserProfile();
$userLevelTable = new Cupa_Model_DbTable_UserLevel();

echo "    Importing `User` data:\n";

// get all the user failures
$failures = array();
$stmt = $origDb->prepare('SELECT COUNT(*) AS failures, username FROM login_failed GROUP BY username');
$stmt->execute();
foreach($stmt->fetchAll() as $row) {
    $failures[$row['username']] = $row['failures'];
}

// get all the user stats
$stats = array();
$stmt = $origDb->prepare('SELECT user_id, data FROM user_stats ORDER BY date DESC');
$stmt->execute();
foreach($stmt->fetchAll() as $row) {
    if(empty($stats[$row['user_id']])) {
        $stats[$row['user_id']] = Zend_Json::decode($row['data']);
    }
}

$level = array();
foreach($userLevelTable->fetchAllByWeight() as $row) {
    $level[$row['name']] = $row['id'];
}

$userTable->getAdapter()->beginTransaction();
$stmt = $origDb->prepare('SELECT u.*, us.data FROM users u LEFT JOIN user_stats us ON us.user_id = u.id');
//$stmt = $origDb->prepare('SELECT * FROM users');
$stmt->execute();

$email = '';
foreach($stmt->fetchAll() as $row) {
    if($email == $row['email']) {
        continue;
    }
    
    $email = $row['email'];
    
    // capitalize the first letter
    $row['name'] = ucfirst(strtolower($row['name']));
    $row['surname'] = ucfirst(strtolower($row['surname']));
    
    echo "        Importing user `{$row['name']} {$row['surname']}`...";
    $user = $userTable->createRow();
    $user->id = $row['id'];
    $user->parent = null;
    $user->username = strtolower($row['username']);
    $user->salt = null;
    $user->password = $row['password'];
    $user->email = $row['email'];
    $user->first_name = $row['name'];
    $user->last_name = $row['surname'];
    $user->activation_code = $userTable->generateUniqueCodeFor('activation_code');
    $user->requested_at = $row['created'];

    if($row['active']) {
       $user->activated_at = $row['created'];
    } else {
        $user->activated_at = null;
    }
    
    $user->expires_at = date('Y-m-d H:i:s', strtotime($row['created']) + 604800);
    $user->updated_at = $row['modified'];
    $user->last_login = $row['last'];
    if(isset($failures[$row['username']])) {
        $user->login_errors = $failures[$row['username']];
    } else {
        $user->login_errors = 0;
    }
    $user->is_active = $row['active'];
    $user->save();
    
    $userProfile = $userProfileTable->createRow();
    $userProfile->user_id = $user->id;
    $userProfile->gender = (empty($row['gender'])) ? null : $row['gender'];
    $userProfile->birthday = (empty($row['birthday'])) ? null : $row['birthday'];
    $userProfile->phone = (empty($row['phone'])) ? null : $row['phone'];
    
    if(isset($stats[$user->id])) {
        $data = $stats[$user->id];
        $userProfile->nickname = (empty($data['stats_nickname'])) ? null : $data['stats_nickname'];
        $userProfile->height = (empty($data['stats_height'])) ? null : $data['stats_height'];
        $userProfile->level = (!is_numeric($level[$data['stats_level']])) ? null : $level[$data['stats_level']];
        $userProfile->experience = (empty($data['stats_experience'])) ? null : $data['stats_experience'];
    }
    $userProfile->save();

    
    if($row['is_admin']) {
        if($row['id'] == 1) {
            $userRole = $userRoleTable->createRow();
            $userRole->user_id = $user->id;
            $userRole->role = 'admin';
            $userRole->save();
        } else {
            $userRole = $userRoleTable->createRow();
            $userRole->user_id = $user->id;
            $userRole->role = 'editor';
            $userRole->save();
            
            $userRole = $userRoleTable->createRow();
            $userRole->user_id = $user->id;
            $userRole->role = 'reporter';
            $userRole->save();
        }
    }
    
    echo "Done.\n";
}

$userTable->getAdapter()->commit();
echo "    Finished Importing Users.\n";
