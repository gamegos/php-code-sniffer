<?php
namespace Gamegos\Sniffs\Commenting;

/* Imports from CodeSniffer */
use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Tokens;

/* Imports from PEAR Sniffs */
use PEAR_Sniffs_Commenting_FunctionCommentSniff;

/* Imports from Gamegos CodeSniffer */
use Gamegos\CodeSniffer\Helpers\ClassHelper;
use Gamegos\CodeSniffer\Helpers\RulesetHelper;

/**
 * Customized some rules from PEAR.Commenting.FunctionComment.
 * - [1] Added PHPUnit test class control for methods without doc comment.
 * - [2] Added {@inheritdoc} validation for overrided methods.
 * - [3] Removed MissingParamComment, MissingReturn, SpacingAfterParamType and SpacingAfterParamName rules.
 * - [4] Ignored MissingParamTag rule for PHPUnit test class methods.
 */
class FunctionCommentSniff extends PEAR_Sniffs_Commenting_FunctionCommentSniff
{
    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $find   = PHP_CodeSniffer_Tokens::$methodPrefixes;
        $find[] = T_WHITESPACE;

        $classHelper = new ClassHelper($phpcsFile);

        $commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            // Inline comments might just be closing comments for
            // control structures or functions instead of function comments
            // using the wrong comment type. If there is other code on the line,
            // assume they relate to that code.
            $prev = $phpcsFile->findPrevious($find, ($commentEnd - 1), null, true);
            if ($prev !== false && $tokens[$prev]['line'] === $tokens[$commentEnd]['line']) {
                $commentEnd = $prev;
            }
        }

        if ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
            && $tokens[$commentEnd]['code'] !== T_COMMENT
        ) {
            // [1] Added PHPUnit test class control for methods without doc comment.
            if (!$classHelper->isTestClassMethod($stackPtr)) {
                $phpcsFile->addError('Missing function doc comment', $stackPtr, 'Missing');
                $phpcsFile->recordMetric($stackPtr, 'Function has doc comment', 'no');
            }
            return;
        } else {
            $phpcsFile->recordMetric($stackPtr, 'Function has doc comment', 'yes');
        }

        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            $phpcsFile->addError('You must use "/**" style comments for a function comment', $stackPtr, 'WrongStyle');
            return;
        }

        if ($tokens[$commentEnd]['line'] !== ($tokens[$stackPtr]['line'] - 1)) {
            $error = 'There must be no blank lines after the function comment';
            $phpcsFile->addError($error, $commentEnd, 'SpacingAfter');
        }

        $commentStart = $tokens[$commentEnd]['comment_opener'];
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if ($tokens[$tag]['content'] === '@see') {
                // Make sure the tag isn't empty.
                $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
                if ($string === false || $tokens[$string]['line'] !== $tokens[$tag]['line']) {
                    $error = 'Content missing for @see tag in function comment';
                    $phpcsFile->addError($error, $tag, 'EmptySees');
                }
            }
        }

        // [2] Added {@inheritdoc} validation for overrided methods.
        if ($this->validateInheritdoc($phpcsFile, $stackPtr, $commentStart, $commentEnd)) {
            return;
        }

        // [3] Removed MissingParamComment, MissingReturn, SpacingAfterParamType and SpacingAfterParamName rules.
        $rulesetHelper = new RulesetHelper($phpcsFile);
        $rulesetHelper->setRuleSeverity('Gamegos.Commenting.FunctionComment.MissingParamComment', 0);
        $rulesetHelper->setRuleSeverity('Gamegos.Commenting.FunctionComment.MissingReturn', 0);
        $rulesetHelper->setRuleSeverity('Gamegos.Commenting.FunctionComment.SpacingAfterParamType', 0);
        $rulesetHelper->setRuleSeverity('Gamegos.Commenting.FunctionComment.SpacingAfterParamName', 0);

        // [4] Ignored MissingParamTag rule for PHPUnit test class methods.
        if ($classHelper->isTestClassMethod($stackPtr)) {
            $rulesetHelper->setRuleSeverity('Gamegos.Commenting.FunctionComment.MissingParamTag', 0);

            $this->processReturn($phpcsFile, $stackPtr, $commentStart);
            $this->processThrows($phpcsFile, $stackPtr, $commentStart);
            $this->processParams($phpcsFile, $stackPtr, $commentStart);

            $rulesetHelper->restore();
        } else {
            $this->processReturn($phpcsFile, $stackPtr, $commentStart);
            $this->processThrows($phpcsFile, $stackPtr, $commentStart);
            $this->processParams($phpcsFile, $stackPtr, $commentStart);
        }
    }

    /**
     * Check if a comment has a valid 'inheritdoc' annotation.
     * @param  \PHP_CodeSniffer_File $phpcsFile
     * @param  int $stackPtr
     * @param  int $commentStart
     * @param  int $commentEnd
     * @return bool
     * @author Safak Ozpinar <safak@gamegos.com>
     */
    protected function validateInheritdoc(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $commentStart, $commentEnd)
    {
        $classHelper = new ClassHelper($phpcsFile);

        $commentString = $phpcsFile->getTokensAsString($commentStart, $commentEnd - $commentStart + 1);
        if (preg_match('/\{\@inheritdoc\}/', $commentString)) {
            $classes = $classHelper->getClassParentsAndInterfaces($stackPtr, 'validate {@inheritdoc}');
            if (false !== $classes) {
                $method = $phpcsFile->getDeclarationName($stackPtr);
                foreach ($classes as $class) {
                    if (method_exists($class, $method)) {
                        return true;
                    }
                }
                $error = 'No overrided method found for {@inheritdoc} annotation';
                $phpcsFile->addError($error, $commentStart, 'InvalidInheritdoc');
            } else {
                return true;
            }
        }
        return false;
    }
}
