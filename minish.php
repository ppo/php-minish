<?php
/**
 * Minimalist-ish PHP framework, in a single file, to easily create dynamic websites with clean URLs.
 *
 * @version 0.1.0
 * @link https://github.com/ppo/php-minish
 * @license https://github.com/ppo/LICENSE MIT
 * @author Pascal Polleunus
 */

// Make sure the path to the private folder is defined. */
if (!defined("PRIVATE_DIR")) {
  throw new Exception("Cannot locate the private folder. Please define it using a `PRIVATE_DIR` constant.");
}


# ======================================================================================================================
# APP
# ======================================================================================================================

/**
 * The `App` is the main component that boostraps the application.
 *
 * It detects the current path, find the current route, and execute its view.
 * This class is automatically instantiated and called at the end of this file so that you only need to require
 * this file in the `index.php`.
 */
class App {
  /** Folder containing the config files. */
  const CONFIG_DIR = PRIVATE_DIR . "/config";

  /** Default base template name. */
  const DEFAULT_BASE_TEMPLATE_NAME = "_base";

  /** URL to alert Google that the sitemap has been updated. */
  const GOOGLE_PING_URL = "https://www.google.com/webmasters/tools/ping";

  /** Path of the `sitemap.xml` file. */
  const SITEMAP_PATH = PUBLIC_DIR . "/sitemap.xml";

  /** Folder containing the templates. */
  const TEMPLATE_DIR = PRIVATE_DIR . "/templates";


  /**
    * @var array App settings, auto-loaded from `_private/config/settings.php`.
    * @see App::_loadSettings()
    *
    * Supported values:
    * - `autoloader` callable Autoloader function for `spl_autoload_register`. @see App:autoloader
    * - `metaTitleFormatter` string|callable Handler to format the meta title. @see App::getMetaTitle()
    */
  protected $_settings = [
    "autoloader" => "static::autoloader",
    "metaTitleFormatter" => '%2$s | %1$s',  # default, route title
    "routeTitleFormatter" => null,
  ];

  /**
    * @var array Routes configuration (name, path, template|view, title), auto-loaded from `_private/config/routes.php`.
    * @see App::_loadRoutes()
    */
  protected $_routes;

  /**
    * @var string Current URL path (e.g. `/foo/bar` for `https://example.com/foo/bar?param=abc`).
    * @see App::_initRequestPath()
    */
  public $requestPath;

  /**
    * @var string Name of the current route.
    * @see App::_initRoute()
    */
  public $routeName;


  # PUBLIC -------------------------------------------------------------------------------------------------------------

  public function __construct() {
    $this->_loadSettings();
    if ($this->_settings["autoloader"]) { spl_autoload_register($this->_settings["autoloader"]); }
    $this->_loadRoutes();
  }

  /**
   * Execute the app.
   *
   * Lifecycle: detect the route, create the view, initialize view data, execute the view.
   */
  public function __invoke() {
    $this->_initRequestPath();
    $this->_initRoute();
    $view = $this->_getView();  // Handles route not found (i.e. null).
    $data = $this->_getViewData();  // Initialize view data even if route not found.

    // Execute the view passing it a reference to te app, the route name and the view data.
    $view($this, $data);
  }

  /**
   * Handler to attempt to load undefined classes.
   *
   * @param string The class name to load.
   */
  public static function autoloader($class) {
    // Spit the class name by uppercase.
    $chunks = preg_split("/(?=[A-Z])/", $class, -1, PREG_SPLIT_NO_EMPTY);

    // Use the last part pluralized as folder and the rest as dash-case file name.
    $folder = array_pop($chunks) . "s";
    $file = join("-", $chunks);

    // Define the lowercased path under the `PRIVATE_DIR` folder.
    $path = strtolower(PRIVATE_DIR . "/{$folder}/{$file}.php");

    // Import the file if it exists.
    if (file_exists($path)) { include $path; }
  }

