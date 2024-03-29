<?php
/**
 * Minimalist-ish PHP framework, in a single file, to easily create dynamic websites
 * with clean URLs.
 *
 * @version 0.1.3
 * @link https://github.com/ppo/php-minish
 * @license https://github.com/ppo/php-minish/LICENSE MIT
 * @author Pascal Polleunus
 */


# ==================================================================================================
# INITIALIZATION
# ==================================================================================================

/** Error codes that will be handled by our custom error handler. */
define("MINISH_500_ERRORS", E_ALL & ~E_NOTICE);


// If not done, define the ENV we’re running on.
if (!defined("ENV")) {
  if (getenv("ENV")) {
    define("ENV", getenv("ENV"));
  } elseif (
    array_key_exists("SERVER_NAME", $_SERVER)
    && (
      $_SERVER["SERVER_NAME"] === "localhost"
      || substr($_SERVER["SERVER_NAME"], -5) == ".test"
    )
  ) {
    define("ENV", "local");
  } else {
    define("ENV", "production");
  }
}


// Activate all error reporting on local, and deactivate all otherwise.
ini_set("error_reporting", MINISH_500_ERRORS);

if (ENV === "local") {
  ini_set("display_errors", 1);
  ini_set("display_startup_errors", 1);
  ini_set("html_errors", 1);
  ini_set("error_log", 0);
} else {
  set_error_handler("minishErrorHandler");
  set_exception_handler("minishExceptionHandler");
  ini_set("display_errors", 0);
  ini_set("display_startup_errors", 0);
  ini_set("html_errors", 0);
}


// Make sure the path to the private folder is defined. */
if (!defined("PRIVATE_DIR")) {
  throw new Exception(
    "Cannot locate the private folder. Please define it using a `PRIVATE_DIR` constant."
  );
}


# ==================================================================================================
# APP
# ==================================================================================================

/**
 * The `App` is the main component that boostraps the application.
 *
 * It detects the current path, find the current route, and execute its view.
 *
 * Usage:
 *   ```
 *   define("PRIVATE_DIR", __DIR__ . "/_private");
 *   define("PUBLIC_DIR", __DIR__);
 *   require_once PRIVATE_DIR . "/minish.php";
 *   $app = new App();
 *   $app->run();
 *   ```
 */
class App {
  /** Folder containing the config files. */
  const CONFIG_DIR = PRIVATE_DIR . "/config";

  /** URL to alert Google that the sitemap has been updated. */
  const GOOGLE_PING_URL = "https://www.google.com/webmasters/tools/ping";

  /** Path of the `sitemap.xml` file. */
  const SITEMAP_PATH = PUBLIC_DIR . "/sitemap.xml";

  /** Folder containing the templates. */
  const TEMPLATE_DIR = PRIVATE_DIR . "/templates";


  /**
    * @var array App settings, auto-loaded from `_private/config/settings.php`.
    * @see App::_loadSettings()
    */
  protected $_settings = [
    "autoloader" => "App::autoloader",
    "baseTemplateName" => "_base",
    "metaTitleFormatter" => '%2$s | %1$s',  # Params: `baseTitle, routeTitle`.
    "routeTitleFormatter" => NULL,  # Default value defined in `App::_loadSettings()`.
  ];

  /**
    * @var array Routes configuration (name, path, template|view, title), auto-loaded from
    *   `_private/config/routes.php`.
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


  # PUBLIC -----------------------------------------------------------------------------------------

  /**
   * Constructor.
   *
  * @param boolean $isConsole Whether we’re running in console mode.
  */

  public function __construct($isConsole=FALSE) {
    $this->_loadSettings();
    if ($this->_settings["autoloader"]) {
      spl_autoload_register($this->_settings["autoloader"]);
    }

    if (!$isConsole) {
      $this->_loadRoutes();
    }
  }

  /**
   * Execute the app.
   *
   * Lifecycle: detect the route, create the view, initialize view data, execute the view.
   */
  public function run() {
    $this->_initRoute();
    $view = $this->_getView();  // Handles route not found (i.e. NULL).
    $data = $this->_getViewData();  // Initialize view data even if route not found.

    // Execute the view passing it a reference to te app, the route name and the view data.
    $view($this, $data);
  }

