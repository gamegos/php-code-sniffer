<?php
namespace Gamegos\Sniffs\Commenting;

// Imports from CodeSniffer.
use PHP_CodeSniffer;
use PHP_CodeSniffer_File;

// Imports from Squiz Sniffs.
use Squiz_Sniffs_Commenting_VariableCommentSniff;

/**
 * Customized some rules from Squiz.Commenting.VariableComment.
 * - Added 'bool' and 'int' into allowed variable types.
 * @author Safak Ozpinar
 */
class VariableCommentSniff extends Squiz_Sniffs_Commenting_VariableCommentSniff
{
    /**
     * @param  PHP_CodeSniffer_File $phpcsFile
     * @param  int $stackPtr
     * @see    Squiz_Sniffs_Commenting_VariableCommentSniff::processMemberVar()
     * @author Safak Ozpinar <safak@gamegos.com>
     */
    public function processMemberVar(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        PHP_CodeSniffer::$allowedTypes = array_unique(
            array_merge(
                PHP_CodeSniffer::$allowedTypes,
                array(
                    'int',
                    'bool'
                )
            )
        );
        parent::processMemberVar($phpcsFile, $stackPtr);
    }
}
