Generate your favicons here: https://realfavicongenerator.net/

Extract the files here, in `static/favicon/`.

Copy the `favicon-16x16.png` file into the root folder as `favicon.png`.

Add the following in the `_private/templates/_base.php` in the `<html><head>`, at the end just before the `</head>`:

```
<link rel="icon" href="<?php echo $_baseUrl; ?>favicon.png" type="image/png">
<link rel="apple-touch-icon" sizes="180x180" href="/static/favicon/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/static/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/static/favicon/favicon-16x16.png">
<link rel="manifest" href="/static/favicon/site.webmanifest">
<meta name="msapplication-config" content="/static/favicon/browserconfig.xml">
<meta name="msapplication-TileColor" content="#000">
<meta name="theme-color" content="#000">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
```
