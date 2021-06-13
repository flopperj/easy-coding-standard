<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix20210613\Symfony\Component\DependencyInjection\Attribute;

/**
 * @Attribute
 */
class TaggedIterator
{
    /**
     * @var string
     */
    public $tag;
    /**
     * @var string|null
     */
    public $indexAttribute;
    /**
     * @param string|null $indexAttribute
     */
    public function __construct(string $tag, $indexAttribute = null)
    {
        $this->tag = $tag;
        $this->indexAttribute = $indexAttribute;
    }
}
