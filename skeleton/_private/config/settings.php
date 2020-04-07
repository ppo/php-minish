<?php
return [
  // `autoloadDirs` array List of folders for `App::autoloader` to look for classes.
  // "autoloadDirs" => [],

  // `autoloader` callable Custom autoloader function for `spl_autoload_register()`.
  // "autoloader" => function($class) { … },

  // `baseMetaTitle` string Required. The base HTML meta title.
  "baseMetaTitle" => "Default Minish",

  // `baseTemplateName` string Name of the base template.
  // "baseMetaTitle" => "_base",

  // `baseUrl` string The base URL of the site. Required to generate `sitemap.xml`. Example: `https://example.com`.
  "baseUrl" => "https://example.be",

  // `metaTitleFormatter` string|callable Handler to format the HTML meta title. Default: `'%2$s | %1$s'`.
  // "metaTitleFormatter" => '%1$s › %2$s',
  // "metaTitleFormatter" => function($baseTitle, $routeTitle) { … },

  // `routeTitleFormatter` string|callable Handler to format the route title.
  // "routeTitleFormatter" => function($routeName) { … },
];
