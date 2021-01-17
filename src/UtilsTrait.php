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
 * @version    0.9.8
 * @copyright  2020-2021 Kristuff
 */
namespace Kristuff\AbuseIPDB;

/**
 * Class Utils
 * 
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
    protected static function getDate($date)
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
    protected static function getScoreColor($score)
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
    protected static function getArgumentValue(array $arguments, string $shortArg, string $longArg)
    {
        return (array_key_exists($shortArg, $arguments) ? $arguments[$shortArg] : 
               (array_key_exists($longArg, $arguments) ? $arguments[$longArg]  : ''));
    }

    /**
     * helper function to check if a argument is given
     * 
     * @access protected
     * @static
     * @param array     $arguments      The list of arguments     
     * @param array     $shortArg       The short argument name
     * @param array     $longArg        The long argument name
     * 
     * @return bool     True if the short or long argument exist in the arguments array, otherwise false
     */
    protected static function inArguments($arguments, $shortArg, $longArg)
    {
          return array_key_exists($shortArg, $arguments) || array_key_exists($longArg, $arguments);
    }
}