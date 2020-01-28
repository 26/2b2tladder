<?php

if(!isset($argv[1]) || $argv[1] !== "--run") die();

require __DIR__ . '/../includes/DatabaseHandler.php';
require __DIR__ . '/../includes/CacheHandler.php';
require __DIR__ . '/../includes/StatisticsHandler.php';

$db = DatabaseHandler::newFromConfig();

$result = file_get_contents("https://api.2b2t.dev/stats?username=all");
$array = json_decode($result, true);

$con = $db->getConnection();
$time = time();

foreach($array as $item) {
    if(!$item['uuid']) continue;

    $con->prepare("DELETE FROM " . DatabaseHandler::USER_CACHE_TABLE . " WHERE `id` = ?")->execute([$item['id']]);
    $con->prepare("INSERT INTO " . DatabaseHandler::USER_CACHE_TABLE . " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute([
        $item['id'],
        $item['username'],
        $item['uuid'],
        $item['kills'],
        $item['deaths'],
        $item['joins'],
        $item['leaves'],
        $item['adminlevel'],
        'https://api.2b2t.dev/',
        'stats',
        'username=' . $item['username'],
        $time
    ]);

    $statistics_handler = new StatisticsHandler();

    $statistics_handler->storeRecord(StatisticsHandler::KILLS_RANK_TYPE, $item['kills'], $item['uuid']);
    $statistics_handler->storeRecord(StatisticsHandler::DEATHS_RANK_TYPE, $item['deaths'], $item['uuid']);
    $statistics_handler->storeRecord(StatisticsHandler::JOINS_RANK_TYPE, $item['joins'], $item['uuid']);
    $statistics_handler->storeRecord(StatisticsHandler::LEAVES_RANK_TYPE, $item['leaves'], $item['uuid']);

    echo "Saving " . $item['username'] . "\n";
}

echo "\n\n\nDone...\n";


