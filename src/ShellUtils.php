<?php

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
use Kristuff\AbuseIPDB\ApiHandler;

/**
 * Class ShellUtils
 * 
 * Abstract base class for main cli program
 */
abstract class ShellUtils
{
    /**
     * helper functions
     */
    use UtilsTrait, ErrorsTrait;
  
    const OUTPUT_JSON       = 'json';
    const OUTPUT_DEFAULT    = 'default';
    const OUTPUT_PLAINTEXT  = 'plaintext';
    
    /**
     * @var string      $outputFormat
     */
    protected static $outputFormat = self::OUTPUT_DEFAULT; 

    /**
     * 
     */
    protected static function isDefaultOuput()
    {
        return self::$outputFormat === self::OUTPUT_DEFAULT; 
    }

    /**
     * Prints title action banner 
     * 
     * @access protected
     * @static
     * @param string    $title
     * 
     * @return void
     */
    protected static function printTitle(string $title)
    {
        if (self::isDefaultOuput()) {
            Console::log();
            Console::log($title);
            Console::log();
        }
    }
  
    /**
     * Print temp message during api request 
     * 
     * @access protected
     * @static
     * @param array $arguments
     * 
     * @return void
     */
    protected static function printTempMessage()
    {
        if (self::isDefaultOuput()) {
            Console::reLog(Console::text('   ? ', 'green') . Console::text('waiting for api response', 'white') . Console::text(' ... ', 'green'));
        }
    }

    /**
     * Clear the temp message set during api request 
     * 
     * @access protected
     * @static
     * @param array $arguments
     * 
     * @return void
     */
    protected static function clearTempMessage()
    {
        if (self::isDefaultOuput()) {
            // long blank string to overwrite previous message
            Console::reLog('                                                     ');
        }
    }

    /**
     * Print to banner 
     * 
     * @access protected
     * @static
     * @param array $arguments
     * 
     * @return void
     */
    protected static function printLogo()
    {
        if (self::isDefaultOuput()) {
            //Console::log("   _       _    _         __  __                   ", "darkgray");
            //Console::log("  | |___ _(_)__| |_ _  _ / _|/ _|                  ", "darkgray");
            //Console::log("  | / / '_| (_-<  _| || |  _|  _|                  ", "darkgray");
            //Console::log("  |_\_\_| |_/__/\__|\_,_|_| |_|                    ", "darkgray");
            Console::log("        _                 ___ ___ ___  ___        ", "darkgray");
            Console::log("   __ _| |__ _  _ ___ ___|_ _| _ \   \| _ )       ", "darkgray");
            Console::log("  / _` | '_ \ || (_-</ -_)| ||  _/ |) | _ \       ", "darkgray");
            Console::log("  \__,_|_.__/\_,_/__/\___|___|_| |___/|___/       ", "darkgray");
        }
    }

    /**
     * Print version 
     * 
     * @access protected
     * @static
     * @param array $arguments
     * 
     * @return void
     */
    protected static function printVersion()
    {
        self::printLogo();

        Console::log();
        Console::log(Console::text('  Kristuff/AbuseIPDB Client version: ', 'darkgray') . Console::text(AbuseIPDBClient::VERSION, 'lightgray'));
        Console::log(Console::text('  Kristuff/AbuseIPDB Core version:   ', 'darkgray') . Console::text(ApiHandler::VERSION, 'lightgray')); 
        Console::log(Console::text('  --------------------------------------------------', 'darkgray'));    
        Console::log(Console::text('  Released under the MIT licence', 'darkgray'));
        Console::log(Console::text('  Made with ', 'darkgray') . Console::text('♥', 'red') . Console::text(' in France', 'darkgray'));
        Console::log(
            Console::text('  © 2020-2021 Kristuff (', 'darkgray').
            Console::text('https://github.com/kristuff', 'darkgray', 'underlined').
            Console::text(')', 'darkgray')
        );
        Console::log(Console::text('  --------------------------------------------------', 'darkgray'));    
        Console::log();
    }

