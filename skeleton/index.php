<?php
define("PUBLIC_DIR", __DIR__);
define("PRIVATE_DIR", PUBLIC_DIR . "/_private");

require_once PRIVATE_DIR . "/minish.php";


$app = new App();
$app->run();
