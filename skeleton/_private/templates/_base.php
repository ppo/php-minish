<?php
$META_DESCRIPTION = $_routeConfig["description"] ?? $_settings["metaDescription"] ?? NULL;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $_metaTitle ?></title>
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <?php if ($META_DESCRIPTION): ?>
    <meta name="description" content="<?= htmlspecialchars($META_DESCRIPTION) ?>">
  <?php endif; ?>
  <link rel="stylesheet" href="/static/css/app.css">
</head>
<body>
  <main>
    <?php include $_mainTemplate; ?>
  </main>

  <?php if (ENV === "production" && $_settings["googleAnalyticsID"]): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $_settings["googleAnalyticsID"] ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?= $_settings["googleAnalyticsID"] ?>');
    </script>
  <?php endif; ?>
</body>
</html>