  /**
   * Generate a `sitemap.xml` based on the routes configuration.
   */
  public function generateSitemap() {
    // Make sure the path to the public folder is defined. */
    if (!defined("PUBLIC_DIR")) {
      throw new Exception("Cannot locate the public folder. Please define it using a `PUBLIC_DIR` constant.");
    }

    // Make sure the base URL of the website is defined. */
    $baseUrl = $this->_settings["baseUrl"];
    if (!$this->_settings["baseUrl"]) {
      throw new Exception(
        "The base URL is not defined in the app settings. Please define `baseUrl` in `config/settings.php`."
      );
    }

    $sitemapPath = static::SITEMAP_PATH;
    $sitemapUrl = "{$baseUrl}/sitemap.xml";
    $googlePingUrl = static::GOOGLE_PING_URL . "?sitemap=" . rawurlencode($sitemapUrl);

    $file = fopen($sitemapPath, "w");
    fwrite($file, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');

    foreach ($this->_routes as $routeName => $routeConfig) {
      if ($this->templateExists($routeName)) {
        $stat = stat($this->getTemplatePath($routeName));
        $lastMod = date("Y-m-d", $stat["mtime"]);
        fwrite($file,
          "<url>" .
            "<loc>{$baseUrl}{$routeConfig[path]}</loc>" .
            "<lastmod>{$lastMod}</lastmod>" .
            "<changefreq>monthly</changefreq>" .
            "<priority>1</priority>" .
          "</url>"
        );
      }
    }

    fwrite($file, '</urlset>');
    fclose($file);

    return [$sitemapPath, $googlePingUrl];
  }

  /**
   * Return the path to the base template.
   *
   * @return string The name of the base template.
   */
  public function getBaseTemplatePath() {
    return $this->getTemplatePath($this->_settings["baseTemplateName"]);
  }

  /**
   * Get the value for the HTML meta title, combining the base value with the current view one.
   *
   * The base value comes from the app settings and must be named `baseMetaTitle`.
   * The `metaTitleFormatter` can be defined also in the app settings in 2 ways and will always
   * receive as params: `baseTitle`, `viewTitle`.
   * - As a string that will be formatted with `sprintf`.
   * - As a callable.
   *
   * @param string Title of the current view.
   * @return string The value for the HTML meta title.
   */
  public function getMetaTitle($title) {
    $baseMetaTitle = $this->_settings["baseMetaTitle"];

    if (!$baseMetaTitle) { return $title; }
    if (!$title) { return $baseMetaTitle; }

    // Check whether there is a formatter defined in the settings.
    $metaTitleFormatter = $this->_settings["metaTitleFormatter"];
    if ($metaTitleFormatter) {
      // If it's a callable…
      if (is_callable($metaTitleFormatter)) {
        return $metaTitleFormatter($baseMetaTitle, $title);
      }

      // Else it must be a string.
      return sprintf($metaTitleFormatter, $baseMetaTitle, $title);
    }

    // If no formatter defined.
    return $title;
  }

  /**
   * Return a given setting.
   *
   * @return mixed|null The requested setting, or null if not defined.
   */
  public function getSetting($name) {
    return $this->_settings[$name];
  }

  /**
   * Return the path to the template file.
   *
   * @name string The name of the template (i.e. the filename without its extension).
   * @return string The path to the template file.
   */
  public function getTemplatePath($name) {
    return static::TEMPLATE_DIR . "/{$name}.php";
  }

  /**
   * Return whether the template exists.
   *
   * @name string The name of the template (i.e. the filename without its extension).
   * @return bool Whether the template file exists.
   */
  public function templateExists($name) {
    return file_exists($this->getTemplatePath($name));
  }


  # PROTECTED ----------------------------------------------------------------------------------------------------------

  /**
   * Harmonize the path.
   *
   * It must always have a leading slash and no trailing one. The root is only a slash.
   */
  protected function _cleanPath($path) {
    return "/" . trim($path, "/");
  }

  /**
   * If no title, generate a route title base on the route name.
   *
   * This method receives the title as reference and update it directly.
   *
   * @param string &$title Reference to the route title.
   * @param string $routeName The route name.
   * @param boolean Whether to force formatting even if the title is set to `false`.
   */
  protected function _formatRouteTitle(&$title, $routeName, $force=false) {
    $formatter = $this->_settings["routeTitleFormatter"];
    if ($formatter && !$title && ($force || $title !== false)) {
      $title = $formatter($routeName);
      return $title;
    }
  }

  /**
    * Retrieve the view to handle an error, if the template exists, otherwise just throw an exception.
    *
    * @param int $status The error HTTP status code (404 or 500).
    * @return View The view to execute, an error (404 or 500) one in case of problem.
    * @throws Exception If there's no template for that error status.
    */
  protected function _getErrorView($status) {
    http_response_code($status);
    if ($this->templateExists($status)) {
      return new View($status);
    }
    throw new Exception("Error {$status}");
  }

  /**
    * Retrieve the view for the current route.
    *
    * @return View The view to execute, an error (404 or 500) one in case of problem.
    */
  protected function _getView() {
    // If no route found, render the 404 error page.
    if ($this->routeName === null) {
      return $this->_getErrorView(404);
    }

    // Retrieve the route config.
    $config = $this->_routes[$this->routeName];

    // First check if there's a view configured.
    // It can be a callable or the name of a View class (string).
    if (isset($config["view"])) {
      return is_callable($config["view"]) ? $config["view"] : new $config["view"]();
    }

    // Otherwise check if a template is defined, verifying that the file exists.
    // Finally, if template is defined, check if a template exists with the route name.
    $template = null;
    if (isset($config["template"])) {
      if ($this->templateExists($config["template"])) {
        $template = $config["template"];
      }
    } elseif ($this->templateExists($this->routeName)) {
      $template = $this->routeName;
    }

    // If a template that exists has been found, return a default View to render it.
    if ($template) {
      return new View($template);
    }

    // If the route is wrong, render the 500 error page.
    $this->_triggerError("Wrong configuration for '{$this->routeName}' in routes.");
    return $this->_getErrorView(500);
  }

  /**
    * Initialize the data for the view.
    *
    * It's composed of (the forced values are prefixed with `_` to avoid name collisions with the initial data):
    * - The initial data, using their own names. @see App::$_data
    * - `_metaTitle`: The formatted HTML meta title. @see App::getMetaTitle()
    * - `_requestPath`: The URL/request path. @see App::$requestPath
    * - `_routeName`: The name of the current route. @see App::$routeName
    * - `_routeConfig`: The config of the current route.
    * - `_routes`: The routes config, without their `view` and `template` attributes. @see App::$_routes
    */
  protected function _getViewData() {
    $data = $this->_loadConfig("data");

    // Export the formatted HTML meta title.
    $data["_metaTitle"] = $this->getMetaTitle($this->_routes[$this->routeName]["title"]);

    // Export the routes config without their `view` and `template` attributes.
    $data["_routes"] = $this->_routes;
    foreach (array_keys($data["_routes"]) as $routeName) {
      $this->_formatRouteTitle($data["_routes"][$routeName]["title"], $routeName, true);
      unset($data["_routes"][$routeName]["template"]);
      unset($data["_routes"][$routeName]["view"]);
    }

    $data["_requestPath"] = $this->requestPath;
    $data["_routeName"] = $this->routeName;
    $data["_routeConfig"] = $data["_routes"][$this->routeName];

    return $data;
  }

  /**
    * Retrieve the URL path from `$_SERVER`.
    *
    * @see App::_cleanPath()
    */
  protected function _initRequestPath() {
    $this->requestPath = $this->_cleanPath($_SERVER["SCRIPT_URL"] ?: $_SERVER["PATH_INFO"]);
  }

  /**
    * Initialize the route based on the request path, using the first matching.
    *
    * @return string|null The name of the route or null if no route found.
    */
  protected function _initRoute() {
    foreach ($this->_routes as $routeName => $routeConfig) {
      if ($routeConfig["path"] === $this->requestPath) {
        $this->routeName = $routeName;
        return;
      }
    }

    return;
  }

  /**
    * Load configuration from a file in `_private/config/`, if it exists.
    *
    * The file must return an array (e.g. `<?php return […];`).
    *
    * @param string $name The name of the config (i.e. the filename without its extension).
    * @return array The configuration data, or an empty array.
    */
  protected function _loadConfig($name) {
    $path = static::CONFIG_DIR . "/{$name}.php";
    return file_exists($path) ? require $path : [];
  }

  /**
    * Load routes configuration from `_private/config/routes.php`.
    *
    *  Each route must have the following structure:
    *  - The index key is the `route-name`. /!\ It must be in dash-case.
    *  - `path`: URL path associated with this route. Default: `/$routeName`.
    *  - `title`: Title of the page, used in the HTML meta title or to generate navigation links.
    *      - If `false`, it won't be used in the HTML meta title but well in the routes passed to the view.
    *      - If empty, it is generated using `settings.routeTitleFormatter($routeName)`.
    *  - `view`: The view to render the content. It can be defined as follows:
    *      - As a class name: `FooBarView` (default autoload: `Foo-Bar` & `View+s` => `_private/views/foo-bar.php`).
    *      - As a callable: `[$obj, 'method']`, `Class::method`, or `function($app, $data=null) { echo 'content'; }`.
    *  - `template`: If not view, the default `View` is used with this template name (from `_private/templates/{$name}.php`).
    *      - If not defined, use the `route-name` as template name if that file exists.
    */
  protected function _loadRoutes() {
    $routes = $this->_loadConfig("routes");

    // Process routes for auto-complete values.
    foreach (array_keys($routes) as $routeName) {
      // If `path` is not defined, generate one based on the route name.
      if (!$routes[$routeName]["path"]) {
        $routes[$routeName]["path"] = "/{$routeName}";
      } else { // Else, ensure the path is harmonized.
        $routes[$routeName]["path"] = $this->_cleanPath($routes[$routeName]["path"]);
      }

      // If `title` is not defined, convert the route name.
      $this->_formatRouteTitle($routes[$routeName]["title"], $routeName);
    }

    $this->_routes = $routes;
  }

  /**
    * Load app settings from `_private/config/settings.php`.
    */
  protected function _loadSettings() {
    $settings = $this->_loadConfig("settings");
    if ($settings) {
      $this->_settings = array_replace_recursive($this->_settings, $settings);
    }

    // Make sure the base template name is defined.
    if ($this->_settings["routeTitleFormatter"]) {
      if (!is_callable($this->_settings["routeTitleFormatter"])) {
        $this->_triggerError("The setting 'routeTitleFormatter' must be a callable.", true);
      }
    } else {
      $this->_settings["routeTitleFormatter"] = function($routeName) {
        // The route name must be in dash-case.
        return ucwords(trim(str_replace("-", " ", $routeName)));
      };
    }

    // Make sure the base template name is defined.
    if (!$this->_settings["baseTemplateName"]) {
      $this->_settings["baseTemplateName"] = static::DEFAULT_BASE_TEMPLATE_NAME;
    }

    // Verify that the base template file exists.
    if (!$this->templateExists($this->_settings["baseTemplateName"])) {
      $this->_triggerError("Base template '{$this->_settings["baseTemplateName"]}' not found.", true);
    }
  }

  /**
    * Trigger an error message, and optionally throw an exception.
    *
    * @param string $message The error message.
    * @param boolean $throw Whether to throw an exception.
    * @throws Exception If askd for (not default behavior).
    */
  protected function _triggerError($message, $throw=false) {
    trigger_error($message);
    if ($throw) { throw new Exception($message); }
  }
}



# ======================================================================================================================
# VIEW
# ======================================================================================================================

/**
 * The `View` component handles templates and may do some processing before.
 *
 * In its basic form, it receives context data and renders templates.
 * In between, it can also act as a controller and perform more actions.
 */
class View {
  /**
   * Path to the base template.
   *
   * Remark: Not defined as a constant because it's retrieve from the app settings.
   */
  public $BASE_TEMPLATE_PATH;

