<?php
require_once __DIR__ . '/exceptions.php';

/**
 * Small reusable validation helpers for services.
 * Every method throws a ValidationException (HTTP 400) with a readable message
 * and does nothing when the value is correct.
 */
class Validator
{
    /** Every field in $fields must exist and must not be empty. */
    public static function required($data, $fields)
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                throw new ValidationException("Field '$field' is required.");
            }
        }
    }

    public static function length($value, $field, $min, $max)
    {
        $length = mb_strlen(trim((string) $value));
        if ($length < $min || $length > $max) {
            throw new ValidationException("Field '$field' must be between $min and $max characters long.");
        }
    }

    public static function integer($value, $field, $min = null, $max = null)
    {
        if (is_bool($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new ValidationException("Field '$field' must be a whole number.");
        }
        if ($min !== null && (int) $value < $min) {
            throw new ValidationException("Field '$field' must be at least $min.");
        }
        if ($max !== null && (int) $value > $max) {
            throw new ValidationException("Field '$field' must be at most $max.");
        }
    }

    /** A number with decimals allowed (for example a price). */
    public static function number($value, $field, $min = null, $max = null)
    {
        if (is_bool($value) || !is_numeric($value)) {
            throw new ValidationException("Field '$field' must be a number.");
        }
        if ($min !== null && $value < $min) {
            throw new ValidationException("Field '$field' must be at least $min.");
        }
        if ($max !== null && $value > $max) {
            throw new ValidationException("Field '$field' must be at most $max.");
        }
    }

    public static function one_of($value, $field, $allowed)
    {
        if (!in_array($value, $allowed, true)) {
            throw new ValidationException("Field '$field' must be one of: " . implode(', ', $allowed) . ".");
        }
    }

    public static function email($value, $field = 'email')
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false || mb_strlen($value) > 120) {
            throw new ValidationException("Field '$field' must be a valid e-mail address (max 120 characters).");
        }
    }

    /** Date in the format YYYY-MM-DD that really exists (2026-02-30 is rejected). */
    public static function date($value, $field)
    {
        $d = DateTime::createFromFormat('Y-m-d', (string) $value);
        if (!$d || $d->format('Y-m-d') !== $value) {
            throw new ValidationException("Field '$field' must be a date in the format YYYY-MM-DD.");
        }
    }

    /** Date and time in the format YYYY-MM-DD HH:MM:SS (use normalize_datetime() first). */
    public static function datetime($value, $field)
    {
        $d = DateTime::createFromFormat('Y-m-d H:i:s', (string) $value);
        if (!$d || $d->format('Y-m-d H:i:s') !== $value) {
            throw new ValidationException("Field '$field' must be a date and time, e.g. 2026-12-03 17:00.");
        }
    }

    /** The moment must be later than now. */
    public static function in_future($value, $field)
    {
        if ($value <= date('Y-m-d H:i:s')) {
            throw new ValidationException("Field '$field' must be in the future.");
        }
    }

    /** At least 8 characters with a lower case letter, an upper case letter and a digit. */
    public static function password($value, $field = 'password')
    {
        $value = (string) $value;
        if (mb_strlen($value) < 8 || mb_strlen($value) > 72
            || !preg_match('/[a-z]/', $value) || !preg_match('/[A-Z]/', $value) || !preg_match('/[0-9]/', $value)) {
            throw new ValidationException("Field '$field' must have 8-72 characters, with a lower case letter, an upper case letter and a digit.");
        }
    }

    /** Turns 2026-12-03T17:00 or 2026-12-03 17:00 into 2026-12-03 17:00:00. Other values are returned unchanged. */
    public static function normalize_datetime($value)
    {
        $value = str_replace('T', ' ', trim((string) $value));
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
            $value .= ':00';
        }
        return $value;
    }
}
