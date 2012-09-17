<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$userTable = new Model_DbTable_User();
$userLevelTable = new Model_DbTable_UserLevel();
$newsCategoryTable = new Model_DbTable_NewsCategory();
$contactTable = new Model_DbTable_Contact();
$tournamentDivisionTable = new Model_DbTable_TournamentDivision();

$db = $userTable->getAdapter();

$createTables = array('club_member', 'volunteer', 'volunteer_pool', 'volunteer_location', 'volunteer_member');

$dropTables = array_reverse($createTables);
$totalTables = count($createTables);

try {
    $userTable->getAdapter()->beginTransaction();
    if(DEBUG) {
        echo "    Dropping Database Tables:\n";
    } else {
        echo "    Dropping $totalTables Tables:\n";
        $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 50, $totalTables);
    }

    $i = 0;
    foreach($dropTables as $table) {
        if(DEBUG) {
          echo "        Dropping " . str_replace(' ', '', ucwords(str_replace('_', ' ', $table))) . "\n";
        } else {
          $progressBar->update($i);
        }

        $db->query("DROP TABLE IF EXISTS `$table`");
        $i++;
    }
    $userTable->getAdapter()->commit();

    if(DEBUG) {
        echo "    Done\n";
    } else {
        $progressBar->update($totalTables);
        echo "\n";
    }
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    endWithError();
}

try {

    if(!DEBUG) {
        echo "    Creating $totalTables Tables:\n";
        $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 50, $totalTables);
    }

    $db->beginTransaction();

    $i = 0;
    foreach($createTables as $table) {
        if(DEBUG) {
            $start = microtime();
            echo "    Creating `$table` Table...";
        }

        $func = 'create' . str_replace(' ', '', ucwords(str_replace('_', ' ', $table))) . 'Table';
        $func($db);

        if(DEBUG) {
            $end = microtime();
            $diff = $end - $start;
            echo "Done. ({$diff})\n";
        } else {
            $progressBar->update($i);
        }

        $i++;
    }

    if(!DEBUG) {
        $progressBar->update($totalTables);
        echo "\n";
    }

    $db->commit();

} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    $db->rollback();
    endWithError();
}


function createClubMemberTable($db)
{
    $db->query("CREATE TABLE `club_member` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `club_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL,
        `year` int(11) NOT NULL,
        `position` varchar(25) NOT NULL DEFAULT 'player',
        PRIMARY KEY (`id`),
        KEY `club_id` (`club_id`),
        KEY `user_id` (`user_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $db->query("ALTER TABLE `club_member`
        ADD CONSTRAINT `club_member_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `club_member_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `club` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
}

function createVolunteerTable($db)
{
    $db->query("CREATE TABLE IF NOT EXISTS `volunteer` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `start` datetime NOT NULL,
        `end` datetime NOT NULL,
        `contact_id` int(11) DEFAULT NULL,
        `max_volunteers` int(11) NOT NULL,
        `information` int(11) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `contact_id` (`contact_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $db->query("ALTER TABLE `volunteer`
        ADD CONSTRAINT `volunteer_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;");
}

function createVolunteerPoolTable($db)
{
    $db->query("CREATE TABLE IF NOT EXISTS `volunteer_pool` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `name` varchar(100) DEFAULT NULL,
        `email` varchar(255) DEFAULT NULL,
        `phone` varchar(12) DEFAULT NULL,
        `involvement` varchar(25) DEFAULT NULL,
        `primary_interest` text,
        `other` text DEFAULT NULL,
        `experience` text DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
}

function createVolunteerLocationTable($db)
{
    $db->query("CREATE TABLE IF NOT EXISTS `volunteer_location` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `volunteer_id` int(11) NOT NULL,
        `name` varchar(255) NOT NULL,
        `street` varchar(255) NOT NULL,
        `city` varchar(150) NOT NULL,
        `state` varchar(2) NOT NULL,
        `zip` varchar(5) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `volunteer_id` (`volunteer_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $db->query("ALTER TABLE `volunteer_location`
        ADD CONSTRAINT `volunteer_location_ibfk_1` FOREIGN KEY (`volunteer_id`) REFERENCES `volunteer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
}

function createVolunteerMemberTable($db)
{
    $db->query("CREATE TABLE IF NOT EXISTS `volunteer_member` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `volunteer_id` int(11) NOT NULL,
        `volunteer_pool_id` int(11) NOT NULL,
        `enrolled_at` datetime NOT NULL,
        `comment` text,
        PRIMARY KEY (`id`),
        KEY `volunteer_id` (`volunteer_id`),
        KEY `volunteer_pool_id` (`volunteer_pool_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

    $db->query("ALTER TABLE `volunteer_member`
        ADD CONSTRAINT `volunteer_member_ibfk_1` FOREIGN KEY (`volunteer_id`) REFERENCES `volunteer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `volunteer_member_ibfk_2` FOREIGN KEY (`volunteer_pool_id`) REFERENCES `volunteer_pool` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
}

function endWithError()
{
    echo "Finished with Errors.\n\n";
    exit();
}

