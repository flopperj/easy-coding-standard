<?php

declare (strict_types=1);
namespace ECSPrefix20220517\Symplify\Skipper\SkipVoter;

use ECSPrefix20220517\Symplify\Skipper\Contract\SkipVoterInterface;
use ECSPrefix20220517\Symplify\Skipper\Matcher\FileInfoMatcher;
use ECSPrefix20220517\Symplify\Skipper\SkipCriteriaResolver\SkippedMessagesResolver;
use ECSPrefix20220517\Symplify\SmartFileSystem\SmartFileInfo;
final class MessageSkipVoter implements \ECSPrefix20220517\Symplify\Skipper\Contract\SkipVoterInterface
{
    /**
     * @var \Symplify\Skipper\SkipCriteriaResolver\SkippedMessagesResolver
     */
    private $skippedMessagesResolver;
    /**
     * @var \Symplify\Skipper\Matcher\FileInfoMatcher
     */
    private $fileInfoMatcher;
    public function __construct(\ECSPrefix20220517\Symplify\Skipper\SkipCriteriaResolver\SkippedMessagesResolver $skippedMessagesResolver, \ECSPrefix20220517\Symplify\Skipper\Matcher\FileInfoMatcher $fileInfoMatcher)
    {
        $this->skippedMessagesResolver = $skippedMessagesResolver;
        $this->fileInfoMatcher = $fileInfoMatcher;
    }
    /**
     * @param string|object $element
     */
    public function match($element) : bool
    {
        if (\is_object($element)) {
            return \false;
        }
        return \substr_count($element, ' ') > 0;
    }
    /**
     * @param string|object $element
     */
    public function shouldSkip($element, \ECSPrefix20220517\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : bool
    {
        if (\is_object($element)) {
            return \false;
        }
        $skippedMessages = $this->skippedMessagesResolver->resolve();
        if (!\array_key_exists($element, $skippedMessages)) {
            return \false;
        }
        // skip regardless the path
        $skippedPaths = $skippedMessages[$element];
        if ($skippedPaths === null) {
            return \true;
        }
        return $this->fileInfoMatcher->doesFileInfoMatchPatterns($smartFileInfo, $skippedPaths);
    }
}
