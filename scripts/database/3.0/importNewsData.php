<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$newsTable = new Cupa_Model_DbTable_News();
$newsCategoryTable = new Cupa_Model_DbTable_NewsCategory();

$stmt = $origDb->prepare('SELECT * FROM news');
$stmt->execute();
$results = $stmt->fetchAll();
$totalNews = count($results);
$i = 0;

if(DEBUG) {
    echo "    Importing `News` data:\n";
} else {
    echo "    Importing $totalNews News Items:\n";
    $progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 100, $totalNews);    
}

foreach($results as $row) {
    $categoryId = $newsCategoryTable->fetchCategoryIdFromName($row['type']);
    if(!is_numeric($categoryId)) {
        $categoryId = $newsCategoryTable->insert(array('name' => $row['type']));
    }

    if(DEBUG) {
        echo "        Importing news item `{$row['title']}`...";
    } else {
        $progressBar->update($i);
    }

    $news = $newsTable->createRow();
    $news->category_id = $categoryId;
    $news->slug = $newsTable->slugifyTitle($row['title']);
    $news->title = $row['title'];
    $news->content = $row['content'];
    $news->url = (empty($row['url'])) ? null : $row['url'];
    $news->info = $row['blurb'];
    $news->is_visible = $row['visible'];
    $news->posted_at = $row['created_at'];
    $news->posted_by = 1;
    $news->type = getNewsType($row);
    $news->edited_at = $row['updated_at'];
    $news->last_edited_by = 1;
    $news->save();

    if(DEBUG) {
        echo "Done\n";
    }

    $i++;
}

if(DEBUG) {
    echo "    Done\n";
} else {
    $progressBar->update($totalNews);
    echo "\n";
}

function getNewsType($news)
{
    if(!empty($news['content'])) {
        return 'news';
    } else if(strpos($news['url'], 'http') !== false) {
        return 'external';
    } else if(strpos($news['url'], 'http') === false and !empty($news['url'])) {
        return 'internal';
    } else {
        return 'text';
    }
}