<?php
namespace Gamegos\Sniffs\Formatting;

/* Imports from CodeSniffer */
use PHP_CodeSniffer_Sniff;
use PHP_CodeSniffer_File;

/**
 * Customized Generic.Formatting.DisallowMultipleStatements rule.
 * - Fixed adding 2 blank lines when applying this fixer with
 *   Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace fixer together.
 */
class DisallowMultipleStatementsSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return array(T_SEMICOLON);
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $prev = $phpcsFile->findPrevious(array(T_SEMICOLON, T_OPEN_TAG), ($stackPtr - 1));
        if ($prev === false || $tokens[$prev]['code'] === T_OPEN_TAG) {
            $phpcsFile->recordMetric($stackPtr, 'Multiple statements on same line', 'no');
            return;
        }

        // Ignore multiple statements in a FOR condition.
        if (isset($tokens[$stackPtr]['nested_parenthesis']) === true) {
            foreach ($tokens[$stackPtr]['nested_parenthesis'] as $bracket) {
                if (isset($tokens[$bracket]['parenthesis_owner']) === false) {
                    // Probably a closure sitting inside a function call.
                    continue;
                }

                $owner = $tokens[$bracket]['parenthesis_owner'];
                if ($tokens[$owner]['code'] === T_FOR) {
                    return;
                }
            }
        }

        /*
         * Fixed adding 2 blank lines when applying this fixer with
         * Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace fixer together.
         */
        if ($tokens[$prev]['line'] === $tokens[$stackPtr]['line']
            && $tokens[$prev + 1]['code'] != T_CLOSE_CURLY_BRACKET
        ) {
            $phpcsFile->recordMetric($stackPtr, 'Multiple statements on same line', 'yes');

            $error = 'Each PHP statement must be on a line by itself';
            $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'SameLine');
            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->addNewline($prev);
                if ($tokens[($prev + 1)]['code'] === T_WHITESPACE) {
                    $phpcsFile->fixer->replaceToken(($prev + 1), '');
                }

                $phpcsFile->fixer->endChangeset();
            }
        } else {
            $phpcsFile->recordMetric($stackPtr, 'Multiple statements on same line', 'no');
        }
    }
}
