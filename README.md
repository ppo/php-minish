# phpMinish

Minimalist-ish PHP framework, in a single file, to easily create dynamic websites with clean URLs.

- Customize the base template (master layout).
- Define your routes in a config file.
- Create a sub-template for each route.
- And voilà!


# 🚀 Getting started

<div style="margin-top:30px">
  <img style="float:left;margin:0 40px 0 30px" src="data:image/jpeg;base64,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" height="100" alt="Sid's talking to you…">
  <strong>Dear lazy fellow,</strong><br>
  I guess you hate doing boring manipulations…<br>
  so check the <code>bin/create-project</code> script, it'll guide your through all the necessary steps 😉<br>
  There's also scripts to run your local <code>server</code> and to <code>deploy</code> your project, [more below](#-cherry-on-the-cake).
</div>
<br style="clear:both">


#### First steps

1. Clone this repository.
2. Create the project base structure.
    1. Copy the content of the `skeleton` folder to your `project_dir`.
    2. Copy the `minish.php` file into `project_dir/_private`.
    3. Change the `baseMetaTitle` in `project_dir/_private/config/settings.php`.
3. Launch the PHP Development Server from your `project_dir`.

```bash
git clone git@github.com:ppo/php-minish.git ${minish_git_dir}

cp -r ${minish_git_dir}/skeleton ${project_dir}
cp ${minish_git_dir}/minish.php ${project_dir}/_private/
sed -i "" -e "s/Default Minish/${project_meta_title}/g" ${project_dir}/_private/config/settings.php

php -S localhost:8000
```


#### Next steps

1. Configure your routes in `_private/config/routes.php`, for example `foo-bar`:

```php
<?php
return [
  "home" => ...,
  "foo-bar" => [                 # Route name in dash-case! Not cleaned!
    "path" => "/foo/bar",        # URL path with leading and no trailing slashes. Cleaned anyway.
    "title" => "Foo Bar",        # Title for the HTML meta title, and to generate links in templates.
    // "template" => "foo-bar",  # Default is `route-name` anyway => file: `_private/templates/foo-bar.php`.
  ],
  ...
];
```

2. Create the related templates in `_private/templates/`, for example `foo-bar.php`:

```php
<h1><?php echo $_routeConfig["title"]; ?></h1>
<p>Foo Bar lorem ipsum ipsum dolor sit amet.</p>
<p><a href="<?php echo $_routes["home"]["path"]; ?>">Back to home</a></p>
```

3. Customize the look in `assets/main.css`.


