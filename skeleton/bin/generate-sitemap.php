#!/usr/bin/env php
<?php
define("PUBLIC_DIR", dirname(__DIR__));
define("PRIVATE_DIR", PUBLIC_DIR . "/_private");

require_once PRIVATE_DIR . "/minish.php";

define("IS_DEPLOY_MODE", $argv[1] === "deploy");


$app = new App(TRUE);
[$sitemapPath, $changed, $googlePingUrl] = $app->generateSitemap();
