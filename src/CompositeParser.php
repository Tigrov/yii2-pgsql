<?php
/**
 * @link https://github.com/tigrov/yii2-pgsql
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */

namespace tigrov\pgsql;

/**
 * The class converts PostgreSQL composite type representation to PHP array
 *
 * @author Sergei Tigrov <rrr-r@ya.ru>
 */
class CompositeParser
{
    /**
     * Converts PostgreSQL composite type representation to PHP array
     *
     * @param string $value string to be converted
     * @return array|null
     */
    public function parse($value)
    {
        if ($value === null) {
            return null;
        }

        if ($value == '()') {
            return [null];
        }

        return $this->parseComposite($value);
    }

    /**
     * Parses PostgreSQL composite type encoded in string
     *
     * @param string $value
     * @param int $i parse starting position
     * @return array
     */
    private function parseComposite($value, &$i = 0)
    {
        $result = [];
        $length = strlen($value);
        for(++$i; $i < $length; ++$i) {
            switch ($value[$i]) {
                case ')':
                    break 2;
                case ',':
                    if (empty($result)) {
                        $result[] = null;
                    }
                    if (in_array($value[$i + 1], [',', ')'], true)) {
                        $result[] = null;
                    }
                    break;
                default:
                    $result[] = $this->parseString($value, $i);
            }
        }

        return $result;
    }

    /**
     * Parses PostgreSQL encoded string
     *
     * @param string $value
     * @param int $i parse starting position
     * @return string
     */
    private function parseString($value, &$i)
    {
        $isQuoted = $value[$i] === '"';
        $endChars = $isQuoted ? ['"'] : [',', ')'];
        $result = '';
        $length = strlen($value);
        for ($i += $isQuoted ? 1 : 0; $i < $length; ++$i) {
            if (in_array($value[$i], ['\\', '"'], true) && in_array($value[$i + 1], [$value[$i], '"'], true)) {
                ++$i;
            } elseif (in_array($value[$i], $endChars, true)) {
                break;
            }

            $result .= $value[$i];
        }

        $i -= $isQuoted ? 0 : 1;

        return $result;
    }
}