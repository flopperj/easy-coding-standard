<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractPhpdocToTypeDeclarationFixer;
use PhpCsFixer\DocBlock\Annotation;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
final class PhpdocToPropertyTypeFixer extends \PhpCsFixer\AbstractPhpdocToTypeDeclarationFixer
{
    /**
     * @var array<string, true>
     */
    private $skippedTypes = ['mixed' => \true, 'resource' => \true, 'null' => \true];
    /**
     * {@inheritdoc}
     * @return \PhpCsFixer\FixerDefinition\FixerDefinitionInterface
     */
    public function getDefinition()
    {
        return new \PhpCsFixer\FixerDefinition\FixerDefinition('EXPERIMENTAL: Takes `@var` annotation of non-mixed types and adjusts accordingly the property signature. Requires PHP >= 7.4.', [new \PhpCsFixer\FixerDefinition\VersionSpecificCodeSample('<?php
class Foo {
    /** @var int */
    private $foo;
    /** @var \\Traversable */
    private $bar;
}
', new \PhpCsFixer\FixerDefinition\VersionSpecification(70400)), new \PhpCsFixer\FixerDefinition\VersionSpecificCodeSample('<?php
class Foo {
    /** @var int */
    private $foo;
    /** @var \\Traversable */
    private $bar;
}
', new \PhpCsFixer\FixerDefinition\VersionSpecification(70400), ['scalar_types' => \false])], null, 'This rule is EXPERIMENTAL and [1] is not covered with backward compatibility promise. [2] `@var` annotation is mandatory for the fixer to make changes, signatures of properties without it (no docblock) will not be fixed. [3] Manual actions might be required for newly typed properties that are read before initialization.');
    }
    /**
     * {@inheritdoc}
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @return bool
     */
    public function isCandidate($tokens)
    {
        return \PHP_VERSION_ID >= 70400 && $tokens->isTokenKindFound(\T_DOC_COMMENT);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before PhpdocAlignFixer.
     * Must run after AlignMultilineCommentFixer, CommentToPhpdocFixer, PhpdocIndentFixer, PhpdocScalarFixer, PhpdocToCommentFixer, PhpdocTypesFixer.
     * @return int
     */
    public function getPriority()
    {
        return parent::getPriority();
    }
    /**
     * @param string $type
     * @return bool
     */
    protected function isSkippedType($type)
    {
        return isset($this->skippedTypes[$type]);
    }
    /**
     * {@inheritdoc}
     * @return void
     * @param \SplFileInfo $file
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     */
    protected function applyFix($file, $tokens)
    {
        for ($index = $tokens->count() - 1; 0 < $index; --$index) {
            if ($tokens[$index]->isGivenKind([\T_CLASS, \T_TRAIT])) {
                $this->fixClass($tokens, $index);
            }
        }
    }
    /**
     * @return void
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $index
     */
    private function fixClass($tokens, $index)
    {
        $index = $tokens->getNextTokenOfKind($index, ['{']);
        $classEndIndex = $tokens->findBlockEnd(\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $index);
        for (; $index < $classEndIndex; ++$index) {
            if ($tokens[$index]->isGivenKind(\T_FUNCTION)) {
                $index = $tokens->getNextTokenOfKind($index, ['{', ';']);
                if ($tokens[$index]->equals('{')) {
                    $index = $tokens->findBlockEnd(\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $index);
                }
                continue;
            }
            if (!$tokens[$index]->isGivenKind(\T_DOC_COMMENT)) {
                continue;
            }
            $docCommentIndex = $index;
            $propertyIndexes = $this->findNextUntypedPropertiesDeclaration($tokens, $docCommentIndex);
            if ([] === $propertyIndexes) {
                continue;
            }
            $typeInfo = $this->resolveAppliableType($propertyIndexes, $this->getAnnotationsFromDocComment('var', $tokens, $docCommentIndex));
            if (null === $typeInfo) {
                continue;
            }
            list($propertyType, $isNullable) = $typeInfo;
            if (\in_array($propertyType, ['void', 'callable'], \true)) {
                continue;
            }
            $newTokens = \array_merge($this->createTypeDeclarationTokens($propertyType, $isNullable), [new \PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, ' '])]);
            $tokens->insertAt(\current($propertyIndexes), $newTokens);
            $index = \max($propertyIndexes) + \count($newTokens) + 1;
            $classEndIndex += \count($newTokens);
        }
    }
    /**
     * @return mixed[]
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $index
     */
    private function findNextUntypedPropertiesDeclaration($tokens, $index)
    {
        do {
            $index = $tokens->getNextMeaningfulToken($index);
        } while ($tokens[$index]->isGivenKind([\T_PRIVATE, \T_PROTECTED, \T_PUBLIC, \T_STATIC, \T_VAR]));
        if (!$tokens[$index]->isGivenKind(\T_VARIABLE)) {
            return [];
        }
        $properties = [];
        while (!$tokens[$index]->equals(';')) {
            if ($tokens[$index]->isGivenKind(\T_VARIABLE)) {
                $properties[$tokens[$index]->getContent()] = $index;
            }
            $index = $tokens->getNextMeaningfulToken($index);
        }
        return $properties;
    }
    /**
     * @param array<string, int> $propertyIndexes
     * @param Annotation[]       $annotations
     * @return mixed[]|null
     */
    private function resolveAppliableType(array $propertyIndexes, array $annotations)
    {
        $propertyTypes = [];
        foreach ($annotations as $annotation) {
            $propertyName = $annotation->getVariableName();
            if (null === $propertyName) {
                if (1 !== \count($propertyIndexes)) {
                    continue;
                }
                $propertyName = \key($propertyIndexes);
            }
            if (!isset($propertyIndexes[$propertyName])) {
                continue;
            }
            $typeInfo = $this->getCommonTypeFromAnnotation($annotation, \false);
            if (!isset($propertyTypes[$propertyName])) {
                $propertyTypes[$propertyName] = [];
            } elseif ($typeInfo !== $propertyTypes[$propertyName]) {
                return null;
            }
            $propertyTypes[$propertyName] = $typeInfo;
        }
        if (\count($propertyTypes) !== \count($propertyIndexes)) {
            return null;
        }
        $type = \array_shift($propertyTypes);
        foreach ($propertyTypes as $propertyType) {
            if ($propertyType !== $type) {
                return null;
            }
        }
        return $type;
    }
}