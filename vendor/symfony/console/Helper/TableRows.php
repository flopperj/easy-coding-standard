<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix20210509\Symfony\Component\Console\Helper;

/**
 * @internal
 */
class TableRows implements \IteratorAggregate
{
    private $generator;
    public function __construct(callable $generator)
    {
        $this->generator = $generator;
    }
    /**
     * @return \Traversable
     */
    public function getIterator()
    {
        $g = $this->generator;
        return $g();
    }
}