  /**
   * Handler to attempt to load undefined classes.
   *
   * @param string $class The class name to load.
   */
  public static function autoloader($class) {
    // Handle custom views.
    if (substr($class, -4) === "View") {
      // Spit the class name by uppercase.
      $chunks = preg_split("/(?=[A-Z])/", $class, -1, PREG_SPLIT_NO_EMPTY);

      // Use the last part pluralized as folder and the rest as dash-case file name.
      $folder = array_pop($chunks) . "s";
      $file = join("-", $chunks);

      // Define the lowercased path under the `PRIVATE_DIR` folder.
      $path = strtolower(PRIVATE_DIR . "/{$folder}/{$file}.php");

      // Import the file if it exists.
      if (file_exists($path)) { require_once $path; }
      return;
    }

    // Handle other classes. Namespace supported.
    $dirs = array_merge([PRIVATE_DIR], $this->getSetting("autoloadDirs", []));
    foreach ($dirs as $dir) {
      $path = $dir . str_replace("\\", "/", $class) . ".php";
      if (file_exists($path)) { require_once $path; break; }
    }
  }

  /**
   * Return the path to the base template.
   *
   * @return string The name of the base template.
   */
  public function getBaseTemplatePath() {
    $defaultBaseTemplate = $this->_settings["baseTemplateName"] ?? NULL;
    $routeBaseTemplate = $this->_routes[$this->routeName]["baseTemplateName"] ?? NULL;

    return $this->getTemplatePath($routeBaseTemplate ?: $defaultBaseTemplate);
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
   * @param string $routeTitle Title of the current view.
   * @return string The value for the HTML meta title.
   */
  public function getMetaTitle($routeTitle) {
    $baseTitle = $this->_settings["baseMetaTitle"];

    if (!$baseTitle) { return $routeTitle; }
    if (!$routeTitle) { return $baseTitle; }

    // Check whether there is a formatter defined in the settings.
    $metaTitleFormatter = $this->_settings["metaTitleFormatter"];
    if ($metaTitleFormatter) {
      // If it’s a callable…
      if (is_callable($metaTitleFormatter)) {
        return $metaTitleFormatter($baseTitle, $routeTitle);
      }

      // Else it must be a string.
      return sprintf($metaTitleFormatter, $baseTitle, $routeTitle);
    }

    // If no formatter defined.
    return $routeTitle;
  }

  /**
   * Return a given setting.
   *
   * @param mixed $default Default value.
   * @return mixed|null The requested setting, or $default if not defined.
   */
  public function getSetting($name, $default=NULL) {
    return $this->_settings[$name] ?? $default;
  }

  /**
   * Return the path to the template file.
   *
   * @param string $name The name of the template (i.e. the filename without its extension).
   * @return string The path to the template file.
   */
  public function getTemplatePath($name) {
    $basePath = static::TEMPLATE_DIR . "/" . $name;
    if (is_dir($basePath)) { $basePath .= "/index"; }
    return $basePath . ".php";
  }

  /**
   * Return whether the template exists.
   *
   * @param string $name The name of the template (i.e. the filename without its extension).
   * @return bool Whether the template file exists.
   */
  public function templateExists($name) {
    return file_exists($this->getTemplatePath($name));
  }


  # PROTECTED --------------------------------------------------------------------------------------

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
   * @param string &$routeTitle Reference to the route title.
   * @param string $routeName The route name.
   * @param boolean $force Whether to force formatting even if the title is set to `FALSE`.
   */
  protected function _formatRouteTitle(&$routeTitle, $routeName, $force=FALSE) {
    $formatter = $this->_settings["routeTitleFormatter"];
    if ($formatter && !$routeTitle && ($force || $routeTitle !== FALSE)) {
      $routeTitle = $formatter($routeName);
      return $routeTitle;
    }
  }

  /**
    * Retrieve the view to handle an error, if the template exists, otherwise just throw an
    * exception.
    *
    * @param int $status The error HTTP status code (404 or 500).
    * @return View The view to execute, an error (404 or 500) one in case of problem.
    * @throws Exception If there’s no template for that error status.
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
    if ($this->routeName === NULL) {
      return $this->_getErrorView(404);
    }

    // Retrieve the route config.
    $config = $this->_routes[$this->routeName] ?? NULL;

    // Check if it’s a redirection.
    if (isset($config["redirect"])) {
      $url = $config["redirect"];
      if (ENV !== "local" && substr($url, 0, 4) !== "http") {
        $url = $this->getSetting("baseUrl", "") . $url;
      }
      header("Location: {$url}");
      die;
    }

    // Check if there’s a view configured.
    // It can be a callable or the name of a View class (string).
    if (isset($config["view"])) {
      return is_callable($config["view"]) ? $config["view"] : new $config["view"]();
    }

    // Otherwise check if a template is defined, verifying that the file exists.
    // Finally, if template is defined, check if a template exists with the route name.
    $template = NULL;
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
    $message = "Wrong configuration for '{$this->routeName}' in routes.";
    trigger_error($message);
    minishRender500();
  }

  /**
    * Initialize the data for the view.
    *
    * It’s composed of (the forced values are prefixed with `_` to avoid name collisions
    * with the initial data):
    *   - The initial data, using their own names. @see App::$_data
    *   - `_metaTitle`: The formatted HTML meta title. @see App::getMetaTitle()
    *   - `_requestPath`: The URL/request path. @see App::$requestPath
    *   - `_routeName`: The name of the current route. @see App::$routeName
    *   - `_routeConfig`: The config of the current route.
    *   - `_routes`: The routes config, without their `view` and `template` attributes.
    *     @see App::$_routes
    *   - `_settings`: Data passed as-is to the view from the setting `viewSettings`.
    */
  protected function _getViewData() {
    $data = $this->_loadConfig("data");
    $thisRoute = $this->_routes[$this->routeName] ?? NULL;

    $data["_baseUrl"] = $this->getSetting("baseUrl");

    // Export the formatted HTML meta title.
    $data["_metaTitle"] = $this->getMetaTitle($thisRoute["title"] ?? NULL);

    // Export the routes config without unnecessary attributes.
    $data["_routes"] = [];
    foreach ($this->_routes as $routeName => $routeConfig) {
      if (substr($routeName, 0, 2) === "__") continue;

      $this->_formatRouteTitle($routeConfig["title"], $routeName, TRUE);
      unset($routeConfig["baseTemplateName"]);
      unset($routeConfig["template"]);
      unset($routeConfig["view"]);
      unset($routeConfig["sitemap"]);

      $data["_routes"][$routeName] = $routeConfig;
    }

    $data["_requestPath"] = $this->requestPath;
    $data["_routeName"] = $this->routeName;
    $data["_routeConfig"] = $thisRoute;

    $data["_settings"] = $this->getSetting("viewSettings", []);

    return $data;
  }

  /**
    * Retrieve the URL path from `$_SERVER`.
    * @see App::_cleanPath()
    */
  protected function _initRequestPath() {
    $path = $_SERVER["REQUEST_URI"] ?? $_SERVER["SCRIPT_URL"] ?? $_SERVER["PATH_INFO"];
    $uri = parse_url("http://example.com" . $path);
    $path = $uri["path"] ?: "";

    if (!$path) {
      $message = "Current path cannot be retrieved from `\$_SERVER`.";
      trigger_error($message);
      throw new Exception($message);
    }

    $this->requestPath = $this->_cleanPath($path);
  }

  /**
    * Initialize the route based on the request path, using the first matching.
    *
    * @return string|null The name of the route or NULL if no route found.
    */
  protected function _initRoute() {
    $this->_initRequestPath();

    // Look for a match in configured routes.
    foreach ($this->_routes as $routeName => $routeConfig) {
      if ($routeConfig["path"] === $this->requestPath) {
        $this->routeName = $routeName;
        return;
      }
    }

    // Find template based on URL path.
    if ($this->getSetting("fileSystemRoutes", TRUE)) {
      $routeName = trim($this->requestPath, "/") ?: "home";
      if ($this->templateExists($routeName)) {
        $this->routeName = $routeName;
        $this->_routes[$routeName] = [
          "path" => $this->requestPath,
          "title" => NULL,
        ];
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
    *    - The index key is the `route-name`. /!\ It must be in dash-case.
    *    - `path`: URL path associated with this route. Default: `/$routeName`.
    *    - `title`: Title of the page, used in the HTML meta title or to generate navigation links.
    *      - If `FALSE`, it won’t be used in the HTML meta title but well in the routes passed to
    *        the view.
    *      - If empty, it is generated using `settings.routeTitleFormatter($routeName)`.
    *    - `view`: The view to render the content. It can be defined as follows:
    *      - As a class name: `FooBarView`
    *        (default autoload: `Foo-Bar` & `View+s` => `_private/views/foo-bar.php`).
    *      - As a callable: `[$obj, "method"]`, `Class::method`, or
    *        `function($app, $data=NULL) { echo "content"; }`.
    *    - `template`: If not view, the default `View` is used with this template name
    *      (from `_private/templates/{$name}.php`).
    *      - If not defined, use the `route-name` as template name if that file exists.
    */
  protected function _loadRoutes() {
    $routes = $this->_loadConfig("routes");

    // Process routes for auto-complete values.
    foreach (array_keys($routes) as $routeName) {
      // If `path` is not defined, generate one based on the route name.
      if (!($routes[$routeName]["path"] ?? NULL)) {
        $routes[$routeName]["path"] = "/{$routeName}";

      // Else, ensure the path is harmonized.
      } else {
        $routes[$routeName]["path"] = $this->_cleanPath($routes[$routeName]["path"]);
      }

      // If `title` is not defined, convert the route name.
      if ($routes[$routeName]["title"]) {
        $this->_formatRouteTitle($routes[$routeName]["title"], $routeName);
      }
    }

    // On local env, auto-add a route to debug the page.
    if (ENV === "local") {
      $routes["__error500__"] = [
        "path" => $this->_cleanPath("/__debug__/500"),
        "baseTemplate" => "__500__",
        "template" => "__500__",
      ];
    }

    $this->_routes = $routes;
  }

  /**
    * Load app settings from `_private/config/settings.php`.
    *
    * Supported values:
    *   - `autoloadDirs` array List of folders for `App::autoloader` to look for classes.
    *   - `autoloader` callable Custom autoloader function for `spl_autoload_register()`.
    *   - `baseMetaTitle` string Required. The base HTML meta title. @see App:getBaseTemplatePath()
    *   - `baseTemplateName` string Name of the base template. Default: `"_base"`.
    *     @see App::getBaseTemplatePath()
    *   - `baseUrl` string The base URL of the site. Required to generate `sitemap.xml`.
    *     Example: `https://example.com`.
    *   - `metaTitleFormatter` string|callable Handler to format the HTML meta title.
    *     Default: `'%2$s | %1$s'`.
    *     @see App::getMetaTitle()
    *   - `routeTitleFormatter` callable Handler to format the route title.
    *     @see App::_formatRouteTitle()
    */
  protected function _loadSettings() {
    $settings = $this->_loadConfig("settings");
    if (!$settings) { return; }

    $errors = [];

    // `autoloadDirs`: Make sure it’s an array.
    if ($settings["autoloadDirs"] ?? NULL) {
      if (!is_array($settings["autoloadDirs"])) {
        $errors["autoloadDirs"] = "Must be an array of folders.";
      }
    } else {
      $settings["autoloadDirs"] = [];
    }

    // `autoloader`: Make sure it’s callable.
    if ($settings["autoloader"] ?? NULL) {
      if (!is_callable($settings["autoloader"])) {
        $errors["autoloader"] = "Must be callable.";
      }
    }

    // `baseMetaTitle`: Verify it’s defined.
    if (!($settings["baseMetaTitle"] ?? NULL)) {
      $errors["baseMetaTitle"] = "Is required.";
    }

    /// `baseTemplateName`: Verify that the base template file exists.
    if (isset($settings["baseTemplateName"]) && !$this->templateExists($settings["baseTemplateName"])) {
      $erros["baseTemplateName"] = "Base template '{$settings["baseTemplateName"]}' not found.";
    }

    // `baseUrl`: Validate it starts with “http” and has no trailing slash.
    $settings["baseUrl"] = rtrim($settings["baseUrl"], "/");
    if (substr($settings["baseUrl"] ?? "", 0, 4) !== "http") {
      $errors["baseUrl"] = "Must start with \"http(s)://\".";
    }

    // `metaTitleFormatter`: Make sure it’s a string or callable.
    if ($settings["metaTitleFormatter"] ?? NULL) {
      if (
        !is_string($settings["metaTitleFormatter"])
        && !is_callable($settings["metaTitleFormatter"])
      ) {
        $errors["metaTitleFormatter"] = "Must be a string or callable.";
      }
    }

    // `routeTitleFormatter`: Make sure the route title formatter is defined, and is callable.
    if ($settings["routeTitleFormatter"] ?? NULL) {
      if (!is_callable($settings["routeTitleFormatter"])) {
        $errors["routeTitleFormatter"] = "Must be callable.";
      }
    } else {
      $settings["routeTitleFormatter"] = function($routeName) {
        // The route name must be in dash-case.
        return ucwords(trim(str_replace("-", " ", $routeName)));
      };
    }

    // Trigger an error if there are problems in the file.
    if (count($errors)) {
      $message = "Please fix the following problems in `_private/config/settings.php`:\n";
      foreach ($errors as $param => $error) {
        $message .= "- `{$param}`: $error\n";
      }
      trigger_error($message);
      throw new Exception($message);
    }

    // Merge the settings from the config file with the default values.
    $this->_settings = array_replace_recursive($this->_settings, $settings);
  }


  # CONSOLE ----------------------------------------------------------------------------------------

  /**
   * Generate a `sitemap.xml` based on the routes configuration.
   *
   * @param boolean $pingGoogle Whether to ping Google if the sitemap changed. Default: `TRUE`.
   * @return array The triplet [path to sitemap, sitemap changed?, Google ping URL].
   * @throws Exception If the base URL is not defined.
   */
  public function generateSitemap($pingGoogle=TRUE) {
    $isDeployMode = $argv[1] === "deploy";

    // Make sure the path to the public folder is defined.
    if (!defined("PUBLIC_DIR")) {
      throw new Exception(
        "Cannot locate the public folder. Please define it using a `PUBLIC_DIR` constant."
      );
    }

    // Make sure the base URL of the website is defined.
    $baseUrl = $this->_settings["baseUrl"];
    if (!$baseUrl) {
      throw new Exception(
        "The base URL is not defined in the app settings. Please define `baseUrl` in " .
        "`config/settings.php`."
      );
    }

    $this->_loadRoutes();

    $sitemapPath = static::SITEMAP_PATH;
    $sitemapFilename = basename($sitemapPath);
    $sitemapUrl = "{$baseUrl}/{$sitemapFilename}";
    $googlePingUrl = static::GOOGLE_PING_URL . "?sitemap=" . rawurlencode($sitemapUrl);

    // Use a temporary file to generate the current sitemap so that the file doesn't unnecessarily
    // change (cf. file tracking/syncing).
    $tmpFile = tmpfile();
    $tmpFilePath = stream_get_meta_data($tmpFile)["uri"];

    // Generate current sitemap.
    fwrite($tmpFile, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
    foreach ($this->_routes as $routeName => $routeConfig) {
      if ($routeConfig["sitemap"] === FALSE || !$this->templateExists($routeName)) {
        continue;
      }

      // @TODO $lastMod = max(base template, route page)
      $stat = stat($this->getTemplatePath($routeName));
      $lastMod = date("Y-m-d", $stat["mtime"]);
      fwrite($tmpFile,
        "<url>" .
          "<loc>{$baseUrl}{$routeConfig['path']}</loc>" .
          "<lastmod>{$lastMod}</lastmod>" .
          "<changefreq>monthly</changefreq>" .
          "<priority>1</priority>" .
        "</url>"
      );
    }
    fwrite($tmpFile, "</urlset>");

    // Check if content changed.
    $currentChecksum = file_exists($sitemapPath) ? md5_file($sitemapPath) : NULL;
    $sitemapChanged = md5_file($tmpFilePath) !== $currentChecksum;

    if (!$sitemapChanged) {
      if (!IS_DEPLOY_MODE) { echo "Sitemap has not changed.\n"; }
    } else {
      copy($tmpFilePath, $sitemapPath);
      echo "\e[32mSitemap successfully generated:\e[0m {$sitemapPath}\n";

      if ($pingGoogle) {
        $response = file_get_contents($googlePingUrl, "r");
        echo "Google has been notified to crawl the new sitemap.";
        if (!IS_DEPLOY_MODE) { echo " \e[37;1mDon't forget to deploy ASAP\e[0m."; }
        echo "\n";
      } else {
        echo "When it's online, you can alert Google that the sitemap has been updated" .
          "by opening the following URL:\n  \e[37;1mcurl $googlePingUrl\e[0m\n";
      }
    }

    // Close temporary file (and auto-deleted).
    fclose($tmpFile);

    return [$sitemapPath, $sitemapChanged, $googlePingUrl];
  }
}



# ==================================================================================================
# VIEWS
# ==================================================================================================

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
   * Remark: Not defined as a constant because it’s retrieve from the app settings.
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


  # PUBLIC -----------------------------------------------------------------------------------------

  /**
   * Constructor.
   *
   * @param string $templateName The name of the main template.
   */
  public function __construct($templateName=NULL) {
    $this->setMainTemplate($templateName);
  }

  /**
   * Execute the view.
   *
   * Lifecycle: Initialize data and render the base template.
   *
   * @param App $app Instance of the app.
   * @param array $data View data.
   */
  public function __invoke($app, $data=NULL) {
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
   *
   * @param string $templateName Name of the main template.
   */
  public function setMainTemplate($templateName) {
    $this->_mainTemplateName = $templateName;
  }


  # PROTECTED --------------------------------------------------------------------------------------

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
   * @see App::getTemplatePath()
   *
   * @param string $name The name of the template (i.e. the filename without its extension).
   * @return string The path to the template file.
   */
  protected function _getTemplatePath($name) {
    return $name ? $this->_private->getTemplatePath($name) : NULL;
  }

  /**
   * Initialize the view data.
   *
   * @param App $app Reference to the app.
   * @param array $data Data for the view.
   */
  protected function _init($app, $data=NULL) {
    $this->BASE_TEMPLATE_PATH = $app->getBaseTemplatePath();
    $this->_private = $app;
    $this->_data = $data ?: [];
  }

  /**
   * Return whether the template exists.
   *
   * @see App::templateExists()
   *
   * @param string $name The name of the template (i.e. the filename without its extension).
   * @return bool Whether the template file exists.
   */
  protected function _templateExists($name) {
    return $this->_private->templateExists($name);
  }
}


/**
 * Allows to render `Twig` templates instead of simple PHP ones.
 */
class TwigView extends View {
  /**
   * Render the view/template.
   */
  public function render() {
    $loader = new \Twig\Loader\FilesystemLoader("templates");
    $twig = new \Twig\Environment($loader, [
        "cache" => "cache",
    ]);

    echo $twig->render($this->BASE_TEMPLATE_PATH, $this->_getContext());
  }
}



# ==================================================================================================
# ERROR HANDLERS
# ==================================================================================================

function minishRender500() {
  header("Status: 500 Internal Server Error");
  include PRIVATE_DIR . "/500.html";
  die;
}

function minishErrorHandler($errNo, $errStr, $errFile=NULL, $errLine=NULL, $errContext=NULL) {
  if ($errNo & MINISH_500_ERRORS) {
    minishRender500();
  }
}

function minishExceptionHandler($exception) {
   minishRender500();
}
