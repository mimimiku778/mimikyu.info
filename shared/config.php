<?php
// Display exceptions on/off.
const EXCEPTION_HANDLER_DISPLAY_ERRORS = true;
const EXCEPTION_LOG_DIRECTORY = __DIR__ . '/exception.log';

// Add root directories to search for class files
const SIMPLE_AUTOLOADER_ROOT_DIRECTORY_NAMES = ['core', 'models'];