<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix20220517\Symfony\Component\Console\Logger;

use ECSPrefix20220517\Psr\Log\AbstractLogger;
use ECSPrefix20220517\Psr\Log\InvalidArgumentException;
use ECSPrefix20220517\Psr\Log\LogLevel;
use ECSPrefix20220517\Symfony\Component\Console\Output\ConsoleOutputInterface;
use ECSPrefix20220517\Symfony\Component\Console\Output\OutputInterface;
/**
 * PSR-3 compliant console logger.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 *
 * @see https://www.php-fig.org/psr/psr-3/
 */
class ConsoleLogger extends \ECSPrefix20220517\Psr\Log\AbstractLogger
{
    public const INFO = 'info';
    public const ERROR = 'error';
    private $output;
    /**
     * @var mixed[]
     */
    private $verbosityLevelMap = [\ECSPrefix20220517\Psr\Log\LogLevel::EMERGENCY => \ECSPrefix20220517\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL, \ECSPrefix20220517\Psr\Log\LogLevel::ALERT => \ECSPrefix20220517\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL, \ECSPrefix20220517\Psr\Log\LogLevel::CRITICAL => \ECSPrefix20220517\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL, \ECSPrefix20220517\Psr\Log\LogLevel::ERROR => \ECSPrefix20220517\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL, \ECSPrefix20220517\Psr\Log\LogLevel::WARNING => \ECSPrefix20220517\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL, \ECSPrefix20220517\Psr\Log\LogLevel::NOTICE => \ECSPrefix20220517\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE, \ECSPrefix20220517\Psr\Log\LogLevel::INFO => \ECSPrefix20220517\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE, \ECSPrefix20220517\Psr\Log\LogLevel::DEBUG => \ECSPrefix20220517\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_DEBUG];
    /**
     * @var mixed[]
     */
    private $formatLevelMap = [\ECSPrefix20220517\Psr\Log\LogLevel::EMERGENCY => self::ERROR, \ECSPrefix20220517\Psr\Log\LogLevel::ALERT => self::ERROR, \ECSPrefix20220517\Psr\Log\LogLevel::CRITICAL => self::ERROR, \ECSPrefix20220517\Psr\Log\LogLevel::ERROR => self::ERROR, \ECSPrefix20220517\Psr\Log\LogLevel::WARNING => self::INFO, \ECSPrefix20220517\Psr\Log\LogLevel::NOTICE => self::INFO, \ECSPrefix20220517\Psr\Log\LogLevel::INFO => self::INFO, \ECSPrefix20220517\Psr\Log\LogLevel::DEBUG => self::INFO];
    /**
     * @var bool
     */
    private $errored = \false;
    public function __construct(\ECSPrefix20220517\Symfony\Component\Console\Output\OutputInterface $output, array $verbosityLevelMap = [], array $formatLevelMap = [])
    {
        $this->output = $output;
        $this->verbosityLevelMap = $verbosityLevelMap + $this->verbosityLevelMap;
        $this->formatLevelMap = $formatLevelMap + $this->formatLevelMap;
    }
    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []) : void
    {
        if (!isset($this->verbosityLevelMap[$level])) {
            throw new \ECSPrefix20220517\Psr\Log\InvalidArgumentException(\sprintf('The log level "%s" does not exist.', $level));
        }
        $output = $this->output;
        // Write to the error output if necessary and available
        if (self::ERROR === $this->formatLevelMap[$level]) {
            if ($this->output instanceof \ECSPrefix20220517\Symfony\Component\Console\Output\ConsoleOutputInterface) {
                $output = $output->getErrorOutput();
            }
            $this->errored = \true;
        }
        // the if condition check isn't necessary -- it's the same one that $output will do internally anyway.
        // We only do it for efficiency here as the message formatting is relatively expensive.
        if ($output->getVerbosity() >= $this->verbosityLevelMap[$level]) {
            $output->writeln(\sprintf('<%1$s>[%2$s] %3$s</%1$s>', $this->formatLevelMap[$level], $level, $this->interpolate($message, $context)), $this->verbosityLevelMap[$level]);
        }
    }
    /**
     * Returns true when any messages have been logged at error levels.
     */
    public function hasErrored() : bool
    {
        return $this->errored;
    }
    /**
     * Interpolates context values into the message placeholders.
     *
     * @author PHP Framework Interoperability Group
     */
    private function interpolate(string $message, array $context) : string
    {
        if (\strpos($message, '{') === \false) {
            return $message;
        }
        $replacements = [];
        foreach ($context as $key => $val) {
            if (null === $val || \is_scalar($val) || $val instanceof \Stringable) {
                $replacements["{{$key}}"] = $val;
            } elseif ($val instanceof \DateTimeInterface) {
                $replacements["{{$key}}"] = $val->format(\DateTime::RFC3339);
            } elseif (\is_object($val)) {
                $replacements["{{$key}}"] = '[object ' . \get_class($val) . ']';
            } else {
                $replacements["{{$key}}"] = '[' . \gettype($val) . ']';
            }
        }
        return \strtr($message, $replacements);
    }
}
