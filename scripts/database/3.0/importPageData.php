<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$pageTable = new Cupa_Model_DbTable_Page();

$stmt = $origDb->prepare('SELECT * FROM pages');
$stmt->execute();
$results = $stmt->fetchAll();
$totalPages = count($results);
$i = 0;

if(DEBUG) {
    echo "    Importing `Page` data:\n";
} else {
    echo "    Importing $totalPages Pages:\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 100, $totalPages);    
}

foreach($results as $row) {
    if(DEBUG) {
        echo "        Importing page `{$row['name']}`...";
    } else {
        $progressBar->update($i);
    }
    $page = $pageTable->createRow();
    $page->parent = ($row['parent'] == 0) ? null : $row['parent'];
    $page->name = $row['name'];
    $page->title = $row['title'];
    $page->content = $row['content'];
    $page->url = (empty($row['url'])) ? null : $row['url'];
    $page->target = (empty($row['target'])) ? '_self' : $row['target'];
    $page->weight = 0;
    $page->is_visible = $row['active'];
    $page->created_at = date('Y-m-d H:i:s');
    $page->created_by = 1;
    $page->updated_at = date('Y-m-d H:i:s');
    $page->last_updated_by = 1;
    $page->save();
    if(DEBUG) {
       echo "Done\n";
    }

    $i++;
}

if(DEBUG) {
    echo "    Done\n";
} else {
    $progressBar->update($totalPages);
    echo "\n";
}
