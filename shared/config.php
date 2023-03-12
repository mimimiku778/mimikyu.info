<?php

// Session
// ini_set('session.use_strict_mode', '1');
// session_name('my_session');
session_set_cookie_params([
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);

// Display exceptions on/off.
const EXCEPTION_HANDLER_DISPLAY_ERRORS = true;

// Exceptions Log directory.
const EXCEPTION_LOG_DIRECTORY = __DIR__ . '/exception.log';

// Add root directories to search for class files
const SIMPLE_AUTOLOADER_ROOT_DIRECTORY_NAMES = ['models', 'core'];

// Object name when an object is passed to the view
const VIEW_OBJECT_NAME = 'v';