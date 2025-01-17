<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix20220517\Symfony\Component\EventDispatcher;

/**
 * A read-only proxy for an event dispatcher.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ImmutableEventDispatcher implements \ECSPrefix20220517\Symfony\Component\EventDispatcher\EventDispatcherInterface
{
    private $dispatcher;
    public function __construct(\ECSPrefix20220517\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    /**
     * {@inheritdoc}
     */
    public function dispatch(object $event, string $eventName = null) : object
    {
        return $this->dispatcher->dispatch($event, $eventName);
    }
    /**
     * {@inheritdoc}
     * @param callable|mixed[] $listener
     */
    public function addListener(string $eventName, $listener, int $priority = 0)
    {
        throw new \BadMethodCallException('Unmodifiable event dispatchers must not be modified.');
    }
    /**
     * {@inheritdoc}
     */
    public function addSubscriber(\ECSPrefix20220517\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber)
    {
        throw new \BadMethodCallException('Unmodifiable event dispatchers must not be modified.');
    }
    /**
     * {@inheritdoc}
     * @param callable|mixed[] $listener
     */
    public function removeListener(string $eventName, $listener)
    {
        throw new \BadMethodCallException('Unmodifiable event dispatchers must not be modified.');
    }
    /**
     * {@inheritdoc}
     */
    public function removeSubscriber(\ECSPrefix20220517\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber)
    {
        throw new \BadMethodCallException('Unmodifiable event dispatchers must not be modified.');
    }
    /**
     * {@inheritdoc}
     */
    public function getListeners(string $eventName = null) : array
    {
        return $this->dispatcher->getListeners($eventName);
    }
    /**
     * {@inheritdoc}
     * @param callable|mixed[] $listener
     */
    public function getListenerPriority(string $eventName, $listener) : ?int
    {
        return $this->dispatcher->getListenerPriority($eventName, $listener);
    }
    /**
     * {@inheritdoc}
     */
    public function hasListeners(string $eventName = null) : bool
    {
        return $this->dispatcher->hasListeners($eventName);
    }
}
