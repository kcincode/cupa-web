<?php
echo "Starting Import:\n";
try {
    $begin = microtime(true);
    include('createDbSchema.php');
    include('importUserData.php');
    include('importUserWaiverData.php');
    include('importPageData.php');
    include('importNewsData.php');
    include('importClubData.php');
    include('importOfficerData.php');
    include('importMinuteData.php');
    include('importPickupData.php');
    include('importLeagueData.php');
    include('importTournamentData.php');
    $end = microtime(true);
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "Finished Successfully (" . number_format(($end - $begin), 2) . " seconds).\n";

