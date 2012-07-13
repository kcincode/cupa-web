<?php
echo "Starting Import:\n";
try {
    $begin = microtime(true);
    include('updateDbSchema.php');
    include('importClubMembers.php');
    $end = microtime(true);
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "Finished Successfully (" . number_format(($end - $begin), 2) . " seconds).\n";

