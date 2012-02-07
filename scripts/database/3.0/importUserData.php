<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$userTable = new Cupa_Model_DbTable_User();
$userRoleTable = new Cupa_Model_DbTable_UserRole();
$userProfileTable = new Cupa_Model_DbTable_UserProfile();
$userLevelTable = new Cupa_Model_DbTable_UserLevel();

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
$stmt = $origDb->prepare('SELECT COUNT(id) as num_users FROM users');
$stmt->execute();
$numResults = $stmt->fetch();

$stmt = $origDb->prepare('SELECT u.*, us.data FROM users u LEFT JOIN user_stats us ON us.user_id = u.id');
$stmt->execute();

$email = '';
$results = $stmt->fetchAll();
$totalUsers = $numResults['num_users'];
$i = 0;

if(!DEBUG) {
    echo "    Importing $totalUsers Users:\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 100, $totalUsers);
} else {
    echo "    Importing `User` data:\n";
}

foreach($results as $row) {
    if(!DEBUG) {
        $progressBar->update($i);
    }

    if($email == $row['email']) {
        continue;
    }
    
    $email = $row['email'];
    
    // capitalize the first letter
    $row['name'] = ucfirst(strtolower($row['name']));
    $row['surname'] = ucfirst(strtolower($row['surname']));
    
    if(DEBUG) {
       echo "        Importing user `{$row['name']} {$row['surname']}`...";
    }

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
        if(is_numeric($data['stats_experience']) and $data['stats_experience'] < 1900) {
            $data['stats_experience'] = date('Y') - $data['stats_experience'];
        }
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
    
    if(DEBUG) {
        echo "Done.\n";
    }

    $i++;
}
if(!DEBUG) {
    $progressBar->update($totalUsers);
}

$stmt = $origDb->prepare('SELECT * FROM user_minors ORDER BY id');
$stmt->execute();

$results = $stmt->fetchAll();
$totalUsers = count($results);
$i = 0;

if(!DEBUG) {
    echo "\n    Importing $totalUsers Minors:\n";
    $progressBar->reset('    [%bar%] %percent%', '=>', '-', 100, $totalUsers);
    
}

foreach($results as $row) {
    if(!DEBUG) {
        $progressBar->update($i);
    }

    // capitalize the first letter
    $row['first_name'] = ucfirst(strtolower($row['first_name']));
    $row['last_name'] = ucfirst(strtolower($row['last_name']));
    
    if(DEBUG) {
        echo "        Importing minor user `{$row['first_name']} {$row['last_name']}`...";
    }
    $user = $userTable->createRow();
    $user->parent = $row['parent_id'];
    $user->username = null;
    $user->salt = null;
    $user->password = null;
    $user->email = null;
    $user->first_name = $row['first_name'];
    $user->last_name = $row['last_name'];
    $user->activation_code = null;
    $user->requested_at = date('Y-m-d H:i:s');
    $user->activated_at = null;
    $user->expires_at = null;
    $user->updated_at = null;
    $user->last_login = date('Y-m-d H:i:s');
    $user->login_errors = 0;
    $user->is_active = 1;
    $user->save();
    
    $userProfile = $userProfileTable->createRow();
    $userProfile->user_id = $user->id;
    $userProfile->gender = (empty($row['gender'])) ? null : $row['gender'];
    $userProfile->birthday = (empty($row['birthday'])) ? null : $row['birthday'];
    $userProfile->phone = null;    
    $userProfile->nickname = null; 
    $userProfile->height = null; 
    $userProfile->level = null; 
    $userProfile->experience = null;
    $userProfile->save();

    if(DEBUG) {
        echo "Done\n";
    }
    $i++;
}

$userTable->getAdapter()->commit();
if(DEBUG) {
    echo "    Finished Importing Users.\n";
} else {
    $progressBar->update($totalUsers);
    echo "\n";
}
