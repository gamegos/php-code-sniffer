<?php
namespace Gamegos\Sniffs\WhiteSpace;

/* Imports from CodeSniffer */
use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Tokens;

/* Imports from Squiz Sniffs */
use Squiz_Sniffs_WhiteSpace_MemberVarSpacingSniff;

/**
 * Customized some of Squiz.WhiteSpace.MemberVarSpacing rules.
 * - [1] Expected no blank lines before the property which is the first defined element of a class.
 * - [2] Fixed fixing spaces before property definitions.
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class MemberVarSpacingSniff extends Squiz_Sniffs_WhiteSpace_MemberVarSpacingSniff
{
    /**
     * {@inheritdoc}
     */
    protected function processMemberVar(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $ignore   = PHP_CodeSniffer_Tokens::$methodPrefixes;
        $ignore[] = T_VAR;
        $ignore[] = T_WHITESPACE;

        $start = $stackPtr;
        $prev  = $phpcsFile->findPrevious($ignore, ($stackPtr - 1), null, true);
        if (isset(PHP_CodeSniffer_Tokens::$commentTokens[$tokens[$prev]['code']]) === true) {
            // Assume the comment belongs to the member var if it is on a line by itself.
            $prevContent = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($prev - 1), null, true);
            if ($tokens[$prevContent]['line'] !== $tokens[$prev]['line']) {
                // Check the spacing, but then skip it.
                $foundLines = ($tokens[$stackPtr]['line'] - $tokens[$prev]['line'] - 1);
                if ($foundLines > 0) {
                    $error = 'Expected 0 blank lines after member var comment; %s found';
                    $data  = array($foundLines);
                    $fix   = $phpcsFile->addFixableError($error, $prev, 'AfterComment', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->beginChangeset();
                        for ($i = ($prev + 1); $i <= $stackPtr; $i++) {
                            if ($tokens[$i]['line'] === $tokens[$stackPtr]['line']) {
                                break;
                            }

                            $phpcsFile->fixer->replaceToken($i, '');
                        }

                        $phpcsFile->fixer->addNewline($prev);
                        $phpcsFile->fixer->endChangeset();
                    }
                }//end if

                $start = $prev;
            }//end if
        }//end if

        // There needs to be 1 blank line before the var, not counting comments.
        if ($start === $stackPtr) {
            // No comment found.
            $first = $phpcsFile->findFirstOnLine(PHP_CodeSniffer_Tokens::$emptyTokens, $start, true);
            if ($first === false) {
                $first = $start;
            }
        } elseif ($tokens[$start]['code'] === T_DOC_COMMENT_CLOSE_TAG) {
            $first = $tokens[$start]['comment_opener'];
        } else {
            $first = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($start - 1), null, true);
            $first = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$commentTokens, ($first + 1));
        }

        $prev       = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($first - 1), null, true);
        $foundLines = ($tokens[$first]['line'] - $tokens[$prev]['line'] - 1);
        // [1] Expected no blank lines before the property which is the first defined element of a class.
        $expectedLines = $tokens[$prev]['code'] == T_OPEN_CURLY_BRACKET ? 0 : 1;
        if ($foundLines === $expectedLines) {
            return;
        }

        $error = 'Expected %s blank line before member var; %s found';
        $data  = array(
            $expectedLines,
            $foundLines
        );
        $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'Incorrect', $data);
        if ($fix === true) {
            $phpcsFile->fixer->beginChangeset();
            for ($i = ($prev + 1); $i < $first; $i++) {
                if ($tokens[$i]['line'] === $tokens[$prev]['line']) {
                    continue;
                }

                if ($tokens[$i]['line'] === $tokens[$first]['line']) {
                    break;
                }

                $phpcsFile->fixer->replaceToken($i, '');
            }

            $phpcsFile->fixer->endChangeset();
        }//end if

    }//end processMemberVar()
}
