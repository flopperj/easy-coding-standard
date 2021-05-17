<?php

namespace ECSPrefix20210517\Nette\Utils;

use ECSPrefix20210517\Nette;
/**
 * Secure random string generator.
 */
final class Random
{
    use Nette\StaticClass;
    /**
     * Generates a random string of given length from characters specified in second argument.
     * Supports intervals, such as `0-9` or `A-Z`.
     * @param int $length
     * @param string $charlist
     * @return string
     */
    public static function generate($length = 10, $charlist = '0-9a-z')
    {
        $length = (int) $length;
        $charlist = (string) $charlist;
        $charlist = \count_chars(\preg_replace_callback('#.-.#', function (array $m) : string {
            return \implode('', \range($m[0][0], $m[0][2]));
        }, $charlist), 3);
        $chLen = \strlen($charlist);
        if ($length < 1) {
            throw new \ECSPrefix20210517\Nette\InvalidArgumentException('Length must be greater than zero.');
        } elseif ($chLen < 2) {
            throw new \ECSPrefix20210517\Nette\InvalidArgumentException('Character list must contain at least two chars.');
        }
        $res = '';
        for ($i = 0; $i < $length; $i++) {
            $res .= $charlist[\random_int(0, $chLen - 1)];
        }
        return $res;
    }
}
