<?php
echo "Starting Import:\n";
try {
    $begin = microtime(true);
    include('createDbSchema.php');
    echo "MEM USAGE: " . memory_get_usage() . "\n";
    include('importUserData.php');
    echo "MEM USAGE: " . memory_get_usage() . "\n";
    include('importUserWaiverData.php');
    echo "MEM USAGE: " . memory_get_usage() . "\n";
    include('importPageData.php');
    echo "MEM USAGE: " . memory_get_usage() . "\n";
    include('importNewsData.php');
    echo "MEM USAGE: " . memory_get_usage() . "\n";
    include('importClubData.php');
    echo "MEM USAGE: " . memory_get_usage() . "\n";
    include('importOfficerData.php');
    echo "MEM USAGE: " . memory_get_usage() . "\n";
    include('importMinuteData.php');
    echo "MEM USAGE: " . memory_get_usage() . "\n";
    include('importPickupData.php');
    echo "MEM USAGE: " . memory_get_usage() . "\n";
    include('importLeagueData.php');
    echo "MEM USAGE: " . memory_get_usage() . "\n";
    include('importTournamentData.php');
    echo "MEM USAGE: " . memory_get_usage() . "\n";
    include('importFormData.php');
    echo "MEM USAGE: " . memory_get_usage() . "\n";
    $end = microtime(true);
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "Finished Successfully (" . number_format(($end - $begin), 2) . " seconds).\n";

