<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $_metaTitle; ?></title>
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <?php if ($_routeConfig["description"]): ?>
      <meta name="description" content="<?php echo $_routeConfig["description"]; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="/static/main.css">
  </head>
  <body>
    <main>
      <?php include $_mainTemplate; ?>
    </main>
  </body>
</html>
