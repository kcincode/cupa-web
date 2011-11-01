<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$newsTable = new Cupa_Model_DbTable_News();
$newsCategoryTable = new Cupa_Model_DbTable_NewsCategory();

echo "    Importing `News` data:\n";

$stmt = $origDb->prepare('SELECT * FROM news');
$stmt->execute();
foreach($stmt->fetchAll() as $row) {
    $categoryId = $newsCategoryTable->fetchCategoryIdFromName($row['type']);
    if(!is_numeric($categoryId)) {
        $categoryId = $newsCategoryTable->insert(array('name' => $row['type']));
    }
    
    echo "        Importing news item `{$row['title']}`...";
    $news = $newsTable->createRow();
    $news->category_id = $categoryId;
    $news->slug = $newsTable->slugifyTitle($row['title']);
    $news->title = $row['title'];
    $news->content = $row['content'];
    $news->url = (empty($row['url'])) ? null : $row['url'];
    $news->info = $row['blurb'];
    $news->is_visible = $row['visible'];
    $news->posted_at = date('Y-m-d H:i:s');
    $news->posted_by = 1;
    $news->type = getNewsType($row);
    $news->edited_at = date('Y-m-d H:i:s');
    $news->last_edited_by = 1;
    $news->save();
    echo "Done\n";
}

echo "    Done\n";

function getNewsType($news)
{
    if(!empty($news['content'])) {
        return 'news';
    } else if(strstr($news['url'], 'http') === true) {
        return 'external';
    } else if(strstr($news['url'], 'http') === false and !empty($news['url'])) {
        return 'internal';
    } else {
        return 'text';
    }
}