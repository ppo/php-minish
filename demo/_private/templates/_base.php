<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $_metaTitle; ?></title>
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sanitize.css@11.0.0/sanitize.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,700">
    <link rel="stylesheet" href="/static/minish.css">
  </head>
  <body>
    <?php include "_/header.php"; ?>

    <main>
      <?php include $_mainTemplate; ?>
    </main>
  </body>
</html>
