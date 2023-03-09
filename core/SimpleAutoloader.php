<?php

spl_autoload_register('SimpleAutoloader::load');

/**
 * Simple autoloader
 * 
 * It's maybe safe to use this autoloader in conjunction with Composer's autoloader.
 * 
 * NOTE: If the class file is located in the root directory path, namespace is not required.
 *       If the class file is located in a subdirectory, namespace must be used to match the class and file names correctly.
 */
class SimpleAutoloader
{
    public static function load(string $className)
    {
        // Match the subdirectory name with the namespace name.
        $classFile = str_replace('\\', '/', ltrim($className, '\\')) . '.php';

        // Search for the class file in each root directory.
        foreach (SIMPLE_AUTOLOADER_ROOT_DIRECTORY_NAMES as $rootDirectoryName) {
            $classFilePath = __DIR__ . '/../' . $rootDirectoryName . '/' . $classFile;
            if (file_exists($classFilePath)) {
                require_once $classFilePath;
            }
        }
    }
}
