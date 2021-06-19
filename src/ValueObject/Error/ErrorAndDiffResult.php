<?php

declare (strict_types=1);
namespace Symplify\EasyCodingStandard\ValueObject\Error;

use Symplify\EasyCodingStandard\SniffRunner\ValueObject\Error\CodingStandardError;
final class ErrorAndDiffResult
{
    /**
     * @var CodingStandardError[]
     */
    private $codingStandardErrors = [];
    /**
     * @var FileDiff[]
     */
    private $fileDiffs = [];
    /**
     * @var SystemError[]
     */
    private $systemErrors = [];
    /**
     * @param CodingStandardError[] $codingStandardErrors
     * @param FileDiff[] $fileDiffs
     * @param SystemError[] $systemErrors
     */
    public function __construct(array $codingStandardErrors = [], array $fileDiffs = [], array $systemErrors = [])
    {
        $this->codingStandardErrors = $this->sortByFileAndLine($codingStandardErrors);
        $this->fileDiffs = $this->sortByFilePath($fileDiffs);
        $this->systemErrors = $systemErrors;
    }
    public function getErrorCount() : int
    {
        return \count($this->codingStandardErrors) + \count($this->systemErrors);
    }
    public function getFileDiffsCount() : int
    {
        return \count($this->fileDiffs);
    }
    /**
     * @return CodingStandardError[]
     */
    public function getErrors() : array
    {
        return $this->codingStandardErrors;
    }
    /**
     * @return SystemError[]
     */
    public function getSystemErrors() : array
    {
        return $this->systemErrors;
    }
    /**
     * @return FileDiff[]
     */
    public function getFileDiffs() : array
    {
        return $this->fileDiffs;
    }
    /**
     * @param CodingStandardError[] $errorMessages
     * @return CodingStandardError[]
     */
    private function sortByFileAndLine(array $errorMessages) : array
    {
        \usort($errorMessages, function (\Symplify\EasyCodingStandard\SniffRunner\ValueObject\Error\CodingStandardError $firstCodingStandardError, \Symplify\EasyCodingStandard\SniffRunner\ValueObject\Error\CodingStandardError $secondCodingStandardError) : int {
            return [$firstCodingStandardError->getRelativeFilePath(), $firstCodingStandardError->getLine()] <=> [$secondCodingStandardError->getRelativeFilePath(), $secondCodingStandardError->getLine()];
        });
        return $errorMessages;
    }
    /**
     * @param FileDiff[] $fileDiffs
     * @return FileDiff[]
     */
    private function sortByFilePath(array $fileDiffs) : array
    {
        \uasort($fileDiffs, function (\Symplify\EasyCodingStandard\ValueObject\Error\FileDiff $firstFileDiff, \Symplify\EasyCodingStandard\ValueObject\Error\FileDiff $secondFileDiff) : int {
            return $firstFileDiff->getRelativeFilePath() <=> $secondFileDiff->getRelativeFilePath();
        });
        return $fileDiffs;
    }
}
