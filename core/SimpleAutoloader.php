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
        $classFile = str_replace('\\', '/', ltrim($className, '\\')) . '.php';
        foreach (SIMPLE_AUTOLOADER_ROOT_DIRECTORY_NAMES as $rootDir) {
            $classPath = __DIR__ . '/../' . $rootDir . '/' . $classFile;
            if (file_exists($classPath)) {
                require $classPath;
                return;
            }
        }
    }
}