<?php
namespace Gamegos\Sniffs\Commenting;

/* Imports from CodeSniffer */
use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Tokens;

/* Imports from PEAR Sniffs */
use PEAR_Sniffs_Commenting_FunctionCommentSniff;

/**
 * Customized some rules from PEAR.Commenting.FunctionComment.
 * - Added {@inheritdoc} validation for overrided methods.
 * - Added PHPUnit test class control for methods without doc comment.
 * @author Safak Ozpinar <safak@gamegos.com>
 */
class FunctionCommentSniff extends PEAR_Sniffs_Commenting_FunctionCommentSniff
{
    /**
     * Get class parents and interfaces.
     * Returns array of class and interface names or false if the class cannot be loaded.
     * @param  \PHP_CodeSniffer_File $phpcsFile
     * @param  int $stackPtr
     * @param  string $reason
     * @return array|bool
     */
    protected function getClassParentsAndInterfaces(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $reason)
    {
        $tokens  = $phpcsFile->getTokens();
        $nsStart = $phpcsFile->findNext(array(T_NAMESPACE), 0) + 2;
        $nsEnd   = $phpcsFile->findNext(array(T_SEMICOLON), $nsStart);
        $class   = '';
        for ($i = $nsStart; $i < $nsEnd; $i++) {
            $class .= $tokens[$i]['content'];
        }
        $class .= '\\' . $phpcsFile->getDeclarationName($phpcsFile->findNext(array(T_CLASS), $nsEnd));

        if (class_exists($class)) {
            return array_merge(class_parents($class), class_implements($class));
        }
        $warning = 'Need class loader to ' . $reason;
        $phpcsFile->addWarning($warning, $stackPtr, 'NeedClassLoader');
        return false;
    }

    /**
     * Check if a comment has a valid {@inheritdoc} annotation.
     * @param  \PHP_CodeSniffer_File $phpcsFile
     * @param  int $stackPtr
     * @param  int $commentStart
     * @param  int $commentEnd
     * @return bool
     */
    protected function isInheritdoc(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $commentStart, $commentEnd)
    {
        $commentString = $phpcsFile->getTokensAsString($commentStart, $commentEnd - $commentStart + 1);
        if (preg_match('/\{\@inheritdoc\}/', $commentString)) {
            $classes = $this->getClassParentsAndInterfaces($phpcsFile, $stackPtr, 'validate {@inheritdoc}');
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

    /**
     * Check if a method is a PHPUnit test class method.
     * @param  \PHP_CodeSniffer_File $phpcsFile
     * @param  int $stackPtr
     * @return bool
     */
    protected function isTestClassMethod(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $testClasses = array(
            'PHPUnit_Framework_TestCase'
        );

        $classes = $this->getClassParentsAndInterfaces($phpcsFile, $stackPtr, 'check for PHPUnit test class');
        if (false !== $classes) {
            foreach ($classes as $class) {
                if (in_array($class, $testClasses)
                    && stripos($phpcsFile->getDeclarationName($stackPtr), 'test') === 0
                ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $find   = PHP_CodeSniffer_Tokens::$methodPrefixes;
        $find[] = T_WHITESPACE;

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
            if (!$this->isTestClassMethod($phpcsFile, $stackPtr)) {
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

        $firstOnLine = $phpcsFile->findFirstOnLine(PHP_CodeSniffer_Tokens::$methodPrefixes, $stackPtr);
        if ($tokens[$commentStart]['column'] !== $tokens[$firstOnLine]['column']) {
            $error = 'Doc comment is not aligned correctly';
            $phpcsFile->addError($error, $commentStart, 'NotAligned');
        }

        if ($this->isInheritdoc($phpcsFile, $stackPtr, $commentStart, $commentEnd)) {
            return;
        }
        $this->processReturn($phpcsFile, $stackPtr, $commentStart);
        $this->processThrows($phpcsFile, $stackPtr, $commentStart);
        $this->processParams($phpcsFile, $stackPtr, $commentStart);
    }
}
