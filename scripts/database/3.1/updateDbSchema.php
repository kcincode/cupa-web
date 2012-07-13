<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$userTable = new Model_DbTable_User();
$userLevelTable = new Model_DbTable_UserLevel();
$newsCategoryTable = new Model_DbTable_NewsCategory();
$contactTable = new Model_DbTable_Contact();
$tournamentDivisionTable = new Model_DbTable_TournamentDivision();

$db = $userTable->getAdapter();

$dropTables = array('club_member');

$createTables = array_reverse($dropTables);
$totalTables = count($dropTables);

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
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

    $db->query("ALTER TABLE `club_member`
        ADD CONSTRAINT `club_member_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        ADD CONSTRAINT `club_member_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `club` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
}

function endWithError()
{
    echo "Finished with Errors.\n\n";
    exit();
}

