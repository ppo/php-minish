<?php
define("PRIVATE_DIR", __DIR__ . "/_private");
define("PUBLIC_DIR", __DIR__);

// /!\ WARNING /!\
// As we don't want to duplicate the `minish.php` file in this demo, we import it from the root of this repo.
// In your project, don't use this file, use the one in the `skeleton` folder:
//   >> require_once PRIVATE_DIR . "/minish.php";
require_once "../minish.php";


$app = new App();
$app->run();