  /**
   * @var string The name of the main template (for the `content` ).
   * @see View::__construct()
   */
  protected $_mainTemplateName;

  /** @var App Reference to the app. */
  protected $_private;

  /**
    * @var string Name of the current route.
    * @see App::$routeName
    */
  protected $_routeName;

  /**
   * @var array Data for the view/template, received from the app.
   * @see App::_getViewData()
   */
  protected $_data = [];


  # PUBLIC -------------------------------------------------------------------------------------------------------------

  /**
   * Constructor.
   *
   * @templateName string The name of the main template.
   */
  public function __construct($templateName=null) {
    $this->setMainTemplate($templateName);
  }

  /**
   * Execute the view.
   *
   * Lifecycle: Initialize data and render the base template.
   */
  public function __invoke($app, $data=null) {
    $this->_init($app, $data);

    // Render the view/base template.
    $this->render();
  }

  /**
   * Render the view/template.
   */
  public function render() {
    // Export context data as local variables.
    extract($this->_getContext());

    // Render the template (as simple PHP include).
    include $this->BASE_TEMPLATE_PATH;
  }

  /**
   * Define the main template path.
   */
  public function setMainTemplate($templateName) {
    $this->_mainTemplateName = $templateName;
  }


  # PROTECTED ----------------------------------------------------------------------------------------------------------

  /**
   * Return the data for the template.
   *
   * @return array The data for the template.
   */
  protected function _getContext() {
    $context = $this->_data;
    $context["_mainTemplate"] = $this->_getTemplatePath($this->_mainTemplateName);
    return $context;
  }

  /**
   * Return the path to the template file.
   *
   * @name string The name of the template (i.e. the filename without its extension).
   * @return string The path to the template file.
   * @see App::getTemplatePath()
   */
  protected function _getTemplatePath($name) {
    return $name ? $this->_private->getTemplatePath($name) : null;
  }

  /**
   * Initialize the view data.
   *
   * @param App $app Reference to the app.
   * @param array $data Data for the view.
   */
  protected function _init($app, $data=null) {
    $this->BASE_TEMPLATE_PATH = $app->getBaseTemplatePath();
    $this->_private = $app;
    $this->_data = $data ?: [];
  }

  /**
   * Return whether the template exists.
   *
   * @name string The name of the template (i.e. the filename without its extension).
   * @return bool Whether the template file exists.
   * @see App::templateExists()
   */
  protected function _templateExists($name) {
    return $this->_private->templateExists($name);
  }
}


// Auto-launch the app.
return new App();
