<?php

declare (strict_types=1);
/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace PhpCsFixer\Fixer\PhpTag;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
/**
 * @author Ceeram <ceeram@cakephp.org>
 */
final class BlankLineAfterOpeningTagFixer extends \PhpCsFixer\AbstractFixer implements \PhpCsFixer\Fixer\WhitespacesAwareFixerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition() : \PhpCsFixer\FixerDefinition\FixerDefinitionInterface
    {
        return new \PhpCsFixer\FixerDefinition\FixerDefinition('Ensure there is no code on the same line as the PHP open tag and it is followed by a blank line.', [new \PhpCsFixer\FixerDefinition\CodeSample("<?php \$a = 1;\n\$b = 1;\n")]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NoBlankLinesBeforeNamespaceFixer.
     * Must run after DeclareStrictTypesFixer.
     */
    public function getPriority() : int
    {
        return 1;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\PhpCsFixer\Tokenizer\Tokens $tokens) : bool
    {
        return $tokens->isTokenKindFound(\T_OPEN_TAG);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \PhpCsFixer\Tokenizer\Tokens $tokens) : void
    {
        $lineEnding = $this->whitespacesConfig->getLineEnding();
        // ignore files with short open tag and ignore non-monolithic files
        if (!$tokens[0]->isGivenKind(\T_OPEN_TAG) || !$tokens->isMonolithicPhp()) {
            return;
        }
        $newlineFound = \false;
        /** @var Token $token */
        foreach ($tokens as $token) {
            if ($token->isWhitespace() && \strpos($token->getContent(), "\n") !== \false) {
                $newlineFound = \true;
                break;
            }
        }
        // ignore one-line files
        if (!$newlineFound) {
            return;
        }
        $token = $tokens[0];
        if (\strpos($token->getContent(), "\n") === \false) {
            $tokens[0] = new \PhpCsFixer\Tokenizer\Token([$token->getId(), \rtrim($token->getContent()) . $lineEnding]);
        }
        if (\strpos($tokens[1]->getContent(), "\n") === \false) {
            if ($tokens[1]->isWhitespace()) {
                $tokens[1] = new \PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $lineEnding . $tokens[1]->getContent()]);
            } else {
                $tokens->insertAt(1, new \PhpCsFixer\Tokenizer\Token([\T_WHITESPACE, $lineEnding]));
            }
        }
    }
}
