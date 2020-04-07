<?php
define("PRIVATE_DIR", __DIR__ . "/_private");
define("PUBLIC_DIR", __DIR__);

$app = require_once PRIVATE_DIR . "/minish.php";
$app();
