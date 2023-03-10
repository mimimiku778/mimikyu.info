<?php

/**
 * MimimalCMS
 * 
 * @author mimimiku778 <0203.sub@gmail.com>
 * @license https://github.com/mimimiku778/MimimalCMS/blob/master/LICENSE.md
 */
require_once __DIR__ . '/shared/config.php';
require_once __DIR__ . '/shared/functions.php';
require_once __DIR__ . '/shared/exceptions.php';
require_once __DIR__ . '/core/SimpleAutoloader.php';
require_once __DIR__ . '/core/ExceptionHandler.php';
require_once __DIR__ . '/core/Route.php';

Route::run();