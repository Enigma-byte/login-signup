<?php
// Set default timezone for the entire application
date_default_timezone_set('Asia/Baghdad'); // GMT+3

/**
 * Configuration Loader
 * Loads and manages environment variables for the application
 */
class Config {
    private static $config = [];

    /**
     * Load environment variables from .env file
     */
    public static function load($path = null) {
        if ($path === null) {
            $path = dirname(__DIR__) . '/.env';
        }

        if (!file_exists($path)) {
            throw new Exception('.env file not found. Please copy .env.example to .env and configure your settings.');
        }

        // Read .env file
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse line
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Remove quotes if present
                if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
                    $value = $matches[2];
                }

                // Set in environment and internal array
                putenv("$name=$value");
                self::$config[$name] = $value;
            }
        }
    }

    /**
     * Get a configuration value
     * @param string $key Configuration key
     * @param mixed $default Default value if key not found
     * @return mixed Configuration value
     */
    public static function get($key, $default = null) {
        return self::$config[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Check if configuration key exists
     * @param string $key Configuration key
     * @return bool
     */
    public static function has($key) {
        return isset(self::$config[$key]) || getenv($key) !== false;
    }
}
