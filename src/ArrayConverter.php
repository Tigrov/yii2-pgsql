<?php
/**
 * @link https://github.com/tigrov/yii2-pgsql
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */

namespace tigrov\pgsql;

/**
 * ArrayConverter is the class for converting Postgres `array` type to PHP `array` type and vice versa.
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */
class ArrayConverter extends \yii\base\Component
{
    /**
     * Convert array from PHP to PostgreSQL
     *
     * @param array $value array to be converted
     * @param string $delimiter the character to be used between values in arrays
     * @return null|string
     */
    public static function toDb($value, $delimiter = ',', $isComposite = false)
    {
        if (!is_array($value)) {
            return null;
        }

        if (!$value) {
            return $isComposite ? '()' : '{}';
        }

        return static::arrayToString($value, $delimiter, $isComposite);
    }

    /**
     * Convert composite type from PHP to PostgreSQL
     *
     * @param array $value array to be converted
     * @return null|string
     */
    public static function compositeToDb($value)
    {
        return static::toDb($value, ',', true);
    }

    /**
     * Convert array from PostgreSQL to PHP
     *
     * @param string $value string to be converted
     * @param string $delimiter the character to be used between values in arrays
     * @return array|null
     */
    public static function toPhp($value, $delimiter = ',', $isComposite = false)
    {
        if (!$value) {
            return null;
        }

        if ($isComposite) {
            if ($value == '()') {
                return [null];
            }
        } elseif ($value == '{}') {
            return [];
        }

        $pos = 0;
        return static::parseArray($value, $delimiter, $pos, $isComposite);
    }

    /**
     * Convert composite type from PostgreSQL to PHP
     *
     * @param string $value string to be converted
     * @return array|null
     */
    public static function compositeToPhp($value)
    {
        return static::toPhp($value, ',', true);
    }

    protected static function arrayToString($list, $delimiter, $isComposite = false)
    {
        $strings = [];
        foreach ($list as $value) {
            $strings[] = static::valueToString($value, $delimiter, $isComposite);
        }

        $result = implode($delimiter, $strings);

        return $isComposite
            ? '(' . $result . ')'
            : '{' . $result . '}';
    }

    protected static function valueToString($value, $delimiter, $isComposite)
    {
        if (is_array($value)) {
            return static::arrayToString($value, $delimiter);
        } elseif (is_string($value)) {
            return '"' . addcslashes($value, '"\\') . '"';
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif ($value === null && !$isComposite) {
            return 'NULL';
        }

        return $value;
    }

    protected static function parseArray($value, $delimiter, &$i, $isComposite = false)
    {
        $tEnd = $isComposite ? ')' : '}';
        $result = [];
        for(++$i; $i < strlen($value); ++$i) {
            switch ($value[$i]) {
                case $tEnd:
                    break 2;
                case $delimiter:
                    if (!$result) {
                        $result[] = null;
                    }
                    if (in_array($value[$i + 1], [$delimiter, $tEnd])) {
                        $result[] = null;
                    }
                    break;
                case '{':
                    $result[] = static::parseArray($value, $delimiter, $i);
                    break;
                default:
                    $result[] = static::parseString($value, $delimiter, $tEnd, $i, $value[$i] == '"', $isComposite);
            }
        }

        return $result;
    }

    protected static function parseString($value, $delimiter, $tEnd, &$i, $isQuoted = false, $isComposite = false)
    {
        $ends = $isQuoted ? ['"'] : [$delimiter, $tEnd];
        $result = '';
        for ($i += $isQuoted ? 1 : 0; $i < strlen($value); ++$i) {
            if (in_array($value[$i], ['\\', '"']) && in_array($value[$i + 1], [$value[$i], '"'])) {
                ++$i;
            } elseif (in_array($value[$i], $ends)) {
                break;
            }

            $result .= $value[$i];
        }

        $i -= $isQuoted ? 0 : 1;

        if (!$isQuoted && !$isComposite && $result === 'NULL') {
            $result = null;
        }

        return $result;
    }
}