<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Application\Version;

use DateTime;
use ECSPrefix20220517\Symfony\Component\Console\Command\Command;
use ECSPrefix20220517\Symfony\Component\Process\Process;
use Symplify\EasyCodingStandard\Exception\VersionException;
/**
 * Inspired by https://github.com/composer/composer/blob/master/src/Composer/Composer.php See
 * https://github.com/composer/composer/blob/6587715d0f8cae0cd39073b3bc5f018d0e6b84fe/src/Composer/Compiler.php#L208
 */
final class StaticVersionResolver
{
    /**
     * @var string
     */
    public const PACKAGE_VERSION = '12e736353f75cf04cdfa4147e341a66a4bf8454b';
    /**
     * @var string
     */
    public const RELEASE_DATE = '2022-05-17 21:46:35';
    /**
     * @var string
     */
    private const GIT = 'git';
    public static function resolvePackageVersion() : string
    {
        $pointsAtProcess = new \ECSPrefix20220517\Symfony\Component\Process\Process([self::GIT, 'tag', '--points-at'], __DIR__);
        if ($pointsAtProcess->run() !== \ECSPrefix20220517\Symfony\Component\Console\Command\Command::SUCCESS) {
            throw new \Symplify\EasyCodingStandard\Exception\VersionException('You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        $tag = \trim($pointsAtProcess->getOutput());
        if ($tag !== '' && $tag !== '0') {
            return $tag;
        }
        $process = new \ECSPrefix20220517\Symfony\Component\Process\Process([self::GIT, 'log', '--pretty="%H"', '-n1', 'HEAD'], __DIR__);
        if ($process->run() !== \ECSPrefix20220517\Symfony\Component\Console\Command\Command::SUCCESS) {
            throw new \Symplify\EasyCodingStandard\Exception\VersionException('You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        $version = \trim($process->getOutput());
        return \trim($version, '"');
    }
    public static function resolverReleaseDateTime() : \DateTime
    {
        $process = new \ECSPrefix20220517\Symfony\Component\Process\Process([self::GIT, 'log', '-n1', '--pretty=%ci', 'HEAD'], __DIR__);
        if ($process->run() !== \ECSPrefix20220517\Symfony\Component\Console\Command\Command::SUCCESS) {
            throw new \Symplify\EasyCodingStandard\Exception\VersionException('You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        return new \DateTime(\trim($process->getOutput()));
    }
}
