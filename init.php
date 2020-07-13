<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/vendor/autoload.php';

$API_KEY = IS_STAGGING ? STAGGING_KEY : LIVE_KEY;
$db = new Database(DB_NAME, DB_USER, DB_PASS, DB_HOST);