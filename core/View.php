<?php

/**
 * View class for rendering and displaying templates.
 */
class View
{
    /**
     * Rendered content cache.
     *
     * @var string
     */
    private static string $renderCache = '';

    /**
     * Render a template file with optional values.
     *
     * @param string $viewTemplateFile Path to the template file.
     * @param array|null $valuesArray Optional associative array of values to pass to the template, 
     *      Keys starting with "__" will not be sanitized.
     *      * NOTE: The passed array must be an associative array, where keys represent variable names in the view.
     *              The values of the associative array must be strings or arrays, except for keys starting with "__",
     *              which can contain nested strings and arrays.
     * 
     * @throws InvalidArgumentException If passed invalid array values.
     * @throws LogicException If rendering fails.
     */
    public static function render(string $viewTemplateFile, ?array $valuesArray = null): void
    {
        self::$renderCache .= self::get($viewTemplateFile, $valuesArray);
    }

    /**
     * Gets rendered template as a string.
     *
     * @param string $viewTemplateFile Path to the template file.
     * @param array|null $valuesArray Optional associative array of values to pass to the template, 
     *      Keys starting with "__" will not be sanitized.
     *      * NOTE: The passed array must be an associative array, where keys represent variable names in the view.
     *              The values of the associative array must be strings or arrays, except for keys starting with "__",
     *              which can contain nested strings and arrays.
     * 
     * @return string The rendered template as a string.
     * @throws InvalidArgumentException If passed invalid array values.
     * @throws LogicException If rendering fails.
     */
    public static function get(string $viewTemplateFile, ?array $valuesArray = null): string
    {
        if ($valuesArray !== null) {
            if (array_values($valuesArray) === $valuesArray) {
                throw new InvalidArgumentException('The passed array must be an associative array.');
            }

            extract(self::sanitizeArray($valuesArray));
        }

        ob_start();
        include __DIR__ . '/../views/' . $viewTemplateFile . '.php';
        $renderedContent = ob_get_clean();

        if ($renderedContent === false) {
            throw new LogicException("Render failed: {$viewTemplateFile}");
        }

        return $renderedContent;
    }

    /**
     * Display the cached content.
     */
    public static function display(): void
    {
        echo self::$renderCache;
    }

    /**
     * Sanitizes an array of values recursively to prevent XSS attacks.
     *
     * @param array $array Array of values to sanitize.
     * @return array The sanitized array.
     * @throws InvalidArgumentException If a value is neither an array nor a string.
     */
    private static function sanitizeArray(array $array): array
    {
        $sanitizedArray = [];

        foreach ($array as $key => $value) {
            if (substr($key, 0, 2) === '__') {
                $sanitizedArray[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                $sanitizedArray[$key] = self::sanitizeArray($value);
            } elseif (is_string($value)) {
                $sanitizedArray[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            } else {
                throw new InvalidArgumentException('Invalid data type. Value must be an array or a string.');
            }
        }

        return $sanitizedArray;
    }
}
