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
    if (!is_string($input) || empty(trim($input))) {
        return false;
    }

    if (!is_null($exact_match)) {
        return $input === $exact_match;
    }

    if (!is_null($max_length) && mb_strlen($input) > $max_length) {
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
