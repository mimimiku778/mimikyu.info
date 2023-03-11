<?php

declare(strict_types=1);

/**
 * Check if the request is POST method.
 * 
 * @return bool Whether the request is POST method.
 */
function isPostRequest(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Redirects the user to the specified URL using the specified HTTP response code.
 * 
 * @param string $url The URL to redirect to.
 * @param int $responseCode The HTTP response code to use. Defaults to 301.
 */
function redirect(string $url, int $responseCode = 301)
{
    header('Location: ' . $url, true, $responseCode);
}

/**
 * Returns HTTP status code and response in JSON format and exits.
 *
 * @param array $data The array to be returned as response.
 * @param ?int $responseCode [optional] HTTP status code
 * @param bool $exit [optional] Whether to exit after sending the response. Default is true.
 */
function jsonResponse(array $data, ?int $responseCode = null, bool $exit = true)
{
    if (!is_null($responseCode)) {
        http_response_code($responseCode);
    }

    header("Content-Type: application/json; charset=utf-8");
    ob_start('ob_gzhandler');
    echo json_encode($data);

    if ($exit) {
        exit;
    }
}

/**
 * Check if the request is for JSON data.
 * 
 * @return bool Whether the request is for `application/json`.
 */
function isJsonRequest(): bool
{
    return strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
}

/**
 * Generate a random CSRF token, save it to the session, and output an HTML input element containing the token.
 */
function csrfField()
{
    // Generate a random 16-byte token.
    $token = bin2hex(random_bytes(16));

    // Save the token to the session.
    $_SESSION['_csrf'] = $token;

    // Output an HTML input element containing the token.
    echo '<input type="hidden" name="_csrf" value="' . $token . '" />';
}

/**
 * Verify the CSRF token from the session and the request.
 *
 * @return bool Returns `true` if the CSRF token in the request matches the token in the session; otherwise, returns `false`.
 */
function verifyCsrfToken(): bool
{
    // Check if the CSRF token is set in the session.
    if (!isset($_SESSION['_csrf'])) {
        return false;
    }

    // Get the CSRF token from the session and unset it to prevent replay attacks.
    $sessionToken = $_SESSION['_csrf'];
    unset($_SESSION['_csrf']);

    // Check if the CSRF token is set in the request.
    if (!isset($_POST['_csrf'])) {
        return false;
    }

    // Verify that the CSRF token in the request matches the token in the session.
    return $_POST['_csrf'] === $sessionToken;
}

/**
 * Retrieve and remove a value from the current session by its name.
 * 
 * @param string $name The name of the value to retrieve and remove.
 * @return mixed|false The retrieved value or false if the session value does not exist.
 */
function getRemoveSessionValue(string $name): mixed
{
    if (!isset($_SESSION[$name])) {
        return false;
    }

    $value = $_SESSION[$name];
    unset($_SESSION[$name]);
    return $value;
}

/**
 * Create a log message from the user's IP address and user agent.
 * 
 * @return string The log message in the format "IP Address: User Agent".
 */
function createUserLogStr(): string
{
    return preg_replace('/[^(\x20-\x7F)]*/', '', ($_SERVER["REMOTE_ADDR"] ?? '') . ': ' . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
}

/**
 * Validate whether the specified key exists in the array and meets the specified string conditions.
 * 
 * @param array $array The array to be validated.
 * @param string $key The key to be validated.
 * @param int|null $maxLength [optional] The maximum length of the string.
 * @param string|null $exactMatch [optional] The string for exact matching.
 * @param string|null $e [optional] An Exception name to be thrown if validation fails.
 * @return bool Whether the validation passed or not.
 * @throws Exception If the specified key exists in the array but its value is invalid, 
 *      and an Exception was provided, it will be thrown.
 */
function validateKeyStr(
    array $array,
    string $key,
    ?int $maxLength = null,
    ?string $exactMatch = null,
    ?string $e = null
): bool {
    if (!isset($array[$key])) {
        return false;
    }

    $input = $array[$key];

    if (!is_string($input)) {
        if ($e !== null) {
            throw new $e;
        }
        return false;
    }

    $normalizedStr = Normalizer::normalize($input, Normalizer::FORM_KC);
    $string = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $normalizedStr);

    if (is_null($string) || empty(trim($string))) {
        if ($e !== null) {
            throw new $e;
        }
        return false;
    }

    if (!is_null($exactMatch)) {
        if ($string === $exactMatch) {
            return true;
        }

        if ($e !== null) {
            throw new $e;
        }

        return false;
    }

    if (!is_null($maxLength) && mb_strlen($string) > $maxLength) {
        if ($e !== null) {
            throw new $e;
        }
        return false;
    }

    return true;
}

/**
 * Validate whether the specified key exists in the array and meets the specified numeric conditions.
 *
 * @param array $array The array to be validated
 * @param string $key The key to be validated
 * @param int|null $maxValue [optional] The maximum numeric value.
 * @param int|null $minValue [optional] The minimum numeric value.
 * @param int|null $exactMatch [optional] The numeric value for exact match.
 * @param string|null $e [optional] An Exception name to be thrown if validation fails.
 * @return bool Whether the validation passed or not.
 * @throws Exception If the specified key exists in the array but its value is invalid, 
 *      and an Exception was provided, it will be thrown.
 */
function validateKeyNum(
    array $array,
    string $key,
    ?int $maxValue = null,
    ?int $minValue = null,
    ?int $exactMatch = null,
    ?string $e = null
): bool {
    if (!isset($array[$key])) {
        return false;
    }

    $input = $array[$key];

    if (!ctype_digit($input)) {
        if ($e !== null) {
            throw new $e;
        }
        return false;
    }

    $number = (int) $input;

    if (!is_null($exactMatch) && $number !== $exactMatch) {
        if ($e !== null) {
            throw new $e;
        }
        return false;
    }

    if (!is_null($minValue) && $number < $minValue) {
        if ($e !== null) {
            throw new $e;
        }
        return false;
    }

    if (!is_null($maxValue) && $number > $maxValue) {
        if ($e !== null) {
            throw new $e;
        }
        return false;
    }

    return true;
}

/**
 * Remove zero-width spaces from a string.
 *
 * @param string $str The input string.
 * @return string The input string without zero-width spaces.
 */
function removeZWS(string $str): string
{
    $normalizedStr = Normalizer::normalize($str, Normalizer::FORM_KC);
    return preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $normalizedStr);
}

