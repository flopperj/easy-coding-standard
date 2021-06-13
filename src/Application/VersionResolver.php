<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\Application;

use DateTime;
use ECSPrefix20210613\Symfony\Component\Process\Process;
use Symplify\EasyCodingStandard\Exception\VersionException;
use ECSPrefix20210613\Symplify\PackageBuilder\Console\ShellCode;
/**
 * Inspired by https://github.com/composer/composer/blob/master/src/Composer/Composer.php See
 * https://github.com/composer/composer/blob/6587715d0f8cae0cd39073b3bc5f018d0e6b84fe/src/Composer/Compiler.php#L208
 */
final class VersionResolver
{
    /**
     * @var string
     */
    const PACKAGE_VERSION = 'f7f05dde9a7517fa2a55825a9f621480571cb79d';
    /**
     * @var string
     */
    const RELEASE_DATE = '2021-06-13 04:29:05';
    public static function resolvePackageVersion() : string
    {
        $process = new \ECSPrefix20210613\Symfony\Component\Process\Process(['git', 'log', '--pretty="%H"', '-n1', 'HEAD'], __DIR__);
        if ($process->run() !== \ECSPrefix20210613\Symplify\PackageBuilder\Console\ShellCode::SUCCESS) {
            throw new \Symplify\EasyCodingStandard\Exception\VersionException('You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        $version = \trim($process->getOutput());
        return \trim($version, '"');
    }
    public static function resolverReleaseDateTime() : \DateTime
    {
        $process = new \ECSPrefix20210613\Symfony\Component\Process\Process(['git', 'log', '-n1', '--pretty=%ci', 'HEAD'], __DIR__);
        if ($process->run() !== \ECSPrefix20210613\Symplify\PackageBuilder\Console\ShellCode::SUCCESS) {
            throw new \Symplify\EasyCodingStandard\Exception\VersionException('You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        return new \DateTime(\trim($process->getOutput()));
    }
}
