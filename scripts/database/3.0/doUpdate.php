<?php

try {
    echo "Importing Data:\n";
    include('createDbSchema.php');
    include('importUserData.php');
    include('importPageData.php');
    include('importNewsData.php');
    include('importClubData.php');
    include('importOfficerData.php');
    include('importMinuteData.php');
    echo "Finished\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Finished with errors\n";
}

