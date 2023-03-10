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
     * @param array|null $valuesArray Optional values to pass to the template.
     * * NOTE: Keys starting with "__" will not be sanitized.
     * 
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
     * @param array|null $valuesArray Optional values to pass to the template.
     * * NOTE: Keys starting with "__" will not be sanitized.
     * 
     * @return string The rendered template as a string.
     * @throws LogicException If rendering fails.
     */
    public static function get(string $viewTemplateFile, ?array $valuesArray = null): string
    {
        if ($valuesArray !== null) {
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
     * Sanitize an array of values recursively to prevent XSS attacks.
     *
     * @param array $array Array of values to sanitize.
     * @return array The sanitized array.
     */
    private static function sanitizeArray(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::sanitizeArray($value);
            } else {
                if (substr($key, 0, 2) !== '__') {
                    $array[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                } else {
                    $array[$key] = $value;
                }
            }
        }
        return $array;
    }
}
