#!/usr/bin/env php
<?php
define("PUBLIC_DIR", dirname(__DIR__));

$PRIVATE_DIR = PUBLIC_DIR . "/_private";
if (!file_exists($PRIVATE_DIR)) {
  $PRIVATE_DIR = getenv("MINISH_PRIVATE_DIR");

  $envFile = __DIR__ . ".env";
  if (!$PRIVATE_DIR && file_exists($envFile)) {
    $env = parse_ini_file($envFile);
    $PRIVATE_DIR = $env["MINISH_PRIVATE_DIR"];
  }

  if (!file_exists($PRIVATE_DIR)) {
    throw new Exception("Private folder not defined.");
  }
}

define("PRIVATE_DIR", realpath($PRIVATE_DIR));

require_once PRIVATE_DIR . "/minish.php";


$app = new App(true);
[$sitemapPath, $googlePingUrl] = $app->generateSitemap();

echo "Sitemap successfully generated: {$sitemapPath}\n\n" .
  "When it's online, you can alert Google that the sitemap has been updated" .
  "by opening the following URL:\n  $googlePingUrl";
