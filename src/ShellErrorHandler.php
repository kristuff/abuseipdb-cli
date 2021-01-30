<?php declare(strict_types=1);

/**
 *     _    _                    ___ ____  ____  ____
 *    / \  | |__  _   _ ___  ___|_ _|  _ \|  _ \| __ )
 *   / _ \ | '_ \| | | / __|/ _ \| || |_) | | | |  _ \
 *  / ___ \| |_) | |_| \__ \  __/| ||  __/| |_| | |_) |
 * /_/   \_\_.__/ \__,_|___/\___|___|_|   |____/|____/
 *
 * This file is part of Kristuff\AbsuseIPDB.
 *
 * (c) Kristuff <contact@kristuff.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version    0.9.10
 * @copyright  2020-2021 Kristuff
 */
namespace Kristuff\AbuseIPDB;

use Kristuff\Mishell\Console;
use Kristuff\Mishell\Program;

/**
 * Class ShellErrorHandler
 * 
 * Abstract base class for main cli program
 */
abstract class ShellErrorHandler extends ShellUtils
{
    /**
     * Check and print errors in API response. 
     * 
     * @access protected
     * @static
     * @param object     $response       
     * @param bool       $checkForEmpty     
     * 
     * @return bool     
     */
    protected static function hasErrors(object $response, bool $checkForEmpty = true): bool
    {
        return $checkForEmpty ? self::parseErrors($response) || self::checkForEmpty($response) : self::parseErrors($response);
    }

    /**
     * Check and print errors in API response. 
     * 
     * @access protected
     * @static
     * @param object     $response       
     * @param bool       $checkForEmpty     
     * 
     * @return bool     
     */
    private static function parseErrors(object $response): bool
    {
        if (isset($response) && isset($response->errors)){
            switch (self::$outputFormat){
                case self::OUTPUT_DEFAULT:
                    self::printFormattedErrors($response);
                    break;

                case self::OUTPUT_PLAINTEXT:
                    self::printPlainTextErrors($response);
                    break;

                case self::OUTPUT_JSON:
                    echo json_encode($response, JSON_PRETTY_PRINT);
                    break;
            }
            return true;
        }
        return false;    
    }

    /**
     * 
     * @access protected
     * @static
     * @param object     $response       
     * 
     * @return void     
     */
    protected static function printFormattedErrors(object $response): void
    {
        // top error badge    
        Console::log('  ' .   Console::text(' ERROR ','white', 'red'));

        $num = 0;
        // errors is an array, could have more than one error..
        foreach ($response->errors as $err){
            $num++;

            Console::log(Console::text('   ✗', 'red') .  self::printResult(' Number:    ', $num, 'lightyellow','', false));
            self::printResult('     Status:    ', $err->status ?? null, 'lightyellow','');    
            
            if (!empty($err->source) && !empty($err->source->parameter)){
                self::printResult('     Parameter: ', $err->source->parameter, 'lightyellow');    
            }
            self::printResult('     Title:     ', $err->title ?? null, 'lightyellow');    
            self::printResult('     Detail:    ', $err->detail ?? null, 'lightyellow');    

            // separate errors
            if (count($response->errors) > 1){
                Console::log('   ---');
            }
        }
        Console::log();           
    }

    /**
     * Print a single error
     * 
     * @access protected
     * @static
     * @param string    $error      The error message
     * 
     * @return void
     */
    protected static function error(string $error): void
    {
        if (self::isDefaultOuput()) {
            // ✗
            Console::log('  ' .   Console::text(' ERROR ','white', 'red'));
            Console::log(
                Console::text('   ✗', 'red') . 
                Console::text(' Detail:    ', 'white') . 
                Console::text($error, 'lightyellow') . 
                Console::text('', 'white')
            );    
            Console::log();
        }    
    }
    
    /**
     * Helper to validate a condition or exit with an error
     * 
     * @access protected
     * @static
     * @param bool      $condition      The condition to evaluate
     * @param string    $message        Error message
     * @param bool      $print          True to print error. Default is true
     * 
     * @return void
     */
    protected static function validate(bool $condition, string $message, bool $print = true): void
    {
        if ( !$condition ){
            if ($print && self::isDefaultOuput()) {
                Console::log();
                self::error($message);
                self::printFooter();
            }
            Program::exit(1);
        }
    }

    /**
     * Get numeric parameter and exit on error
     * 
     * @access protected
     * @static
     * @param array     $arguments
     * @param string    $shortArg           The short argument name
     * @param string    $longArg            The long argument name
     * @param int       $defaultValue
     * 
     * @return int
     */
    protected static function getNumericParameter(array $arguments, string $shortArg, string $longArg, int $defaultValue): int
    {
         if (self::inArguments($arguments,$shortArg, $longArg)){
            $val = self::getArgumentValue($arguments,$shortArg, $longArg);

            if (!is_numeric($val)){
                self::error("Invalid parameter: $longArg must be a numeric value.");
                self::printFooter();
                Program::exit(1);
            }
            return intval($val);
        }
        return $defaultValue;
    }

    /**
     * Check and print errors in API response. Null response object is considered as no errors
     * 
     * @access protected
     * @static
     * @param object     $response       
     * 
     * @return void     
     */
    protected static function printPlainTextErrors(object $response): void
    {
        foreach ($response->errors as $err){
            $text = 'Error: ';
            $text .= self::getErrorDetail($err, 'title');
            $text .= self::getErrorDetail($err, 'statuts');
            $text .= self::getErrorDetail($err, 'parameter', 'source');
            $text .= self::getErrorDetail($err, 'detail');
            $text .= PHP_EOL;
            echo $text;
        }
    }

    /**
     * Get error property if exist
     * 
     * @access protected
     * @static
     * @param object     $error       
     * @param string     $field       
     * @param string     $parent       
     * 
     * @return string     
     */
    private static function getErrorDetail(object $error, string $field, ?string $parent = null): string
    {
        if (!empty($parent)){
            return  !empty($error->$parent) && !empty($error->$parent->$field) ? ' ' . $field . ': ' . $error->$parent->$field : '';
        }

        return !empty($error->$field) ? ' ' . $field . ': ' . $error->$field : '';
    }

    /**
     * Check if response is empty
     * 
     * @access protected
     * @static
     * @param object     $response       
     * 
     * @return bool     
     */
    protected static function checkForEmpty(object $response): bool
    {
        // check for empty response ?
        if ( empty($response) || empty($response->data) ){
            self::error('An unexpected error occurred.');
            return true;
        }
        return false;    
    }
}