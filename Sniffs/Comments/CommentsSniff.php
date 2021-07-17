<?php

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

// @codingStandardsIgnoreStart
class php_standard_Sniffs_Comments_CommentsSniff implements Sniff
{

    private $fixer;
    private $stackPtr;
    private $position;

    public function register()
    {
        return [T_COMMENT];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $this->fixer = $phpcsFile->fixer;
        $this->position = $stackPtr;

        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr]["type"] == "T_COMMENT") {
            $content = $tokens[$stackPtr]["content"];

            if (substr($content, 0, 2) != "//") {
                // This is not a multiline comment
                return;
            }

            // This is a comment. If it is not a region comment, then it should have a space
            // at start and must begin if a capital letter if not a multi line comment

            // Check region comment
            if (!(strpos($content, "//region") === 0) && !(strpos($content, "//endregion") === 0) && trim($content) !== "//") {
                // Check multiline comment
                $multiline = false;


                if ($multiline) {
                    $regEx = "/\/\/ [A-Z0-9][^.]*/";
                } else {
                    $regEx = "/\/\/ [a-zA-Z0-9][^.]*/";
                }

                if (preg_match($regEx, $content) === 0) {
                    $error = 'Line comments must start with a capital letter';
                    $phpcsFile->addFixableError(
                        $error,
                        $stackPtr - 1,
                        self::class
                    );
                }
            }
        }

    }
}