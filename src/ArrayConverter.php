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
     * @var string the delimiter character to be used between values in arrays made of this type.
     */
    public $delimiter;

    /**
     * Convert array from PHP to PostgreSQL
     *
     * @param $value
     * @return null|string
     */
    public function toDb($value)
    {
        if (!is_array($value)) {
            return null;
        }

        if (!$value) {
            return '{}';
        }

        return $this->arrayToString($value);
    }

    /**
     * Convert array from PostgreSQL to PHP
     *
     * @param $value
     * @return array|null
     */
    public function toPhp($value)
    {
        if (empty($value) || $value === 'NULL') {
            return null;
        }

        if ($value == '{}' || $value == '{NULL}') {
            return [];
        }

        $pos = 0;
        return $this->parseArray($value, $pos);
    }

    public function arrayToString($list)
    {
        $result = [];
        foreach ($list as $value) {
            $result[] = $this->valueToString($value);
        }

        return '{' . implode($this->delimiter, $result) . '}';
    }

    public function valueToString($value)
    {
        if (is_array($value)) {
            return $this->arrayToString($value);
        } elseif (is_string($value)) {
            return '"' . addcslashes($value, '"\\') . '"';
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif ($value === null) {
            return 'null';
        }

        return $value;
    }

    public function parseArray($value, &$i)
    {
        $result = [];
        for(++$i; $i < strlen($value); ++$i) {
            switch ($value[$i]) {
                case '}':
                    break 2;
                case $this->delimiter:
                    if (!$result) {
                        $result[] = '';
                    }
                    if (in_array($value[$i + 1], [$this->delimiter, '}'])) {
                        $result[] = '';
                    }
                    break;
                case '{':
                    $result[] = $this->parseArray($value, $i);
                    break;
                case '"':
                    $result[] = $this->parseString($value, $i, 1);
                    break;
                default:
                    $result[] = $this->parseString($value, $i);
            }
        }

        return $result;
    }

    public function parseString($value, &$i, $quoted = 0)
    {
        $ends = $quoted ? ['"'] : [$this->delimiter, '}'];
        $result = '';
        for ($i += $quoted; $i < strlen($value); ++$i) {
            if (in_array($value[$i], $ends)) {
                break;
            } elseif ($value[$i] == '\\' && in_array($value[$i + 1], ['\\', '"'])) {
                ++$i;
            }

            $result .= $value[$i];
        }

        $i += $quoted - 1;

        return $result;
    }
}