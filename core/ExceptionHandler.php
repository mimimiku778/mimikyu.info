<?php

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
        if ($exception instanceof NotFoundException) {
            self::notFound();
            return;
        }

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

        echo '404 Not Found';
    }

    /**
     * Writes error messages to the error log file.
     */
    public static function errorLog(Throwable $exception)
    {
        if (ob_get_length() !== false) {
            ob_clean();
        }

        $message = get_class($exception) . ': ' . $exception->getMessage() . ': ' . $exception->getTraceAsString();

        http_response_code(500);

        if (defined('EXCEPTION_HANDLER_DISPLAY_ERRORS') && EXCEPTION_HANDLER_DISPLAY_ERRORS === true) {
            if (isJsonRequest()) {
                jsonResponse(['error' => $message], exit: false);
            }

            echo $message;
        } else {
            if (isJsonRequest()) {
                jsonResponse(['error' => '500 Internal Server Error'], exit: false);
            }

            echo '500 Internal Server Error';
        }

        if (isset($_SERVER["REMOTE_ADDR"]) && isset($_SERVER['HTTP_USER_AGENT'])) {
            $message .= ': ' . ($_SERVER["REMOTE_ADDR"] ?? '') . ': ' . ($_SERVER['HTTP_USER_AGENT'] ?? '');
        }

        $time = date('Y-m-d H:i:s') . ' ' . date_default_timezone_get() . ': ';
        error_log($time . $message . "\n", 3, EXCEPTION_LOG_DIRECTORY);
    }
}
