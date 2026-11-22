<?php
/**
 * Copy this file to config.php and put your own values in it:
 *     cp backend/config/config.example.php backend/config/config.php
 * config.php is listed in .gitignore and must never be committed.
 *
 * Every value can also be overridden by an environment variable of the same name,
 * which is handy on a server (deploy) where you do not want to edit files.
 */
class Config
{
    public static function get_env($name, $default)
    {
        $value = getenv($name);
        return ($value !== false && $value !== '') ? $value : $default;
    }

    // ---- Database -------------------------------------------------------
    public static function DB_HOST()     { return self::get_env('DB_HOST', '127.0.0.1'); }
    public static function DB_PORT()     { return self::get_env('DB_PORT', '3306'); }
    public static function DB_NAME()     { return self::get_env('DB_NAME', 'gym_app'); }
    public static function DB_USER()     { return self::get_env('DB_USER', 'root'); }
    public static function DB_PASSWORD() { return self::get_env('DB_PASSWORD', ''); }

    // ---- JWT (used from milestone 4) -------------------------------------
    public static function JWT_SECRET()  { return self::get_env('JWT_SECRET', 'change-me-to-a-long-random-string'); }
    public static function JWT_ISSUER()  { return self::get_env('JWT_ISSUER', 'gym-app'); }
    public static function JWT_TTL()     { return (int) self::get_env('JWT_TTL', 7200); }

    // ---- External API (wger, used from milestone 3/4) ---------------------
    public static function WGER_BASE_URL() { return self::get_env('WGER_BASE_URL', 'https://wger.de/api/v2'); }
    public static function WGER_API_KEY()  { return self::get_env('WGER_API_KEY', ''); }
    public static function WGER_TIMEOUT()  { return (int) self::get_env('WGER_TIMEOUT', 5); }
}
