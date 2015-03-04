<?php
namespace Gamegos\Sniffs\Functions;

// Imports from CodeSniffer.
use PEAR_Sniffs_Functions_FunctionDeclarationSniff;
use PHP_CodeSniffer_File;

/**
 * Gamegos.Functions.FunctionDeclaration Sniff
 * Customized some of Squiz.Functions.MultiLineFunctionDeclaration rules.
 * - Added fixer for Squiz.Functions.MultiLineFunctionDeclaration.SpaceAfterFunction rule.
 * - Forwarded closures to Gamegos.Functions.OpeningFunctionBraceKernighanRitchie sniff.
 */
class FunctionDeclarationSniff extends PEAR_Sniffs_Functions_FunctionDeclarationSniff
{
    /**
     * @param  PHP_CodeSniffer_File $phpcsFile
     * @param  int $stackPtr
     * @see    PEAR_Sniffs_Functions_FunctionDeclarationSniff::process()
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $spaces = 0;
        if ($tokens[($stackPtr + 1)]['content'] === $phpcsFile->eolChar) {
            $spaces = 'newline';
        } elseif ($tokens[($stackPtr + 1)]['code'] == T_WHITESPACE) {
            $spaces = strlen($tokens[($stackPtr + 1)]['content']);
        }

        if ($spaces !== 1) {
            $error = 'Expected 1 space after FUNCTION keyword; %s found';
            $data  = array($spaces);
            $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceAfterFunction', $data);
            if ($fix === true) {
                if ($spaces === 0) {
                    $phpcsFile->fixer->addContent($stackPtr, ' ');
                } else {
                    $phpcsFile->fixer->replaceToken(($stackPtr + 1), ' ');
                }
            }
        }
        parent::process($phpcsFile, $stackPtr);
    }

    /**
     * @param  PHP_CodeSniffer_File $phpcsFile
     * @param  int $stackPtr
     * @param  array $tokens
     * @see    PEAR_Sniffs_Functions_FunctionDeclarationSniff::processSingleLineDeclaration()
     * @see    Generic_Sniffs_Functions_OpeningFunctionBraceKernighanRitchieSniff::process()
     * @author Safak Ozpinar <safak@gamegos.com>
     */
    public function processSingleLineDeclaration(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $tokens)
    {
        if ($tokens[$stackPtr]['code'] === T_CLOSURE) {
            $sniff = new OpeningFunctionBraceKernighanRitchieSniff();
            $sniff->process($phpcsFile, $stackPtr);
        } else {
            parent::processSingleLineDeclaration($phpcsFile, $stackPtr, $tokens);
        }
    }
}
