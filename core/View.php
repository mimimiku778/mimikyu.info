<?php

declare(strict_types=1);

/**
 * View class for rendering and displaying templates.
 */
class View
{
    private const OBJECT_NAME = 'v';

    private static string $renderCache = '';

    /**
     * Render a template file with optional values.
     *
     * @param string $viewTemplateFile Path to the template file.
     * @param array|object|null $valuesArray Optional associative array of values to pass to the template, 
     *                                Keys starting with "__" will not be sanitized.
     * 
     * @throws InvalidArgumentException If passed invalid array.
     * @throws LogicException If rendering fails.
     */
    public static function render(string $viewTemplateFile, array|object|null $valuesArray = null): void
    {
        self::$renderCache .= self::get($viewTemplateFile, $valuesArray);
    }

    /**
     * Gets rendered template as a string.
     *
     * @param string $viewTemplateFile Path to the template file.
     * @param array|object|null $valuesArray Optional associative array of values to pass to the template, 
     *                                Keys starting with "__" will not be sanitized.
     * 
     * @return string The rendered template as a string.
     * @throws InvalidArgumentException If passed invalid array.
     * @throws LogicException If rendering fails.
     */
    public static function get(string $viewTemplateFile, array|object|null $valuesArray = null): string
    {
        if (ob_get_length() !== false) {
            self::$renderCache .= ob_get_clean();
            ob_clean();
        }

        if ($valuesArray !== null) {
            if (is_array($valuesArray) && array_values($valuesArray) === $valuesArray) {
                throw new InvalidArgumentException('The passed array must be an associative array.');
            }

            $sanitizedValues = self::sanitizeArray($valuesArray);
            if (is_array($sanitizedValues)) {
                extract($sanitizedValues);
            } else {
                $OBJECT_NAME = self::OBJECT_NAME;
                $$OBJECT_NAME = $sanitizedValues;
            }
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
     * @return array|object The sanitized array.
     */
    private static function sanitizeArray(array|object $input)
    {
        if (is_array($input)) {
            $output = [];
            foreach ($input as $key => $value) {
                if (substr((string) $key, 0, 2) === '__') {
                    $output[$key] = $value;
                    continue;
                }

                if (is_array($value) || $value instanceof stdClass) {
                    $output[$key] = self::sanitizeArray($value);
                } elseif (is_string($value)) {
                    $output[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                } else {
                    $output[$key] = $value;
                }
            }
        } elseif (is_object($input)) {
            $output = new \stdClass();
            foreach ($input as $key => $value) {
                if (substr((string) $key, 0, 2) === '__') {
                    $output->$key = $value;
                    continue;
                }

                if (is_array($value) || $value instanceof stdClass) {
                    $output->$key = self::sanitizeArray($value);
                } elseif (is_string($value)) {
                    $output->$key = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                } else {
                    $output->$key = $value;
                }
            }
        } else {
            $output = $input;
        }

        return $output;
    }
}