> 🎓 You can find [more details about how this framework works](#-learning-phpminish) here below.


#### Deployment

1. Edit `.htaccess` and replace `example.com` with your domain.
2. Copy the content of your `project_dir` folder on your server. _If you're lazy, check the following chapter 😉_

```bash
sed -i "" -e "s/example\.com/${project_domain}/g" ${project_dir}/.htaccess

rsync -au --delete --progress --exclude-from="$__DIR__/.deployignore" "$project_dir/" "${DEPLOY_DST%/}/"
```


#### 🍒 Cherry on the cake

In the `bin` folder, there are 3 scripts:

- `create-project` to guide you through the process of creating a new project.
- `server` to run your local PHP Development Server.
    - If you use [dnsmasq](http://www.thekelleys.org.uk/dnsmasq/doc.html), replace `localhost` with something like `yourproject.test`.
- `deploy` to rsync your project with your server.
    - You can specify the destination in `.env`. (`.gitignore`'d in that folder)
    - `.deployignore` allows you to configure which files to exclude from rsync.
    - All command-line arguments are passed to rsync, so you `deploy --dry-run`.

```bash
sed -i "" -e "s/localhost/$project_slug.test/g" $project_dir/bin/server

mv $project_dir/bin/.env.example $project_dir/bin/.env
sed -i "" -e "s/^DEPLOY_DST=.*$/DEPLOY_DST=$project_rsync_dst/g" $project_dir/bin/.env
```


# 🎓 Learning phpMinish

Basically, the framework is a single file: `minish.php`.

The minimal project folder looks like this.  
_(The `skeleton` folder has that minimal structure.)_

```
PROJECT_ROOT/
├── _private/             # Folder: all the files of the app.
│   ├── config/           # Folder: all the configuration files.
│   │   ├── routes.php    # Configuration of the routes/URLs.
│   │   └── settings.php  # Application settings.
│   ├── templates/        # Folder: all the templates.
│   │   ├── _base.php     # Base template (rendered by the view, having a `main block`).
│   │   └── home.php      # Template for the home page (loaded in the `main block`).
│   ├── .htaccess         # Apache config denying access to this folder.
│   └── minish.php        # The framework!
├── assets/               # Folder: all the static files (images, css, js).
│   └── main.css          # Site styles.
├── .htaccess             # Apache config for clean URLs and other stuff.
└── index.php             # The default handler launching the app.
```

Check also the `demo` folder for a working example with all the different cases and options.  
You can test it with: `php -S localhost:8000 -t demo`.


## Classes

The framework provides 2 classes:

- `App`: The main component, handling the request and executing the view.
- `View`: The view, receiving context data and rendering templates.


### App

The `App` is the main component that boostraps the application.  
It detects the current path, find the current route, and execute its view.

This class is automatically instantiated and called at the end of the `minish.php` file so that you only need to
require this file in the `index.php`.


##### Lifecycle

1. Initialization (`__constructor()`)
    1. Load settings
    2. Register the autoloader
    3. Load routes
    4. Initialize the request path
2. Execution (`__invoke()`)
    1. Initialize the route
    2. Get the view
    3. Get view data
    4. Execute the view (passing app & data)


##### View data

The `App` passes the following data to the view when executing it.  
*The forced values are prefixed with `_` to avoid name collisions with the initial data (from the config).*

- The initial data, using their own names.
- `_metaTitle`: The HTML meta title, that is formatted using the `settings.metaTitleFormatter`.
- `_requestPath`: The URL/request path.
- `_routeName`: The name of the current route. It can be used to highlight the current page in the navigation.
- `_routeConfig`: The config of the current route. It can be used to display information like the title.
- `_routes`: The routes config, without their `view` and `template` attributes. It can be used to get the path based on the route name ([DRY](https://en.wikipedia.org/wiki/Don%27t_repeat_yourself)).


### View

The `View` component handles templates and may do some processing before.

In its basic form, it receives context data and renders templates.  
In between, it can also act as a controller and perform more actions.


##### Lifecycle

1. Basic instantiation (`__constructor()`)
2. Execution (`__invoke()`)
    1. Initialization (receiving app & data)
    2. Render the view
    3. Initialize template data
    4. Render the base template


##### Template blocks

In the templates, you can simply `include` other template files.


## Config

The `App` handles the following configuration files, that must be located under `_private/config`:

- `routes`: Definition of routes.
- `data`: Initial data for the view/templates.
- `settings`: Settings for the application.


### Routes

Definition of routes.  
File: `_private/config/routes.php`

Each route must have the following structure:

- The index key is the `"route-name"`, /!\ It must be in dash-case.
- `path`: URL path associated with this route. Default: `"/$routeName"`.
- `title`: Title of the page, used in the HTML meta title or to generate navigation links. If the value is falsy, it tries to generate a value using `settings.routeTitleFormatter($routeName)`.
    - If empty, it is always generated.
    - If `false`, it will be generated only in the routes passed to the view, not in the HTML meta title.
- `view`: The view to render the content. It can be defined as follows:
    - As a class name: `"FooBarView"` (default autoload: `Foo-Bar` & `View+s` => `_private/views/foo-bar.php`).
    - As a callable: `[$obj, 'method']`, `"Class::method"`, or `function($app, $data=null) { echo 'content'; }`.
- `template`: If not view, the default `View` is used with this template name (from `_private/templates/{$name}.php`).
    - If not defined, use the `route-name` as template name if that file exists.


```php
<?php
return [
    "minimal-one" => [],  # path: `/minimal-one`
                          # title: `Minimal One`
                          # template: `minimal-one`
    "route-name" => [
      "path" => "/path/for/this-route",
      "title" => "My Route",
    ],
    "other-route" => [
      "path" => "/other-route",  # Default: `/{$routeName}` so could been omitted.
      "title" => "Other Route",
      "template" => "my-other-router",  # Default is `other-route`, so must be defined.
    ],
    "custom-view" => [
      "path" => "/custom-view",
      "title" => "My Custom View",
      "view" => "CustomView",  # Autoloaded from `_private/views/custom.php`.
    ],
];
```


### Data

Initial data for the view/templates. It can content basically anything you want.  
File: `_private/config/data.php`

```php
<?php
return [
  "foo" => "bar",
  "myArray" => ["a" => 1, "b" => 2],
  "isTrue" => true,
  "logoUrl" => "/assets/images/logo.png",
  "now" => time(),
];
```


### Settings

Settings for the application.  
File: `_private/config/settings.php`

They can be accessed from the `View` using `$this->_private->getSetting("name")`.

The following settings are available:

- `autoloader`: A callable that will be passed to `spl_autoload_register`.
    - As a string referencing a static method: `"App::autoloader"`.
    - A lambda function: `function($class) { include "{$class}.php"; }`
- `baseMetaTitle`: The base part of the HTML meta title that is used in `settings.metaTitleFormatter($viewTitle)`.
- `baseTemplateName`: The name of the base template. Default: `"_base"`.
- `metaTitleFormatter`: Used to format the HTML meta title based on `baseTitle` and `viewTitle`. It can be defined as:
    - As a string that will be formatted with `sprintf`. Default method with: `"%2$s | %1$s"`.
    - As a callable.
- `routeTitleFormatter`: A callable to generate the route title based on the route name.
    - Default: `ucwords(trim(str_replace("-", " ", $routeName)))`


## Full structure

```
PROJECT_ROOT/
├── _private/                   # Folder: all the files of your app.
│   ├── config/                 # Folder: all configuration files.
│   │   ├── data.php            # Initial data for the view.
│   │   ├── routes.php          # Configuration of the routes/URLs.
│   │   └── settings.php        # Application settings.
│   ├── templates/              # Folder: all the templates.
│   │   ├── _/                  # Folder: all include/blocks files.
│   │   │   ├── foo.php         # Special block for "foo-bar".
│   │   │   ├── header.php      # Page header.
│   │   │   └── nav.php         # Main navigation.
│   │   ├── _base.php           # Base template, "inherited" by all pages.
│   │   ├── foo-bar.php         # Template for the home page.
│   │   └── home.php            # Template for the home page.
│   ├── views/                  # Folder: the custom Views (autoloaded).
│   │   └── foo-bar.php         # Custom FooBarView.
│   ├── .htaccess               # Apache config denying access to this folder.
│   └── minish.php              # The framework!
├── assets/                     # Folder: all your static files (images, css, js).
│   └── main.css                # Site styles.
├── .htaccess                   # Apache config for clean URLs and other stuff.
└── index.php                   # The default handler launching the app.
```


# 📋 TODO

- Route parameters (e.g. `/blog/{author}/{slug}`), back and forth.
- 🤔(false good idea?) Integration with [Twig](https://github.com/twigphp/Twig)
- 🤔(probably not) Or…
  - Simple `{{ var }}` substitution?
  - Template helpers (echo, ternary operator print, route handling, anchors).


# 📜 License

Licensed under the [MIT License](LICENSE).
