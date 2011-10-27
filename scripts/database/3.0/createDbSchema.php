<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

echo "Creating Database Schema:\n";

// Database table links
$userTable = new Cupa_Model_DbTable_User();
$userLevelTable = new Cupa_Model_DbTable_UserLevel();
$db = $userTable->getAdapter();

try {
    echo "    Dropping all tables..."; 
    $db->query("DROP TABLE IF EXISTS `user_password_reset`");
    $db->query("DROP TABLE IF EXISTS `user_role`");
    $db->query("DROP TABLE IF EXISTS `user_level`");
    $db->query("DROP TABLE IF EXISTS `user_profile`");
    $db->query("DROP TABLE IF EXISTS `user`");
    $db->query("DROP TABLE IF EXISTS `page`");
    echo "Done\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    endWithError();
}

/*******************************************************************************
 * 
 * USER TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `User` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `user` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `parent` int(11) DEFAULT NULL,
          `username` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `salt` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
          `password` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `first_name` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `last_name` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `activation_code` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `requested_at` datetime DEFAULT NULL,
          `activated_at` datetime DEFAULT NULL,
          `expires_at` datetime DEFAULT NULL,
          `updated_at` datetime DEFAULT NULL,
          `last_login` datetime DEFAULT NULL,
          `login_errors` int(11) NOT NULL,
          `is_active` tinyint(1) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `username` (`username`),
          UNIQUE KEY `email` (`email`),
          UNIQUE KEY `activation_code` (`activation_code`),
          KEY `parent` (`parent`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    
    $db->query("
        ALTER TABLE `user`
          ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * USER_LEVEL TABLE
 * 
 *******************************************************************************/
$levels = array(
    0 => array(
        'name' => 'New',
        'weight' => 0,
    ),
    1 => array(
        'name' => 'Pickup',
        'weight' => 1,
    ),
    2 => array(
        'name' => 'Leagues',
        'weight' => 2,
    ),
    3 => array(
        'name' => 'College',
        'weight' => 3,
    ),
    4 => array(
        'name' => 'Club',
        'weight' => 4,
    ),
    5 => array(
        'name' => 'College Regionals',
        'weight' => 5,
    ),
    6 => array(
        'name' => 'Club Regionals',
        'weight' => 6,
    ),
    7 => array(
        'name' => 'College Nationals',
        'weight' => 7,
    ),
    8 => array(
        'name' => 'Club Nationals',
        'weight' => 8,
    ),
    9 => array(
        'name' => 'Worlds',
        'weight' => 9,
    ),
);

try {
    echo "    Creating `UserLevel` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `user_level` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
          `weight` int(11) NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    
    foreach($levels as $level) {
        $userLevelTable->insert($level);
    }
    
    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * USER_PROFILE TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `UserProfile` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `user_profile` (
          `user_id` int(11) NOT NULL,
          `gender` enum('Male','Female') COLLATE utf8_unicode_ci DEFAULT NULL,
          `birthday` date DEFAULT NULL,
          `phone` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
          `nickname` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
          `height` int(11) DEFAULT NULL,
          `level` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
          `experience` int(11) DEFAULT NULL,
          PRIMARY KEY (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    
    $db->query("
        ALTER TABLE `user_profile`
          ADD CONSTRAINT `user_profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * Page TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `Page` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `page` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `parent` int(11) DEFAULT NULL,
          `name` varchar(255) NOT NULL,
          `title` varchar(255) NOT NULL,
          `content` text NOT NULL,
          `url` varchar(255) DEFAULT NULL,
          `target` enum('_self','_blank','_top','_parent') NOT NULL DEFAULT '_self',
          `weight` int(11) NOT NULL DEFAULT '0',
          `visible` tinyint(1) NOT NULL DEFAULT '0',
          `created_at` datetime NOT NULL,
          `updated_at` datetime NOT NULL,
          PRIMARY KEY (`id`),
          KEY `parent` (`parent`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    
    $db->query("
        ALTER TABLE `page`
          ADD CONSTRAINT `page_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * USER_ROLE TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `UserRole` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `user_role` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `role` enum('admin','editor','reporter') COLLATE utf8_unicode_ci NOT NULL,
          `page_id` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`),
          KEY `page_id` (`page_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    
    $db->query("
        ALTER TABLE `user_role`
          ADD CONSTRAINT `user_role_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          ADD CONSTRAINT `user_role_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * USER_PASSWORD_RESET TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `UserPasswordReset` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `user_password_reset` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `code` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
          `user_id` int(11) NOT NULL,
          `requested_at` datetime NOT NULL,
          `expires_at` datetime NOT NULL,
          `completed_at` datetime NOT NULL,
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    
    $db->query("
        ALTER TABLE `user_password_reset`
          ADD CONSTRAINT `user_password_reset_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

echo "Finished\n\n";


function endWithError()
{
    echo "Finished with Errors.\n\n";
    exit();
}