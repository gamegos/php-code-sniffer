<?php
namespace Gamegos\Sniffs\Strings;

// Imports from CodeSniffer.
use PHP_CodeSniffer_Sniff;
use PHP_CodeSniffer_File;

/**
 * Gamegos.Strings.ConcatenationSpacing Sniff
 * 1) There must be only one space between the concatenation operator (.) and the strings being concatenated.
 * 2) Multiline string concatenations must be aligned.
 * @author Safak Ozpinar
 */
class ConcatenationSpacingSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * @return array
     * @see    PHP_CodeSniffer_Sniff::register()
     * @author Safak Ozpinar
     */
    public function register()
    {
        return array(T_STRING_CONCAT);
    }

    /**
     * @param  PHP_CodeSniffer_File $phpcsFile
     * @param  int $stackPtr
     * @see    PHP_CodeSniffer_Sniff::process()
     * @author Safak Ozpinar
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $previous = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);
        if ($tokens[$previous]['line'] !== $tokens[$stackPtr]['line']) {
            $before = 'newline';
        } elseif ($tokens[$stackPtr - 1]['code'] !== T_WHITESPACE) {
            $before = 0;
        } else {
            $before = $tokens[$stackPtr - 1]['length'];
        }

        $next = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr + 1, null, true);
        if ($tokens[$next]['line'] !== $tokens[$stackPtr]['line']) {
            $after = 'newline';
        } elseif ($tokens[$stackPtr + 1]['code'] !== T_WHITESPACE) {
            $after = 0;
        } else {
            $after = $tokens[$stackPtr + 1]['length'];
        }

        $phpcsFile->recordMetric($stackPtr, 'Spacing before string concat', $before);
        $phpcsFile->recordMetric($stackPtr, 'Spacing after string concat', $after);

        if ($before === 'newline') {
            $findExpected = function ($stackPtr) use ($phpcsFile, $tokens, & $findExpected) {
                // Find the last operator in the previous line.
                $prevLineOp = $phpcsFile->findPrevious(
                    array(
                        T_WHITESPACE, T_CONSTANT_ENCAPSED_STRING
                    ),
                    $stackPtr - 1,
                    null,
                    true
                );

                if ($tokens[$prevLineOp]['code'] === T_EQUAL) {
                    // Align to the assignment operator.
                    return $tokens[$prevLineOp]['column'] - 1;
                } elseif ($tokens[$prevLineOp]['code'] === T_STRING_CONCAT) {
                    // Align to the previous line.
                    $prev2 = $phpcsFile->findPrevious(T_WHITESPACE, $prevLineOp - 1, null, true);
                    if ($tokens[$prev2]['line'] !== $tokens[$prevLineOp]['line']) {
                        return $tokens[$prevLineOp]['column'] - 1;
                    }
                    return $findExpected($prevLineOp);
                }

                $startOfStmt = $phpcsFile->findStartOfStatement($stackPtr);
                if ($tokens[$startOfStmt]['code'] == T_RETURN) {
                    // Align to the return statement with 5 spaces.
                    return $tokens[$startOfStmt]['column'] + 4;
                }
                // Align to the start of the statement with 4 spaces.
                return $tokens[$startOfStmt]['column'] + 3;
            };

            $found = $tokens[$stackPtr]['column'] - 1;
            $expected = $findExpected($stackPtr);

            if ($found != $expected) {
                $message = 'Concat operator not aligned correctly; expected %s space(s) but found %s.';
                $fix = $phpcsFile->addFixableError($message, $stackPtr, 'NotAligned', array($expected, $found));
                if ($fix === true) {
                    $addBefore = $expected - $found;
                    if ($addBefore > 0) {
                        $padding = str_repeat(' ', $addBefore);
                        $phpcsFile->fixer->addContentBefore($stackPtr, $padding);
                    } else {
                        while ($addBefore < 0) {
                            $phpcsFile->fixer->replaceToken($stackPtr - 1, '');
                            $addBefore += 1;
                        }
                    }
                }
            }

            return;
        }

        if ($before === 1 && $after === 1) {
            return;
        }

        $message = 'Concat operator must be surrounded by a single space';
        $fix = $phpcsFile->addFixableError($message, $stackPtr, 'PaddingFound');
        if ($fix === true) {
            if ($tokens[$stackPtr - 1]['code'] === T_WHITESPACE) {
                $phpcsFile->fixer->replaceToken($stackPtr - 1, ' ');
            } else {
                $phpcsFile->fixer->addContent($stackPtr - 1, ' ');
            }

            if ($tokens[$stackPtr + 1]['code'] === T_WHITESPACE) {
                $phpcsFile->fixer->replaceToken($stackPtr + 1, ' ');
            } else {
                $phpcsFile->fixer->addContent($stackPtr, ' ');
            }
        }
    }
}
