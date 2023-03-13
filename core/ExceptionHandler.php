<?php

/**
 * @author mimimiku778 <0203.sub@gmail.com>
 * @license https://github.com/mimimiku778/MimimalCMS/blob/master/LICENSE.md
 */

set_exception_handler('ExceptionHandler::handleException');

/**
 * A class for handling exceptions thrown in the application. 
 */
class ExceptionHandler
{
    /**
     * Handles the specified Throwable instance.
     */
    public static function handleException(Throwable $exception)
    {
        if (ob_get_length() !== false) {
            ob_clean();
        }

        if ($exception instanceof NotFoundException) {
            self::notFound();
            return;
        }

        if ($exception instanceof ThrottleRequestsException) {
            self::manyRequests();
            self::errorLog($exception);
        }

        if ($exception instanceof BadRequestException) {
            self::badRequest();
            self::errorLog($exception);
        }

        if ($exception instanceof ValidationException) {
            self::badRequest();
            self::errorLog($exception);
        }

        if ($exception instanceof InvalidInputException) {
            self::badRequest();
            self::errorLog($exception);
        }

        if ($exception instanceof DataIntegrityViolationException) {
            self::internalServerError();
            self::errorLog($exception);
        }

        self::internalServerError();
        self::errorLog($exception);
    }

    /**
     * Handles a NotFoundException by returning a 404 error response.
     */
    public static function notFound()
    {
        http_response_code(404);

        if (isJsonRequest()) {
            jsonResponse(['error' => '404 Not Found']);
        }

        echo '404 Not Found<br>';
    }

    /**
     * Handles a NotFoundException by returning a 404 error response.
     */
    public static function manyRequests()
    {
        http_response_code(429);

        if (isJsonRequest()) {
            jsonResponse(['error' => '429 Too Many Requests']);
        }

        echo '429 Too Many Requests<br>';
    }

    /**
     * Handles a BadRequest by returning a 404 error response.
     */
    public static function badRequest()
    {
        http_response_code(400);

        if (isJsonRequest()) {
            jsonResponse(['error' => '400 Bad Request']);
        }

        echo '400 Bad Request<br>';
    }

    public static function internalServerError()
    {
        http_response_code(500);

        if (isJsonRequest()) {
            jsonResponse(['error' => '500 Internal Server Error'], exit: false);
        }

        echo '500 Internal Server Error<br>';
    }

    /**
     * Writes error messages to the error log file and exits.
     * 
     * @param bool $exit [optional] Whether to exit after writing error messages. Default is true.
     */
    public static function errorLog(Throwable $exception, bool $exit = true)
    {
        $message = get_class($exception) . ': ' . $exception->getMessage() . ': ' . $exception->getTraceAsString();

        if (defined('EXCEPTION_HANDLER_DISPLAY_ERRORS') && EXCEPTION_HANDLER_DISPLAY_ERRORS === true) {
            if (isJsonRequest()) {
                jsonResponse(['error' => $message], exit: false);
            }

            echo nl2br($message);
        }

        if (isset($_SERVER["REMOTE_ADDR"]) && isset($_SERVER['HTTP_USER_AGENT'])) {
            $message .= ': ' . $_SERVER["REMOTE_ADDR"] . ': ' . createUserLogStr();
        }

        $time = date('Y-m-d H:i:s') . ' ' . date_default_timezone_get() . ': ';
        error_log($time . $message . "\n", 3, EXCEPTION_LOG_DIRECTORY);

        if ($exit) {
            exit;
        }
    }
}
