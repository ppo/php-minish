<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $_metaTitle; ?></title>
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <?php if ($_routeConfig["description"]): ?>
      <meta name="description" content="<?php echo htmlspecialchars($_routeConfig["description"]); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="/static/css/main.css">
  </head>
  <body>
    <main>
      <?php include $_mainTemplate; ?>
    </main>

    <?php if (ENV === "production" && $_settings["googleAnalyticsID"]): ?>
      <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $_settings["googleAnalyticsID"]; ?>"></script>
      <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo $_settings["googleAnalyticsID"]; ?>');
      </script>
    <?php endif; ?>
  </body>
</html>
