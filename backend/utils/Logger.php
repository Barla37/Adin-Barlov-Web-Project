<?php
/**
 * Writes one line per event to backend/logs/info.log or backend/logs/error.log:
 *     [2026-12-01 14:05:12] INFO GET /orders 200 user=12 role=admin 35ms
 * Used for external API calls and failures now; the request/auth middleware of milestone 4 reuses it.
 * Never pass passwords, tokens or API keys to these methods.
 */
class Logger
{
    public static function info($message)
    {
        self::write('info.log', 'INFO', $message);
    }

    public static function error($message)
    {
        self::write('error.log', 'ERROR', $message);
    }

    private static function write($file, $level, $message)
    {
        $dir = __DIR__ . '/../logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        // Keep every entry on one line
        $message = str_replace(["\r", "\n"], ' ', (string) $message);
        // Logging must never break a request, so a failed write is ignored (@)
        @file_put_contents($dir . '/' . $file, '[' . date('Y-m-d H:i:s') . "] $level $message\n", FILE_APPEND | LOCK_EX);
    }
}
