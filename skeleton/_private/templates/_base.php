<?php
// Google Fonts URL without "display=swap".
$GFONTS_CSS = "https://fonts.googleapis.com/css2" .
  "?family=Roboto:wght@400;700";
$META_DESCRIPTION = $_routeConfig["description"]
  ? $_routeConfig["description"]
  : $_settings["metaDescription"];
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $_metaTitle; ?></title>
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <?php if ($_routeConfig["description"]): ?>
      <meta name="description" content="<?php echo htmlspecialchars($META_DESCRIPTION); ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?php echo $GFONTS_CSS; ?>&display=swap" media="print"
      onload="this.media='all'">
    <link rel="preload" href="<?php echo $GFONTS_CSS; ?>" as="style">
    <noscript>
      <link rel="stylesheet" href="<?php echo $GFONTS_CSS; ?>&display=swap">
    </noscript>
    <link rel="stylesheet" href="<?php echo $_basePath; ?>/static/css/app.css">
    <!--[if lt IE 9]>
      <script src="https://cdn.jsdelivr.net/npm/html5shiv@3.7.3/dist/html5shiv.min.js"
        integrity="sha256-9uAoNWHdszsUDhSXf/rVcWOqKPfi5/8V5R4UdbZle2A="
        crossorigin="anonymous"></script>
    <![endif]-->
  </head>
  <body>
    <main>
      <?php include $_mainTemplate; ?>
    </main>

    <?php if (ENV === "production" && $_settings["googleAnalyticsID"]): ?>
      <script
        src="https://www.googletagmanager.com/gtag/js?id=<?php echo $_settings["googleAnalyticsID"]; ?>"
        async></script>
      <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo $_settings["googleAnalyticsID"]; ?>');
      </script>
    <?php endif; ?>
  </body>
</html>
