<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

echo "Creating Database Schema:\n";

// Database table links
$userTable = new Cupa_Model_DbTable_User();
$userLevelTable = new Cupa_Model_DbTable_UserLevel();
$newsCategoryTable = new Cupa_Model_DbTable_NewsCategory();
$contactTable = new Cupa_Model_DbTable_Contact();

$db = $userTable->getAdapter();

try {
    echo "    Dropping all tables:\n"; 
    echo "        UserPasswordReset\n";
    $db->query("DROP TABLE IF EXISTS `user_password_reset`");
    echo "        UserRole\n";
    $db->query("DROP TABLE IF EXISTS `user_role`");
    echo "        UserLevel\n";
    $db->query("DROP TABLE IF EXISTS `user_level`");
    echo "        UserProfile\n";
    $db->query("DROP TABLE IF EXISTS `user_profile`");
    echo "        Page\n";
    $db->query("DROP TABLE IF EXISTS `page`");
    echo "        News\n";
    $db->query("DROP TABLE IF EXISTS `news`");
    echo "        NewsCategory\n";
    $db->query("DROP TABLE IF EXISTS `news_category`");
    echo "        ClubCaptain\n";
    $db->query("DROP TABLE IF EXISTS `club_captain`");
    echo "        Club\n";
    $db->query("DROP TABLE IF EXISTS `club`");
    echo "        Officer\n";
    $db->query("DROP TABLE IF EXISTS `officer`");
    echo "        Pickup\n";
    $db->query("DROP TABLE IF EXISTS `pickup`");
    echo "        LeagueQuestionList\n";
    $db->query("DROP TABLE IF EXISTS `league_question_list`");
    echo "        LeagueAnswer\n";
    $db->query("DROP TABLE IF EXISTS `league_answer`");
    echo "        LeagueQuestion\n";
    $db->query("DROP TABLE IF EXISTS `league_question`");
    echo "        LeagueGameData\n";
    $db->query("DROP TABLE IF EXISTS `league_game_data`");
    echo "        LeagueGame\n";
    $db->query("DROP TABLE IF EXISTS `league_game`");
    echo "        LeagueMember\n";
    $db->query("DROP TABLE IF EXISTS `league_member`");
    echo "        LeagueTeam\n";
    $db->query("DROP TABLE IF EXISTS `league_team`");
    echo "        LeagueInformation\n";
    $db->query("DROP TABLE IF EXISTS `league_information`");
    echo "        LeagueLimit\n";
    $db->query("DROP TABLE IF EXISTS `league_limit`");
    echo "        LeagueLocation\n";
    $db->query("DROP TABLE IF EXISTS `league_location`");
    echo "        League\n";
    $db->query("DROP TABLE IF EXISTS `league`");
    echo "        User\n";
    $db->query("DROP TABLE IF EXISTS `user`");
    echo "        Contact\n";
    $db->query("DROP TABLE IF EXISTS `contact`");
    echo "        Minute\n";
    $db->query("DROP TABLE IF EXISTS `minute`");
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
          `username` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
          `salt` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
          `password` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
          `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
          `first_name` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `last_name` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `activation_code` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
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
 * CONTACT TABLE
 * 
 *******************************************************************************/
$contacts = array(
    array(
        'name' => 'CUPA Information',
        'email' => 'cincinnatiultimate@gmail.com',
    ),
    array(
        'name' => 'Website Issues/Questions',
        'email' => 'webmaster@cincyultimate.org',
    ),
);


try {
    echo "    Creating `Contact` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `contact` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `email` varchar(255) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `name` (`name`),
          UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    
    foreach($contacts as $contact) {
        $contactTable->insert($contact);
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
          `is_visible` tinyint(1) NOT NULL DEFAULT '0',
          `created_at` datetime NOT NULL,
          `created_by` int(11) DEFAULT NULL,
          `updated_at` datetime NOT NULL,
          `last_updated_by` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `parent` (`parent`),
          KEY `created_by` (`created_by`),
          KEY `last_updated_by` (`last_updated_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $db->query("
        ALTER TABLE `page`
          ADD CONSTRAINT `page_ibfk_3` FOREIGN KEY (`last_updated_by`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
          ADD CONSTRAINT `page_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `page` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          ADD CONSTRAINT `page_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;");

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
          `completed_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `code` (`code`),
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

/*******************************************************************************
 * 
 * NEWS_CATEGORY TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `NewsCategory` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `news_category` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    
    $db->commit();
    
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * NEWS TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `News` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `news` (
          `id` bigint(20) NOT NULL AUTO_INCREMENT,
          `category_id` int(11) DEFAULT NULL,
          `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
          `info` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `content` text COLLATE utf8_unicode_ci NOT NULL,
          `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `type` enum('internal','external','news','text') NOT NULL,
          `is_visible` tinyint(1) NOT NULL DEFAULT '1',
          `posted_at` datetime NOT NULL,
          `posted_by` int(11) DEFAULT NULL,
          `edited_at` datetime NOT NULL,
          `last_edited_by` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `slug` (`slug`),
          KEY `last_edited_by` (`last_edited_by`),
          KEY `posted_by` (`posted_by`),
          KEY `category_id` (`category_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    
    $db->query("
        ALTER TABLE `news`
          ADD CONSTRAINT `news_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `news_category` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
          ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
          ADD CONSTRAINT `news_ibfk_2` FOREIGN KEY (`last_edited_by`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * CLUB TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `Club` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `club` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `type` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `facebook` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
          `twitter` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
          `begin` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `end` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
          `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
          `website` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
          `content` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `updated_at` datetime NOT NULL,
          `last_updated_by` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `last_updated_by` (`last_updated_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    
    $db->query("
        ALTER TABLE `club`
          ADD CONSTRAINT `club_ibfk_1` FOREIGN KEY (`last_updated_by`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * CLUB_CAPTAIN TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `ClubCaptain` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `club_captain` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `club_id` int(11) NOT NULL,
          `user_id` int(11) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`),
          KEY `club_id` (`club_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    
    $db->query("
        ALTER TABLE `club_captain`
          ADD CONSTRAINT `club_captain_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          ADD CONSTRAINT `club_captain_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `club` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * OFFICER TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `Officer` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `officer` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) DEFAULT NULL,
          `position` varchar(100) NOT NULL,
          `since` date NOT NULL,
          `to` date DEFAULT NULL,
          `weight` int(11) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    
    $db->query("
        ALTER TABLE `officer`
          ADD CONSTRAINT `officer_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * MINUTES TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `Minutes` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `minute` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `when` datetime NOT NULL,
          `location` text NOT NULL,
          `pdf` longblob,
          `is_visible` tinyint(1) NOT NULL DEFAULT '1',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}


/*******************************************************************************
 * 
 * PICKUP TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `Pickup` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `pickup` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
          `day` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
          `time` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
          `info` text COLLATE utf8_unicode_ci DEFAULT NULL,
          `user_id` int(11) DEFAULT NULL,
          `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `location` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
          `map` text COLLATE utf8_unicode_ci DEFAULT NULL,
          `weight` int(11) NOT NULL DEFAULT 0,
          `is_visible` tinyint(1) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
    
    $db->query("
        ALTER TABLE `pickup`
          ADD CONSTRAINT `pickup_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}
 

/*******************************************************************************
 * 
 * LEAGUES TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `League` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `league` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `year` int(11) NOT NULL,
          `season` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
          `day` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') COLLATE utf8_unicode_ci NOT NULL,
          `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
          `info` text COLLATE utf8_unicode_ci NOT NULL,
          `registration_begin` datetime NOT NULL,
          `registration_end` datetime NOT NULL,
          `visible_from` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
    
    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * LEAGUE_TEAM TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `LeagueTeam` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `league_team` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `league_id` int(11) NOT NULL,
          `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
          `color` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
          `color_code` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
          `text_code` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
          `final_rank` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `league_id` (`league_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

    $db->query("
        ALTER TABLE `league_team`
          ADD CONSTRAINT `league_team_ibfk_1` FOREIGN KEY (`league_id`) REFERENCES `league` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * LEAGUE_GAME TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `LeagueGame` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `league_game` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `league_id` int(11) NOT NULL,
          `day` datetime NOT NULL,
          `week` int(11) NOT NULL,
          `field` int(11) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `league_id` (`league_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

    $db->query("
        ALTER TABLE `league_game`
          ADD CONSTRAINT `league_game_ibfk_1` FOREIGN KEY (`league_id`) REFERENCES `league` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * LEAGUE_GAME_DATA TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `LeagueGameData` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `league_game_data` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `league_game_id` int(11) NOT NULL,
          `type` enum('home','away') COLLATE utf8_unicode_ci NOT NULL,
          `league_team_id` int(11) DEFAULT NULL,
          `score` int(11) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `league_game_id` (`league_game_id`),
          KEY `league_team_id` (`league_team_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

    $db->query("
        ALTER TABLE `league_game_data`
          ADD CONSTRAINT `league_game_data_ibfk_2` FOREIGN KEY (`league_team_id`) REFERENCES `league_team` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
          ADD CONSTRAINT `league_game_data_ibfk_1` FOREIGN KEY (`league_game_id`) REFERENCES `league_game` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * LEAGUE_INFORMATION TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `LeagueInformation` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `league_information` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `league_id` int(11) NOT NULL,
          `is_youth` tinyint(1) NOT NULL,
          `user_teams` tinyint(1) NOT NULL,
          `is_pods` tinyint(1) NOT NULL,
          `contact_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `cost` int(11) DEFAULT 0,
          `paypal_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
          `description` text COLLATE utf8_unicode_ci,
          PRIMARY KEY (`id`),
          KEY `league_id` (`league_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

    $db->query("
        ALTER TABLE `league_information`
          ADD CONSTRAINT `league_information_ibfk_1` FOREIGN KEY (`league_id`) REFERENCES `league` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * LEAGUE_LIMIT TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `LeagueLimit` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `league_limit` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `league_id` int(11) NOT NULL,
          `male_players` int(11) DEFAULT NULL,
          `female_players` int(11) DEFAULT NULL,
          `total_players` int(11) DEFAULT NULL,
          `teams` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `league_id` (`league_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

    $db->query("
        ALTER TABLE `league_limit`
          ADD CONSTRAINT `league_limit_ibfk_1` FOREIGN KEY (`league_id`) REFERENCES `league` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * LEAGUE_MEMBER TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `LeagueMember` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `league_member` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `league_id` int(11) NOT NULL,
          `user_id` int(11) NOT NULL,
          `position` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          `league_team_id` int(11) DEFAULT NULL,
          `paid` tinyint(1) NOT NULL DEFAULT '0',
          `release` tinyint(1) NOT NULL DEFAULT '0',
          `created_at` datetime NOT NULL,
          `modified_at` datetime NOT NULL,
          PRIMARY KEY (`id`),
          KEY `league_id` (`league_id`),
          KEY `user_id` (`user_id`),
          KEY `legaue_team_id` (`league_team_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

    $db->query("
        ALTER TABLE `league_member`
          ADD CONSTRAINT `league_member_ibfk_3` FOREIGN KEY (`league_team_id`) REFERENCES `league_team` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          ADD CONSTRAINT `league_member_ibfk_1` FOREIGN KEY (`league_id`) REFERENCES `league` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          ADD CONSTRAINT `league_member_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}

/*******************************************************************************
 * 
 * LEAGUE_LOCATION TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `LeagueLocation` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `league_location` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `league_id` int(11) NOT NULL,
          `type` enum('draft','tournament','league') COLLATE utf8_unicode_ci NOT NULL,
          `location` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
          `map_link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `photo_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `address_street` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `address_city` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
          `address_state` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
          `address_zip` int(11) NOT NULL,
          `start` datetime NOT NULL,
          `end` datetime NOT NULL,
          PRIMARY KEY (`id`),
          KEY `league_id` (`league_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

    $db->query("
        ALTER TABLE `league_location`
          ADD CONSTRAINT `league_location_ibfk_1` FOREIGN KEY (`league_id`) REFERENCES `league` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}


/*******************************************************************************
 * 
 * LEAGUE_QUESTION TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `LeagueQuestion` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `league_question` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
          `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
          `type` enum('multiple','text','boolean','textarea') COLLATE utf8_unicode_ci NOT NULL,
          `answers` text COLLATE utf8_unicode_ci NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}


/*******************************************************************************
 * 
 * LEAGUE_QUESTION_LIST TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `LeagueQuestionList` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `league_question_list` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `league_id` int(11) NOT NULL,
          `league_question_id` int(11) NOT NULL,
          `required` tinyint(1) NOT NULL,
          `weight` int(11) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `league_id` (`league_id`),
          KEY `league_question_id` (`league_question_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
    
    $db->query("
        ALTER TABLE `league_question_list`
          ADD CONSTRAINT `league_question_list_ibfk_2` FOREIGN KEY (`league_question_id`) REFERENCES `league_question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          ADD CONSTRAINT `league_question_list_ibfk_1` FOREIGN KEY (`league_id`) REFERENCES `league` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

    $db->commit();
    echo "Done.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}


/*******************************************************************************
 * 
 * LEAGUE_ANSWER TABLE
 * 
 *******************************************************************************/
try {
    echo "    Creating `LeagueAnswer` Table..."; 
    $db->beginTransaction();

    $db->query("
        CREATE TABLE IF NOT EXISTS `league_answer` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `league_member_id` int(11) NOT NULL,
          `league_question_id` int(11) NOT NULL,
          `answer` text COLLATE utf8_unicode_ci NOT NULL,
          PRIMARY KEY (`id`),
          KEY `league_member_id` (`league_member_id`),
          KEY `league_question_id` (`league_question_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
    
    $db->query("
        ALTER TABLE `league_answer`
          ADD CONSTRAINT `league_answer_ibfk_2` FOREIGN KEY (`league_question_id`) REFERENCES `league_question` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
          ADD CONSTRAINT `league_answer_ibfk_1` FOREIGN KEY (`league_member_id`) REFERENCES `league_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");

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

