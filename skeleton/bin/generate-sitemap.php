#!/usr/bin/env php
<?php
define("PUBLIC_DIR", dirname(__DIR__));
define("PRIVATE_DIR", PUBLIC_DIR . "/_private");

require_once PRIVATE_DIR . "/minish.php";


$app = new App(true);
[$sitemapPath, $googlePingUrl] = $app->generateSitemap();

echo "Sitemap successfully generated: {$sitemapPath}\n\n" .
  "When it's online, you can alert Google that the sitemap has been updated" .
  "by opening the following URL:\n  $googlePingUrl";
