<?php
namespace Gamegos\Sniffs\Functions;

// Imports from CodeSniffer.
use PHP_CodeSniffer_Sniff;
use PHP_CodeSniffer_File;
use Generic_Sniffs_Functions_OpeningFunctionBraceKernighanRitchieSniff as OpeningFunctionBraceKRSniff;

/**
 * Gamegos.Functions.OpeningFunctionBraceKernighanRitchieSniff Sniff
 * Customized some of Generic.Functions.OpeningFunctionBraceKernighanRitchie rules.
 * - Added fixer for SpaceAfterBracket rule.
 * - Added fixer for SpaceBeforeBrace rule.
 */
class OpeningFunctionBraceKernighanRitchieSniff extends OpeningFunctionBraceKRSniff
{
    /**
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int $stackPtr
     * @see   Generic_Sniffs_Functions_OpeningFunctionBraceKernighanRitchieSniff::process()
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        if (isset($tokens[$stackPtr]['scope_opener']) === false) {
            return;
        }

        $openingBrace = $tokens[$stackPtr]['scope_opener'];

        $next = $phpcsFile->findNext(T_WHITESPACE, ($openingBrace + 1), null, true);
        if ($tokens[$next]['line'] === $tokens[$openingBrace]['line']) {
            $error = 'Opening brace must be the last content on the line';
            $fix   = $phpcsFile->addFixableError($error, $openingBrace, 'ContentAfterBrace');
            if ($fix === true) {
                $phpcsFile->fixer->addNewline($openingBrace);
            }
        }

        // The end of the function occurs at the end of the argument list. Its
        // like this because some people like to break long function declarations
        // over multiple lines.
        $functionLine = $tokens[$tokens[$stackPtr]['parenthesis_closer']]['line'];
        $braceLine    = $tokens[$openingBrace]['line'];

        $lineDifference = ($braceLine - $functionLine);

        if ($lineDifference > 0) {
            $error = 'Opening brace should be on the same line as the declaration';
            $phpcsFile->addError($error, $openingBrace, 'BraceOnNewLine');
            $phpcsFile->recordMetric($stackPtr, 'Function opening brace placement', 'new line');
            return;
        }

        $closeBracket = $tokens[$stackPtr]['parenthesis_closer'];
        if ($tokens[($closeBracket + 1)]['code'] !== T_WHITESPACE) {
            $length = 0;
        } elseif ($tokens[($closeBracket + 1)]['content'] === "\t") {
            $length = '\t';
        } else {
            $length = strlen($tokens[($closeBracket + 1)]['content']);
        }

        if ($length !== 1) {
            $error = 'Expected 1 space after closing parenthesis; found %s';
            $data  = array($length);
            $fix   = $phpcsFile->addFixableError($error, $closeBracket, 'SpaceAfterBracket', $data);
            if ($fix === true) {
                if ($length === 0) {
                    $phpcsFile->fixer->addContent($closeBracket, ' ');
                } else {
                    $phpcsFile->fixer->replaceToken($closeBracket + 1, ' ');
                }
            }
            return;
        }

        $closeBrace = $tokens[$stackPtr]['scope_opener'];
        if ($tokens[($closeBrace - 1)]['code'] !== T_WHITESPACE) {
            $length = 0;
        } elseif ($tokens[($closeBrace - 1)]['content'] === "\t") {
            $length = '\t';
        } else {
            $length = strlen($tokens[($closeBrace - 1)]['content']);
        }

        if ($length !== 1) {
            $error = 'Expected 1 space before opening brace; found %s';
            $data  = array($length);
            $fix   = $phpcsFile->addFixableError($error, $openingBrace, 'SpaceBeforeBrace', $data);
            if ($fix === true) {
                if ($length === 0) {
                    $phpcsFile->fixer->addContentBefore($openingBrace, ' ');
                } else {
                    $phpcsFile->fixer->replaceToken($openingBrace - 1, ' ');
                }
            }
            return;
        }

        $phpcsFile->recordMetric($stackPtr, 'Function opening brace placement', 'same line');
    }
}
