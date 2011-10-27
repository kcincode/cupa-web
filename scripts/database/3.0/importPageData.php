<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$pageTable = new Cupa_Model_DbTable_Page();

echo "    Importing `Page` data:\n";

$stmt = $origDb->prepare('SELECT * FROM pages');
$stmt->execute();
foreach($stmt->fetchAll() as $row) {
    echo "        Importing page `{$row['name']}`...";
    $page = $pageTable->createRow();
    $page->parent = ($row['parent'] == 0) ? null : $row['parent'];
    $page->name = $row['name'];
    $page->title = $row['title'];
    $page->content = $row['content'];
    $page->url = (empty($row['url'])) ? null : $row['url'];
    $page->target = (empty($row['target'])) ? '_self' : $row['target'];
    $page->weight = 0;
    $page->visible = $row['active'];
    $page->created_at = date('Y-m-d H:i:s');
    $page->updated_at = date('Y-m-d H:i:s');
    $page->save();
    echo "Done\n";
}

echo "    Done\n";
