<?php

declare(strict_types=1);

/**
 * Returns HTTP status code and response in JSON format and exits.
 *
 * @param array $data The array to be returned as response.
 * @param ?int $response_code [optional] HTTP status code
 * @param bool $exit [optional] Whether to exit after sending the response. Default is true.
 */
function jsonResponse(array $data, ?int $response_code = null, bool $exit = true)
{
    if (!is_null($response_code)) {
        http_response_code($response_code);
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
 * Check if the request is POST method.
 * 
 * @return bool Whether the request is POST method.
 */
function isPostRequest(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
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
 * Validate whether the specified key exists in the array and meets the specified string conditions.
 * 
 * @param array $array The array to be validated
 * @param string $key The key to be validated
 * @param int|null $max_length The maximum length of the string (optional)
 * @param string|null $exact_match The string for exact matching (optional)
 * @return bool The result of validation
 */
function validateKeyStr(
    array $array,
    string $key,
    ?int $max_length = null,
    ?string $exact_match = null
): bool {
    $input = $array[$key] ?? null;
    if (!is_string($input)) {
        return false;
    }

    $normalizedStr = Normalizer::normalize($input, Normalizer::FORM_KC);
    $string = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $normalizedStr);

    if (is_null($string) || empty(trim($string))) {
        return false;
    }

    if (!is_null($exact_match)) {
        return $string === $exact_match;
    }

    if (!is_null($max_length) && mb_strlen($string) > $max_length) {
        return false;
    }

    return true;
}

/**
 * Validate whether the specified key exists in the array and meets the specified numeric conditions.
 * 
 * @param array $array The array to be validated
 * @param string $key The key to be validated
 * @param int|null $max_value The maximum numeric value (optional)
 * @param int|null $min_value The minimum numeric value (optional)
 * @param int|null $exact_match The numeric value for exact match (optional)
 * @return bool The result of validation
 */
function validateKeyNum(
    array $array,
    string $key,
    ?int $max_value = null,
    ?int $min_value = null,
    ?int $exact_match = null
): bool {
    $input = $array[$key] ?? null;
    if (!ctype_digit($input)) {
        return false;
    }

    $number = (int) $input;

    if (!is_null($exact_match) && $number !== $exact_match) {
        return false;
    }

    if (!is_null($min_value) && $number < $min_value) {
        return false;
    }

    if (!is_null($max_value) && $number > $max_value) {
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
