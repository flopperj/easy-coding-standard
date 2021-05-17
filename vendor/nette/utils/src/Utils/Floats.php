<?php

namespace ECSPrefix20210517\Nette\Utils;

use ECSPrefix20210517\Nette;
/**
 * Floating-point numbers comparison.
 */
class Floats
{
    use Nette\StaticClass;
    const EPSILON = 1.0E-10;
    /**
     * @param float $value
     * @return bool
     */
    public static function isZero($value)
    {
        $value = (double) $value;
        return \abs($value) < self::EPSILON;
    }
    /**
     * @param float $value
     * @return bool
     */
    public static function isInteger($value)
    {
        $value = (double) $value;
        return \abs(\round($value) - $value) < self::EPSILON;
    }
    /**
     * Compare two floats. If $a < $b it returns -1, if they are equal it returns 0 and if $a > $b it returns 1
     * @throws \LogicException if one of parameters is NAN
     * @param float $a
     * @param float $b
     * @return int
     */
    public static function compare($a, $b)
    {
        $a = (double) $a;
        $b = (double) $b;
        if (\is_nan($a) || \is_nan($b)) {
            throw new \LogicException('Trying to compare NAN');
        } elseif (!\is_finite($a) && !\is_finite($b) && $a === $b) {
            return 0;
        }
        $diff = \abs($a - $b);
        if ($diff < self::EPSILON || $diff / \max(\abs($a), \abs($b)) < self::EPSILON) {
            return 0;
        }
        return $a < $b ? -1 : 1;
    }
    /**
     * Returns true if $a = $b
     * @throws \LogicException if one of parameters is NAN
     * @param float $a
     * @param float $b
     * @return bool
     */
    public static function areEqual($a, $b)
    {
        $a = (double) $a;
        $b = (double) $b;
        return self::compare($a, $b) === 0;
    }
    /**
     * Returns true if $a < $b
     * @throws \LogicException if one of parameters is NAN
     * @param float $a
     * @param float $b
     * @return bool
     */
    public static function isLessThan($a, $b)
    {
        $a = (double) $a;
        $b = (double) $b;
        return self::compare($a, $b) < 0;
    }
    /**
     * Returns true if $a <= $b
     * @throws \LogicException if one of parameters is NAN
     * @param float $a
     * @param float $b
     * @return bool
     */
    public static function isLessThanOrEqualTo($a, $b)
    {
        $a = (double) $a;
        $b = (double) $b;
        return self::compare($a, $b) <= 0;
    }
    /**
     * Returns true if $a > $b
     * @throws \LogicException if one of parameters is NAN
     * @param float $a
     * @param float $b
     * @return bool
     */
    public static function isGreaterThan($a, $b)
    {
        $a = (double) $a;
        $b = (double) $b;
        return self::compare($a, $b) > 0;
    }
    /**
     * Returns true if $a >= $b
     * @throws \LogicException if one of parameters is NAN
     * @param float $a
     * @param float $b
     * @return bool
     */
    public static function isGreaterThanOrEqualTo($a, $b)
    {
        $a = (double) $a;
        $b = (double) $b;
        return self::compare($a, $b) >= 0;
    }
}
