<?php

namespace ECSPrefix20210517\Nette\Bridges\CacheLatte;

use ECSPrefix20210517\Latte;
use ECSPrefix20210517\Nette;
use ECSPrefix20210517\Nette\Caching\Cache;
/**
 * Macro {cache} ... {/cache}
 */
final class CacheMacro implements \ECSPrefix20210517\Latte\IMacro
{
    use Nette\SmartObject;
    /** @var bool */
    private $used;
    /**
     * Initializes before template parsing.
     * @return void
     */
    public function initialize()
    {
        $this->used = \false;
    }
    /**
     * Finishes template parsing.
     * @return array(prolog, epilog)
     */
    public function finalize()
    {
        if ($this->used) {
            return ['Nette\\Bridges\\CacheLatte\\CacheMacro::initRuntime($this);'];
        }
    }
    /**
     * New node is found.
     * @return bool
     */
    public function nodeOpened(\ECSPrefix20210517\Latte\MacroNode $node)
    {
        if ($node->modifiers) {
            throw new \ECSPrefix20210517\Latte\CompileException('Modifiers are not allowed in ' . $node->getNotation());
        }
        $this->used = \true;
        $node->empty = \false;
        $node->openingCode = \ECSPrefix20210517\Latte\PhpWriter::using($node)->write('<?php if (Nette\\Bridges\\CacheLatte\\CacheMacro::createCache($this->global->cacheStorage, %var, $this->global->cacheStack, %node.array?)) /* line %var */ try { ?>', \ECSPrefix20210517\Nette\Utils\Random::generate(), $node->startLine);
    }
    /**
     * Node is closed.
     * @return void
     */
    public function nodeClosed(\ECSPrefix20210517\Latte\MacroNode $node)
    {
        $node->closingCode = \ECSPrefix20210517\Latte\PhpWriter::using($node)->write('<?php
				Nette\\Bridges\\CacheLatte\\CacheMacro::endCache($this->global->cacheStack, %node.array?) /* line %var */;
				} catch (\\Throwable $ʟ_e) {
					Nette\\Bridges\\CacheLatte\\CacheMacro::rollback($this->global->cacheStack); throw $ʟ_e;
				} ?>', $node->startLine);
    }
    /********************* run-time helpers ****************d*g**/
    /**
     * @return void
     */
    public static function initRuntime(\ECSPrefix20210517\Latte\Runtime\Template $template)
    {
        if (!empty($template->global->cacheStack)) {
            $file = (new \ReflectionClass($template))->getFileName();
            if (@\is_file($file)) {
                // @ - may trigger error
                \end($template->global->cacheStack)->dependencies[\ECSPrefix20210517\Nette\Caching\Cache::FILES][] = $file;
            }
        }
    }
    /**
     * Starts the output cache. Returns Nette\Caching\OutputHelper object if buffering was started.
     * @return Nette\Caching\OutputHelper|\stdClass
     * @param mixed[]|null $parents
     * @param string $key
     */
    public static function createCache(\ECSPrefix20210517\Nette\Caching\Storage $cacheStorage, $key, &$parents, array $args = null)
    {
        $key = (string) $key;
        if ($args) {
            if (\array_key_exists('if', $args) && !$args['if']) {
                return $parents[] = new \stdClass();
            }
            $key = \array_merge([$key], \array_intersect_key($args, \range(0, \count($args))));
        }
        if ($parents) {
            \end($parents)->dependencies[\ECSPrefix20210517\Nette\Caching\Cache::ITEMS][] = $key;
        }
        $cache = new \ECSPrefix20210517\Nette\Caching\Cache($cacheStorage, 'Nette.Templating.Cache');
        if ($helper = $cache->start($key)) {
            $parents[] = $helper;
        }
        return $helper;
    }
    /**
     * Ends the output cache.
     * @param  Nette\Caching\OutputHelper[]  $parents
     * @return void
     */
    public static function endCache(array &$parents, array $args = null)
    {
        $helper = \array_pop($parents);
        if (!$helper instanceof \ECSPrefix20210517\Nette\Caching\OutputHelper) {
            return;
        }
        if (isset($args['dependencies'])) {
            $args += $args['dependencies']();
        }
        if (isset($args['expire'])) {
            $args['expiration'] = $args['expire'];
            // back compatibility
        }
        $helper->dependencies[\ECSPrefix20210517\Nette\Caching\Cache::TAGS] = isset($args['tags']) ? $args['tags'] : null;
        $helper->dependencies[\ECSPrefix20210517\Nette\Caching\Cache::EXPIRATION] = isset($args['expiration']) ? $args['expiration'] : '+ 7 days';
        $helper->end();
    }
    /**
     * @param  Nette\Caching\OutputHelper[]  $parents
     * @return void
     */
    public static function rollback(array &$parents)
    {
        $helper = \array_pop($parents);
        if ($helper instanceof \ECSPrefix20210517\Nette\Caching\OutputHelper) {
            $helper->rollback();
        }
    }
}