    /**
     * Print app banner
     * 
     * @access protected
     * @static
     * 
     * @return void
     */
    protected static function printBanner()
    {
        if (self::isDefaultOuput()) {
            Console::log();    
            Console::log( Console::text(' Kristuff\AbuseIPDB ', 'darkgray') . Console::text(' ' . AbuseIPDBClient::VERSION . ' ', 'white', 'blue')); 
            Console::log(Console::text(' Made with ', 'darkgray') . Console::text('♥', 'red') . Console::text(' in France', 'darkgray')); 
            Console::log(' © 2020-2021 Kristuff', 'darkgray'); 
            Console::log();  
        }  
    }

    /**
     * Print footer
     * 
     * @access protected
     * @static
     * 
     * @return void
     */
    protected static function printFooter(string $requestTime = '')
    {
        if (self::isDefaultOuput()) {
            if (!empty($requestTime)){
                $date_utc = new \DateTime("now", new \DateTimeZone("UTC"));
                Console::log(
                    Console::text('  Request time: ', 'darkgray') . Console::text($requestTime . 's', 'lightgray'). 
                    Console::text(' | UTC time: ', 'darkgray') . Console::text($date_utc->format('Y-m-d H:i:s'), 'lightgray')
                );
            }
            Console::log(Console::text('  ------------------------------------------------------------------------------------------------------', 'darkgray')); 
            Console::log(
                Console::text('  Kristuff\AbuseIPDB ', 'darkgray') . 
                Console::text(AbuseIPDBClient::VERSION, 'lightgray') . 
                Console::text(' | Made with ', 'darkgray') . 
                Console::text('♥', 'red') .
                Console::text(' in France | © 2020-2021 Kristuff (https://github.com/kristuff)', 'darkgray')
            ); 
            Console::log(); 
        }   
    }

    /**
     * Prints/gets a result value 
     * 
     * @access protected
     * @static
     * 
     * @return string
     */
    protected static function printResult($text, $value, string $foregroundColor = 'lightred', string $backgroundColor = '', bool $print = true)
    {
        
        // do not print null/blank values
        if (isset($value)){
            $line = Console::text($text, 'white') . Console::text($value, $foregroundColor, $backgroundColor); 
            if ($print && self::isDefaultOuput()){
                Console::log($line);
            }
            return $line;
        }
        return '';
    }
 
    /**
     * Prints score badge 
     * 
     * @access protected
     * @static
     * @param string    $text       
     * @param int       $score     
     * @param string    $textColor
     * 
     * @return string
     */
    protected static function getScoreBadge(int $score, string $padding = ' ')
    {
        $scoreforegroundColor = 'white';
        $scoreBackgroundColor = 'green';

        if (intval($score) > 0 ){
            $scoreforegroundColor = 'black';
            $scoreBackgroundColor = 'yellow';
        } 
        if (intval($score) > 50 ){
            $scoreforegroundColor = 'white';
            $scoreBackgroundColor = 'red';
        } 
  
        $badge = str_pad($score, 3, ' ',STR_PAD_LEFT); 
        return Console::text($padding.$badge.$padding, $scoreforegroundColor, $scoreBackgroundColor);
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
    protected static function hasErrors(object $response, bool $checkForEmpty = true )
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
    private static function parseErrors(object $response)
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
     * Check and print errors in API response. 
     * 
     * @access protected
     * @static
     * @param object     $response       
     * 
     * @return bool     
     */
    protected static function checkForEmpty(object $response)
    {
        // check for empty response ?
        if ( empty($response) || empty($response->data) ){
            self::error('An unexpected error occurred.');
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
    protected static function printFormattedErrors(object $response)
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
    protected static function error(string $error)
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
     * @return bool
     */
    protected static function validate(bool $condition, string $message, bool $print = true)
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
    protected static function getNumericParameter(array $arguments, string $shortArg, string $longArg, int $defaultValue)
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

}