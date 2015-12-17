<?php
namespace Gamegos\Sniffs\Functions;

/* Imports from CodeSnifferz */
use PEAR_Sniffs_Functions_FunctionDeclarationSniff;
use PHP_CodeSniffer_File;

/**
 * Gamegos.Functions.FunctionDeclaration Sniff
 * Customized some of PEAR.Functions.FunctionDeclaration rules.
 * - Added fixer for SpaceAfterFunction rule.
 * - Forwarded closures to Gamegos.Functions.OpeningFunctionBraceKernighanRitchie sniff.
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class FunctionDeclarationSniff extends PEAR_Sniffs_Functions_FunctionDeclarationSniff
{
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
