<?php

namespace ECSPrefix20220517\Psr\Log;

/**
 * This is a simple Logger implementation that other Loggers can inherit from.
 *
 * It simply delegates all log-level-specific methods to the `log` method to
 * reduce boilerplate code that a simple Logger that does the same thing with
 * messages regardless of the error level has to implement.
 */
abstract class AbstractLogger implements \ECSPrefix20220517\Psr\Log\LoggerInterface
{
    use LoggerTrait;
}
