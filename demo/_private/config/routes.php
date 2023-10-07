<?php
return [
  "home" => [
    "path" => "/",
    "title" => FALSE,  # Not used in HTML meta title but generated in routes using
                       # `settings.routeTitleFormatter($routeName)`.
  ],

  "minimal-one" => [],  # path: `/minimal-one`
                        # title: `Minimal One`
                        # template: `minimal-one`

  "path-only" => [
    "path" => "/path-only",  # Default: `/{$routeName}` so could been omitted.
    "title" => "Path Only",
  ],

  "template" => [
    "path" => "/template",
    "title" => "Example Template",
    "template" => "example-template",  # Default is `template`, so must be defined.
  ],

  "redirect-to-template" => [
    "path" => "/redirect-to-template",
    "redirect" => "/template",
  ],

  "extended-view" => [
    "path" => "/extended-view",
    "title" => "Extended View",
    "view" => "MyExtendedView",  # Autoloaded from `_private/views/my-extended.php`.
                                 # That view uses the template: `extended-template`.
  ],

  "independent-view" => [
    "path" => "/independent-view",
    "title" => "Independent View",
    "view" => "IndependentView",
  ],

  "independent-function-view" => [
    "path" => "/independent-function-view",
    "title" => "Independent Function View",
    "view" => function($app, $data=NULL) {
      echo '<h2>Template: Independent Function View</h2><a href="/">Home</a>';
    },
  ],

  // Configured without template or view and template file doesn't exist.
  "error-500" => [
    "path" => "/error-500",
    "title" => "Error 500",
  ],
];
