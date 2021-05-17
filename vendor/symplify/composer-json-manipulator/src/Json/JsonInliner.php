<?php

namespace ECSPrefix20210517\Symplify\ComposerJsonManipulator\Json;

use ECSPrefix20210517\Nette\Utils\Strings;
use ECSPrefix20210517\Symplify\ComposerJsonManipulator\ValueObject\Option;
use ECSPrefix20210517\Symplify\PackageBuilder\Parameter\ParameterProvider;
final class JsonInliner
{
    /**
     * @var string
     * @see https://regex101.com/r/jhWo9g/1
     */
    const SPACE_REGEX = '#\\s+#';
    /**
     * @var ParameterProvider
     */
    private $parameterProvider;
    public function __construct(\ECSPrefix20210517\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider)
    {
        $this->parameterProvider = $parameterProvider;
    }
    /**
     * @param string $jsonContent
     * @return string
     */
    public function inlineSections($jsonContent)
    {
        $jsonContent = (string) $jsonContent;
        if (!$this->parameterProvider->hasParameter(\ECSPrefix20210517\Symplify\ComposerJsonManipulator\ValueObject\Option::INLINE_SECTIONS)) {
            return $jsonContent;
        }
        $inlineSections = $this->parameterProvider->provideArrayParameter(\ECSPrefix20210517\Symplify\ComposerJsonManipulator\ValueObject\Option::INLINE_SECTIONS);
        foreach ($inlineSections as $inlineSection) {
            $pattern = '#("' . \preg_quote($inlineSection, '#') . '": )\\[(.*?)\\](,)#ms';
            $jsonContent = \ECSPrefix20210517\Nette\Utils\Strings::replace($jsonContent, $pattern, function (array $match) : string {
                $inlined = \ECSPrefix20210517\Nette\Utils\Strings::replace($match[2], self::SPACE_REGEX, ' ');
                $inlined = \trim($inlined);
                $inlined = '[' . $inlined . ']';
                return $match[1] . $inlined . $match[3];
            });
        }
        return $jsonContent;
    }
}