/**
 * Removes non-ASCII characters from the given string.
 *
 * @param string $string The input string to be cleaned.
 * @return string The cleaned string with only ASCII characters.
 */
function sanitizeString(string $string): string
{
    return preg_replace('/[^(\x20-\x7F)]*/', '', $string);
}

/**
 * Get a numeric value from the query string for the specified key, or return the default value if not found or invalid.
 * 
 * @param string $key The key to retrieve from the query string
 * @param int $default The default numeric value to return if the key is not found or the value is invalid
 * @return int The retrieved numeric value, or the default value if not found or invalid
 */
function getQueryNumValue(string $queryKey, int $defaultValue): int
{
    return (int) ($_GET[$queryKey] ?? $defaultValue);
}

/**
 * Calculates the maximum number of pages needed to display a set of records,
 * given the total number of records and the number of records to display per page.
 *
 * @param int $totalRecords The total number of records to display.
 * @param int $itemsPerPage The number of records to display per page.
 * @return int The maximum number of pages needed to display all the records.
 */
function calcMaxPages(int $totalRecords, int $recordsPerPage): int
{
    return (int) ceil($totalRecords / $recordsPerPage);
}

/**
 * Calculate the starting index for a given page.
 *
 * @param int $pageNumber The current page number.
 * @param int $totalRecords The total number of records.
 * @param int $itemsPerPage The number of items to display per page.
 *
 * @return int The starting index for the current page.
 */
function calcStartIndex(int $pageNumber, int $totalRecords, int $itemsPerPage): int
{
    if ($pageNumber === 1) {
        return $totalRecords;
    }

    return $totalRecords - $itemsPerPage * ($pageNumber - 1);
}

/**
 * Calculate the ending index for a given page.
 *
 * @param int $pageNumber The current page number.
 * @param int $totalRecords The total number of records.
 * @param int $itemsPerPage The number of items to display per page.
 * @param int $maxPage The total number of pages needed to display all records.
 *
 * @return int The ending index for the current page.
 */
function calcEndIndex(int $pageNumber, int $totalRecords, int $itemsPerPage, int $maxPage): int
{
    if ($pageNumber === $maxPage) {
        return 1;
    }

    return $totalRecords - $itemsPerPage * $pageNumber;
}

/**
 * Generates the URL for a given page number.
 *
 * @param int $pageNumber The page number to generate the URL for.
 * @param string $url The base URL to use.
 * @return string The URL for the given page number.
 */
function generatePagerUrl(int $pageNumber, string $url): string
{
    $path = ($pageNumber === 1) ? '' : '/' . (string) $pageNumber;
    return $url . $path;
}
