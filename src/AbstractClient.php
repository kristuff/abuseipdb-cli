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

use Kristuff\AbuseIPDB\QuietApiHandler;
use Kristuff\Mishell\Program;

/**
 * Class AbstractClient
 * 
 * Abstract base class for main program
 */
abstract class AbstractClient extends ShellErrorHandler
{
    /**
     * @var string      
     */
    const SHORT_ARGUMENTS = "o:S:GLBK:C:d:R:c:m:l:E:V:hvs:";

    /**
     * @var string      
     */
    const LONG_ARGUMENTS = ['output:', 'save-key:', 'config', 'list', 'blacklist', 'check:', 'check-block:', 'days:', 'report:', 'categories:', 'message:', 'limit:', 'clear:',' bulk-report:', 'help', 'verbose', 'score:', 'version'];
    
    /**
     * @var string
     */
    const VERSION = 'v0.9.10'; 

    /**
     * @var QuietApiHandler
     */
    protected static $api = null; 

    /**
     * @var string
     */
    protected static $keyPath = __DIR__.'/../config/key.json';

    /**
     * @var array
     */
    protected static $basicCommands = [
        ['h',           'help',         'printHelp'],
        ['version',     'version',      'printVersion'],    // no short arg
        ['S',           'save-key',     'registerApiKey'],  
    ];
    
    /**
     * @var array
     */
    protected static $mainCommands = [
        ['G',           'config',       'printConfig'],
        ['L',           'list',         'printCategories'],
        ['C',           'check',        'checkIP'],
        ['K',           'check-block',  'checkBlock'],
        ['R',           'report',       'reportIP'],
        ['V',           'bulk-report',  'bulkReport'],
        ['B',           'blacklist',    'getBlacklist'],
        ['E',           'clear',        'clearIP'],
    ];

    /**
     * Parse command 
     * 
     * @access public 
     * @static
     * @param array     $arguments   
     * @param string    $keyPath     The configuration file path
     * 
     * @return bool     true is the command has been found, otherwise false
     */
    protected static function parseCommand(array $arguments, string $keyPath)
    {
        foreach(self::$basicCommands as $cmd){
            if (self::inArguments($arguments, $cmd[0], $cmd[1])){
                call_user_func(__NAMESPACE__.'\AbuseIPDBClient::'.$cmd[2], $cmd[2]=== 'registerApiKey' ? $arguments : null);
                return true;
            }
        }
        foreach(self::$mainCommands as $cmd){
            if (self::inArguments($arguments, $cmd[0], $cmd[1])){
                self::createHandler($keyPath);
                self::setOutputFormat($arguments);                    
                call_user_func(__NAMESPACE__.'\AbuseIPDBClient::'.$cmd[2], $arguments);
                return true;
            }
        }
        return false;
    }

    /**
     * Get and register output format
     *  
     * @access protected
     * @static
     * @param array         $arguments      The list of arguments     
     * 
     * @return string   
     * 
     */
    protected static function setOutputFormat(array $arguments)
    {
        $given = self::getArgumentValue($arguments, 'o', 'output') ?? 'default';
        $output = empty($given) ? 'default' : $given; 
        self::validate(in_array($output, ['default', 'json', 'plaintext']), 'Invalid output argument given.');
        self::$outputFormat = $output ;
    }

    /**
     * Check for install then create and register ApiHandler
     * 
     * @access public 
     * @static
     * @param string    $configPath     The configuration file path
     * 
     * @return void
     * @throws \InvalidArgumentException                        If the given file does not exist
     * @throws \Kristuff\AbuseIPDB\InvalidPermissionException   If the given file is not readable 
     */
    protected static function createHandler(string $keyPath)
    {
        self::$keyPath = $keyPath; 
        self::validate(self::checkForInstall(), 'Key file missing.');
        try {
            self::$api = self::fromConfigFile(self::$keyPath);
        } catch (\Exception $e) {
            self::error($e->getMessage());
            self::printFooter();
            Program::exit(1);
        }
    }
  
    /**
     * Check for install
     * 
     * @access protected
     * @static
     * 
     * @return bool
     */
    protected static function checkForInstall()
    {
        return file_exists(self::$keyPath);
    }

    /**
     * Get a new instance of ApiHandler with config stored in a Json file
     * 
     * @access public 
     * @static
     * @param string    $configPath     The configuration file path
     * 
     * @return \Kristuff\AbuseIPDB\ApiHandler
     * @throws \InvalidArgumentException                        If the given file does not exist
     * @throws \Kristuff\AbuseIPDB\InvalidPermissionException   If the given file is not readable 
     */
    public static function fromConfigFile(string $configPath)
    {
        // check file exists
        if (!file_exists($configPath) || !is_file($configPath)){
            throw new \InvalidArgumentException('The file ['.$configPath.'] does not exist.');
        }

        // check file is readable
        if (!is_readable($configPath)){
            throw new InvalidPermissionException('The file ['.$configPath.'] is not readable.');
        }

        $keyConfig = self::loadJsonFile($configPath);
        $selfIps = [];
        
        // Look for other optional config files in the same directory 
        $selfIpsConfigPath = pathinfo($configPath, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR.'self_ips.json';
        if (file_exists($selfIpsConfigPath)){
            $selfIps = self::loadJsonFile($selfIpsConfigPath)->self_ips;
        }

        $app = new QuietApiHandler($keyConfig->api_key, $selfIps);
        
        return $app;
    }

}