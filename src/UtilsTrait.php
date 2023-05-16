<?php declare(strict_types=1);

/**
 *       _                 ___ ___ ___  ___
 *  __ _| |__ _  _ ___ ___|_ _| _ \   \| _ )
 * / _` | '_ \ || (_-</ -_)| ||  _/ |) | _ \
 * \__,_|_.__/\_,_/__/\___|___|_| |___/|___/
 * 
 * This file is part of Kristuff\AbuseIPDB.
 *
 * (c) Kristuff <kristuff@kristuff.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version    0.9.20
 * @copyright  2020-2022 Kristuff
 */
namespace Kristuff\AbuseIPDB;

/**
 * Trait Utils
 */
trait UtilsTrait
{
    /**
     * helper function to get formatted date
     * 
     * @access private
     * @static
     * @param string    $date        The UTC date
     * 
     * @return string   Formated time
     */
    protected static function getDate($date): string
    {
        //2020-05-22T17:06:35+00:00
        return \DateTime::createFromFormat('Y-m-d\TH:i:s+', $date)->format('Y-m-d H:i:s');
    } 

    /**
     * helper function to get the color corresponding to given score:
     *   0    : green
     *   1-50 : yellow
     *   > 50 : lightred
     *  
     * @access protected
     * @static
     * @param mixed          $score    
     * 
     * @return string   
     * 
     */    
    protected static function getScoreColor($score): string
    {
        $score = intval($score);
        return $score > 50 ? 'lightred' : ($score > 0 ? 'yellow' : 'green') ;
    }

   
    /**
     * Helper function to get the value of an argument
     *  
     * @access protected
     * @static
     * @param array         $arguments      The list of arguments     
     * @param string        $shortArg       The short argument name
     * @param string        $longArg        The long argument name
     * 
     * @return string   
     * 
     */
    protected static function getArgumentValue(array $arguments, string $shortArg, string $longArg): string
    {
        return (array_key_exists($shortArg, $arguments) ? $arguments[$shortArg] : 
               (array_key_exists($longArg, $arguments) ? $arguments[$longArg]  : ''));
    }

    /**
     * helper function to check if a given argument is given
     * 
     * @access protected
     * @static
     * @param array     $arguments      The list of arguments     
     * @param string    $shortArg       The short argument name
     * @param string    $longArg        The long argument name
     * 
     * @return bool     True if the short or long argument exist in the arguments array, otherwise false
     */
    protected static function inArguments(array $arguments, string $shortArg, string $longArg): bool
    {
          return array_key_exists($shortArg, $arguments) || array_key_exists($longArg, $arguments);
    }

    /** 
     * Load and returns decoded Json from given file  
     *
     * @access public
     * @static
     * @param string    $filePath       The file's full path
     * @param bool      $throwError     Throw error on true or silent process. Default is true
     *  
	 * @return object|null 
     * @throws \Exception
     * @throws \LogicException
     */
    protected static function loadJsonFile(string $filePath, bool $throwError = true): ?object
    {
        // check file exists
        if (!file_exists($filePath) || !is_file($filePath)){
           if ($throwError) {
                throw new \Exception('Config file not found');
           }
           return null;  
        }

        // get and parse content
        $content = utf8_encode(file_get_contents($filePath));
        $json    = json_decode($content);

        // check for errors
        if ($json == null && json_last_error() != JSON_ERROR_NONE && $throwError) {
            throw new \LogicException(sprintf("Failed to parse config file Error: '%s'", json_last_error_msg()));
        }

        return $json;        
    }
}