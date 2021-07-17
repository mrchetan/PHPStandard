<?php

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

// @codingStandardsIgnoreStart
class php_standard_Sniffs_Spacing_SpaceBetweenBlocksSniff implements Sniff
{
    public function register()
    {
        return array(
            T_IF,
            T_ELSE,
            T_ELSEIF,
            T_SWITCH,
            T_WHILE,
            T_FOR,
            T_FOREACH,
            T_CLOSE_CURLY_BRACKET
        );
    }

    public function process(File $phpcsFile, $stackPtr)
    {

        $tokens = $phpcsFile->getTokens();

        switch ($tokens[$stackPtr]['type']) {
            case 'T_ELSE':
            case 'T_IF':
            case 'T_ELSEIF':
            case 'T_SWITCH':
            case 'T_WHILE':
            case 'T_FOR':
            case 'T_FOREACH':
                $semicolonLine = $phpcsFile->findPrevious([T_SEMICOLON, T_OPEN_CURLY_BRACKET], $stackPtr - 1, null, false);
                $lineDiff = $tokens[$stackPtr]["line"] - $tokens[$semicolonLine]["line"];

                if ($lineDiff < 2 && $tokens[$semicolonLine]["type"] != "T_OPEN_CURLY_BRACKET") {
                    $error = 'Construct blocks should be separated by two lines';
                    $phpcsFile->addFixableError(
                        $error,
                        $stackPtr - 1,
                        self::class
                    );
                }

                break;
            case 'T_CLOSE_CURLY_BRACKET':

                if (!isset($tokens[$stackPtr]["scope_condition"])) {
                    break;
                }

                // Which condition this token belongs to
                $conditionPosition = $tokens[$stackPtr]["scope_condition"];
                $conditionType = $tokens[$conditionPosition]["type"];

                // Find next non white space token
                if ($conditionType === "T_ELSE" || $conditionType == "T_FOREACH" || $conditionType == "T_IF"
                    || $conditionType == "T_ELSE" || $conditionType == "T_WHILE"
                    || $conditionType == "T_SWITCH" || $conditionType == "T_FOR"
                ) {
                    // Find next non-white space (start of next statement/block)
                    $nonWhitePosition = $stackPtr + 1;
                    $onlyIfBlock = true;
                    $foundNonWhite = false;

                    if (isset($tokens[$nonWhitePosition])) {
                        while (true) {
                            if (($tokens[$nonWhitePosition]["type"] == "T_ELSEIF"
                                || $tokens[$nonWhitePosition]["type"] == "T_ELSE")) {
                                // This is not an only if block
                                $onlyIfBlock = false;
                            }

                            if ($tokens[$nonWhitePosition]["type"] == "T_WHITESPACE" && isset($tokens[$nonWhitePosition + 1])) {
                                $foundNonWhite = true;
                                $nonWhitePosition++;
                                continue;
                            } else {
                                break;
                            }
                        }

                        if (!$onlyIfBlock) {
                            // If this is not only if block, then break, because we need to check this condition
                            // for else statements
                            break;
                        }

                        $lineDiff = $tokens[$nonWhitePosition]["line"] - $tokens[$stackPtr]["line"];

                        if ($lineDiff < 2 && $tokens[$nonWhitePosition]["type"] != "T_CLOSE_CURLY_BRACKET" && $foundNonWhite) {
                            $error = 'Construct blocks should be separated by two lines';
                            $phpcsFile->addFixableError(
                                $error,
                                $stackPtr - 1,
                                self::class
                            );
                        }
                    }
                }
                break;
        }
    }
}
