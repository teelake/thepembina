<?php
/**
 * Lightweight file logger to capture production errors.
 */

namespace App\Core;

class Logger
{
    private static string $logFile = '';
    private static bool $shutdownRegistered = false;

    /**
     * Ensure the log file exists and is writable.
     */
    private static function boot(): void
    {
        if (self::$logFile !== '') {
            return;
        }

        $defaultPath = ROOT_PATH . '/logs/app.log';
        $dir = dirname($defaultPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!file_exists($defaultPath)) {
            touch($defaultPath);
            chmod($defaultPath, 0664);
        }

        self::$logFile = $defaultPath;
    }

    /**
     * Register a shutdown handler to capture fatal errors that do not
     * reach PHP's normal error_log (e.g. permission issues).
     */
    public static function registerShutdownHandler(): void
    {
        if (self::$shutdownRegistered) {
            return;
        }

        self::$shutdownRegistered = true;

        register_shutdown_function(function () {
            $error = error_get_last();

            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                self::error('Fatal shutdown error', [
                    'type' => $error['type'],
                    'message' => $error['message'],
                    'file' => $error['file'],
                    'line' => $error['line'],
                    'url' => $_SERVER['REQUEST_URI'] ?? null,
                ]);
            }
        });
    }

    /**
     * Generic logger.
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        self::boot();

        $timestamp = date('Y-m-d H:i:s');
        $contextString = empty($context)
            ? ''
            : ' | context=' . json_encode($context, JSON_PARTIAL_OUTPUT_ON_ERROR);
        $entry = sprintf('[%s] %s: %s%s%s', $timestamp, strtoupper($level), $message, $contextString, PHP_EOL);

        file_put_contents(self::$logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    /**
     * Convenience helper for logging caught exceptions.
     */
    public static function exception(\Throwable $exception, array $context = []): void
    {
        $context = array_merge($context, [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        self::log('exception', 'Unhandled exception', $context);
    }
}


