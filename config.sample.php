<?php
// MessageBird
define("LIVE_KEY", "MESSAGE_BIRD_LIVE_KEY");
define("STAGGING_KEY", "MESSAGE_BIRD_TEST_KEY");
define("CHANNEL_ID", "MESSAGE_BIRD_CHANNEL_ID");
define("IS_STAGGING", false);

// DATABASE
define("DB_NAME", 'DATABASE_NAME');
define("DB_USER", 'DATABASE_USER');
define("DB_PASS", 'DATABASE_PASS');
define("DB_HOST", 'localhost');

$db = new Database(DB_NAME, DB_USER, DB_PASS, DB_HOST);