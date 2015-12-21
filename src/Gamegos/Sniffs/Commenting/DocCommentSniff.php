<?php
namespace Gamegos\Sniffs\Commenting;

/* Imports from CodeSniffer */
use PHP_CodeSniffer_File;

/* Imports from Generic sniffs */
use Generic_Sniffs_Commenting_DocCommentSniff;
use Gamegos\CodeSniffer\Helpers\ClassHelper;

/**
 * Customized Generic.Commenting.DocComment rules.
 * - [1] Ignored MissingShort rule for PHPUnit test class methods.
 * - [2] Changed MissingShort rule type from error to warning.
 * - [3] Removed rules for comments with long descriptions:
 *     • SpacingBetween
 *     • LongNotCapital
 *     • SpacingBeforeTags
 *     • ParamGroup
 *     • NonParamGroup
 *     • SpacingAfterTagGroup
 *     • TagValueIndent
 *     • ParamNotFirst
 *     • TagsNotGrouped
 */
class DocCommentSniff extends Generic_Sniffs_Commenting_DocCommentSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens       = $phpcsFile->getTokens();
        $commentStart = $stackPtr;
        $commentEnd   = $tokens[$stackPtr]['comment_closer'];

        $empty = array(
            T_DOC_COMMENT_WHITESPACE,
            T_DOC_COMMENT_STAR,
        );

        $short = $phpcsFile->findNext($empty, ($stackPtr + 1), $commentEnd, true);
        if ($short === false) {
            // No content at all.
            $error = 'Doc comment is empty';
            $phpcsFile->addError($error, $stackPtr, 'Empty');
            return;
        }

        // The first line of the comment should just be the /** code.
        if ($tokens[$short]['line'] === $tokens[$stackPtr]['line']) {
            $error = 'The open comment tag must be the only content on the line';
            $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'ContentAfterOpen');
            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->addNewline($stackPtr);
                $phpcsFile->fixer->addContentBefore($short, '* ');
                $phpcsFile->fixer->endChangeset();
            }
        }

        // The last line of the comment should just be the */ code.
        $prev = $phpcsFile->findPrevious($empty, ($commentEnd - 1), $stackPtr, true);
        if ($tokens[$prev]['line'] === $tokens[$commentEnd]['line']) {
            $error = 'The close comment tag must be the only content on the line';
            $fix   = $phpcsFile->addFixableError($error, $commentEnd, 'ContentBeforeClose');
            if ($fix === true) {
                $phpcsFile->fixer->addNewlineBefore($commentEnd);
            }
        }

        // Check for additional blank lines at the end of the comment.
        if ($tokens[$prev]['line'] < ($tokens[$commentEnd]['line'] - 1)) {
            $error = 'Additional blank lines found at end of doc comment';
            $fix   = $phpcsFile->addFixableError($error, $commentEnd, 'SpacingAfter');
            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();
                for ($i = ($prev + 1); $i < $commentEnd; $i++) {
                    if ($tokens[($i + 1)]['line'] === $tokens[$commentEnd]['line']) {
                        break;
                    }

                    $phpcsFile->fixer->replaceToken($i, '');
                }

                $phpcsFile->fixer->endChangeset();
            }
        }

        // Check for a comment description.
        if ($tokens[$short]['code'] !== T_DOC_COMMENT_STRING) {
            // [1] Ignored MissingShort rule for PHPUnit test class methods.
            if (!$this->isTestClassMethodComment($phpcsFile, $commentEnd)) {
                $error = 'Missing short description in doc comment';
                // [2] Changed MissingShort rule type from error to warning.
                $phpcsFile->addWarning($error, $stackPtr, 'MissingShort');
                return;
            }
        }

        // No extra newline before short description.
        if ($tokens[$short]['line'] !== ($tokens[$stackPtr]['line'] + 1)) {
            $error = 'Doc comment short description must be on the first line';
            $fix   = $phpcsFile->addFixableError($error, $short, 'SpacingBeforeShort');
            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();
                for ($i = $stackPtr; $i < $short; $i++) {
                    if ($tokens[$i]['line'] === $tokens[$stackPtr]['line']) {
                        continue;
                    } elseif ($tokens[$i]['line'] === $tokens[$short]['line']) {
                        break;
                    }

                    $phpcsFile->fixer->replaceToken($i, '');
                }

                $phpcsFile->fixer->endChangeset();
            }
        }

        // Account for the fact that a short description might cover
        // multiple lines.
        $shortContent = $tokens[$short]['content'];
        $shortEnd     = $short;
        for ($i = ($short + 1); $i < $commentEnd; $i++) {
            if ($tokens[$i]['code'] === T_DOC_COMMENT_STRING) {
                if ($tokens[$i]['line'] === ($tokens[$shortEnd]['line'] + 1)) {
                    $shortContent .= $tokens[$i]['content'];
                    $shortEnd      = $i;
                } else {
                    break;
                }
            }
        }

        if (preg_match('/\p{Lu}|\P{L}/u', $shortContent[0]) === 0) {
            $error = 'Doc comment short description must start with a capital letter';
            $phpcsFile->addError($error, $short, 'ShortNotCapital');
        }

        // [3] Removed rules for comments with long descriptions.
    }

    /**
     * Check i f a token is a test class method comment.
     * @param  \PHP_CodeSniffer_File $phpcsFile
     * @param  int $commentEnd
     * @return bool
     * @author Safak Ozpinar <safak@gamegos.com>
     */
    protected function isTestClassMethodComment(PHP_CodeSniffer_File $phpcsFile, $commentEnd)
    {
        $method = $this->getCommentMethod($phpcsFile, $commentEnd);
        if (false !== $method) {
            $classHelper = new ClassHelper($phpcsFile);
            return $classHelper->isTestClassMethod($method);
        }
        return false;
    }

    /**
     * Get the method token if current token is a method comment.
     * Returns false if no method found for the comment.
     * @param  \PHP_CodeSniffer_File $phpcsFile
     * @param  int $commentEnd
     * @return int|bool
     * @author Safak Ozpinar <safak@gamegos.com>
     */
    protected function getCommentMethod(PHP_CodeSniffer_File $phpcsFile, $commentEnd)
    {
        $tokens = $phpcsFile->getTokens();
        $next   = $phpcsFile->findNext(array(T_WHITESPACE), $commentEnd + 1, null, true);
        if (in_array($tokens[$next]['code'], \PHP_CodeSniffer_Tokens::$methodPrefixes)) {
            return $phpcsFile->findNext(array(T_FUNCTION), $next, $phpcsFile->findEndOfStatement($next));
        }
        return false;
    }
}
