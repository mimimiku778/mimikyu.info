<?php

declare(strict_types=1);

/**
 * Route - Automatically loads a controller from a directory without requiring a routing definition
 * 
 *       **Example:**
 * 
 *        *https://example.com/about*
 *          `Route::start();` 
 *          new AboutPageController();
 *          AboutPageController::index();
 * 
 *       **Gets any path as a GET value by passing a path with placeholders as an array.**
 *      * *https://example.com/blog/1234*
 *          `Route::start(['blog/{id}']);` 
 *          $_GET['id'] = 1234;
 *          new BlogPageController();
 *          BlogPageController::index();
 * 
 *      * *https://example.com/blog/1234/aritcle*
 *          `Route::start(['blog/{id}']);`
 *          throw new NotFoundException;
 * 
 *      * *https://example.com/blog/1234/aritcle*
 *          `Route::start(['blog/{id}', 'blog/{id}'/article]);`
 *          $_GET['id'] = 1234;
 *          new BlogPageController();
 *          BlogPageController::aritcle();
 * 
 *       **NOTE: If there are three or more actual paths, a 404 error will always occur.**
 *      * *https://example.com/posts/1234/user/image*
 *          `Route::start(['posts/{postId}/user/image']);`
 *          throw new NotFoundException;
 * 
 * @author mimimiku778 <0203.sub@gmail.com>
 * @license https://github.com/mimimiku778/MimimalCMS/blob/master/LICENSE.md
 */
class Route
{
    public static ?array $path = null;
    public static bool $isJson;

    /**
     * Route constructor.
     * Parses incoming request URI and calls corresponding controller method
     * 
     * @param array ...$pathToQuery [optional] An array of paths to search for a match, with parameters enclosed in { }
     * @throws LogicException If the route has been started more than once.
     * @throws NotFoundException
     */
    public static function run(...$pathToQuery)
    {
        if (!is_null(self::$path)) {
            throw new LogicException('Routing has been started multiple times.');
        }

        // Whether request content type is JSON
        self::$isJson = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;

        // Get request URI
        $requestUri = strtolower(strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));

        // Parse request URI
        self::$path = self::parseRequestUri($requestUri, $pathToQuery);

        // Execute controller method
        self::runControllerMethod();
    }

    /**
     * Parses incoming request URI to determine which controller method to call
     *
     * @param string $requestUri Request URI to parse
     * @return array An array include parsed path strings.
     * @throws NotFoundException
     */
    private static function parseRequestUri(string $requestUri, array $pathToQuery): array
    {
        // Remove leading and trailing slashes.
        $requestUri = preg_replace('#^/|/$#', '', $requestUri);

        // Matchs requestUri to pathToQuery and update $_GET and $path variables.
        $path = null;
        if ($pathToQuery !== []) {
            $matchResult = self::matchPath($requestUri, $pathToQuery);

            if ($matchResult !== false) {
                list($query, $parsedPath) = $matchResult;
                $_GET = array_merge($_GET, $query);
                $path = $parsedPath;
            }
        }

        if (is_null($path)) {
            // Split request URI by "/"
            $path = explode('/', $requestUri);
        }

        // If there is a 3rd path, return 404 error
        if (isset($path[2])) {
            throw new NotFoundException;
        }

        // If path contains invalid characters, return 404 error
        if (preg_grep('{[^a-z0-9_]}', $path)) {
            throw new NotFoundException;
        }

        // Return controller name and method name
        return $path;
    }

    /**
     * Search for a matching path in an array of paths and extract parameter values from the request URI.
     *
     * @param string $requestUri The URI to match against
     * @param array $pathToQuery An array of paths to search for a match, with parameters enclosed in { }
     * @return array|false If a match is found, an array containing the extracted parameters and the matched path; otherwise, null
     */
    private static function matchPath(string $requestUri, array $pathToQuery): array|false
    {
        foreach ($pathToQuery as $path) {
            $pattern = str_replace('/', '\/', $path);
            $pattern = preg_replace('<\{([^\/]+)\}>', '(?P<$1>[^\/]+)', $pattern);
            $pattern = '<^' . $pattern . '$>';

            if (preg_match($pattern, $requestUri, $matches)) {
                // Extract parameter values from the URI and return them along with the matched path.
                $query = [];
                $pathParts = explode('/', $path);
                $parsedPath = [];
                foreach ($pathParts as $i => $part) {
                    if (strpos($part, '{') === 0 && strpos($part, '}') === strlen($part) - 1) {
                        $key = substr($part, 1, -1);
                        $query[$key] = $matches[$key];
                    } else {
                        $parsedPath[] = $part;
                    }
                }
                return array($query, $parsedPath);
            }
        }
        return false;
    }

    /**
     * Calls the specified controller method
     *
     * @throws NotFoundException
     */
    private static function runControllerMethod()
    {
        // Set default controller name
        $controllerClassName = self::$isJson ? 'IndexApiController' : 'IndexPageController';

        // Resolve controller name
        if (!empty(self::$path[0])) {
            $controllerPrefix = ucfirst(self::$path[0]);
            $controllerSuffix = self::$isJson ? 'ApiController' : 'PageController';
            $controllerClassName = $controllerPrefix . $controllerSuffix;
        }

        // Resolve method name
        if (isset(self::$path[1])) {
            $methodName = self::$path[1];
        } else {
            // Use "index" if second path is empty
            $methodName = 'index';
        }

        // Resolve controller file path
        $controllerDir = self::$isJson ? 'api' : 'pages';
        $controllerFilePath = __DIR__ . "/../controllers/{$controllerDir}/{$controllerClassName}.php";

        // Return 404 error if controller file does not exist
        if (!file_exists($controllerFilePath)) {
            throw new NotFoundException;
        }

        // Load controller base class
        $controllerBaseClass = self::$isJson ? 'AbstractApiController' : 'AbstractPageController';
        require_once __DIR__ . "/{$controllerBaseClass}.php";

        // Load controller
        require_once $controllerFilePath;
        $controller = new $controllerClassName();

        // Return 404 error if method does not exist or private
        if (!method_exists($controller, $methodName)) {
            throw new NotFoundException;
        }

        $reflectionMethod = new ReflectionMethod($controllerClassName, $methodName);
        if (!$reflectionMethod->isPublic()) {
            throw new NotFoundException;
        }

        // Execute controller method
        $controller->$methodName();
    }
}
