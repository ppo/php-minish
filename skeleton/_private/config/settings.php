<?php
return [
  # `autoloadDirs` array List of folders for `App::autoloader` to look for classes.
  // "autoloadDirs" => [],

  # `autoloader` callable Custom autoloader function for `spl_autoload_register()`.
  // "autoloader" => function($class) { … },

  # `fileSystemRoutes` boolean Whether to locate template using URL path as file path.
  // "fileSystemRoutes" => TRUE,

  # `baseUrl` string The base URL of the site. Required to generate `sitemap.xml`.
  // Example: `https://example.com`.
  "baseUrl" => "https://%DOMAIN%",

  # `baseTemplateName` string Name of the base template.
  // "baseTemplateName" => "_base",

  # `baseMetaTitle` string Required. The base HTML meta title.
  "baseMetaTitle" => "%PROJECT_META_TITLE%",

  # `metaTitleFormatter` string|callable Handler to format the HTML meta title.
  # Default: `'%2$s | %1$s'`.
  // "metaTitleFormatter" => '%1$s › %2$s',
  // "metaTitleFormatter" => function($baseTitle, $routeTitle) { … },

  # `routeTitleFormatter` string|callable Handler to format the route title.
  // "routeTitleFormatter" => function($routeName) { … },

  # `viewSettings` array Data passed as-is to the view as `_settings`.
  // "viewSettings" => [
  //   "googleAnalyticsID" => "",
  // ],
];
