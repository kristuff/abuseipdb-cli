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
 * @version    0.9.15
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
    const SHORT_ARGUMENTS = "o:GLBK:C:d:R:c:m:l:E:V:hvs:";

    /**
     * @var string      
     */
    const LONG_ARGUMENTS = ['output:', 'config', 'list', 'blacklist', 'check:', 'check-block:', 'days:', 'report:', 'categories:', 'message:', 'limit:', 'clear:',' bulk-report:', 'help', 'verbose', 'score:', 'version'];
    
    /**
     * @var string
     */
    const VERSION = 'v0.9.15'; 

    /**
     * @var QuietApiHandler
     */
    protected static $api = null; 

    /**
     * @var string
     */
    protected static $configPath = __DIR__.'/../config';

    /**
     * @var array
     */
    protected static $basicCommands = [
        ['h',           'help',         'printHelp'],
        ['version',     'version',      'printVersion'],    // no short arg
        ['L',           'list',         'printCategories'],
    ];
    
    /**
     * @var array
     */
    protected static $mainCommands = [
        ['G',           'config',       'printConfig'], // require handler 
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
     * 
     * @return bool     true is the command has been found, otherwise false
     */
    protected static function parseCommand(array $arguments): bool
    {
        foreach(self::$basicCommands as $cmd){
            if (self::inArguments($arguments, $cmd[0], $cmd[1])){
                call_user_func(__NAMESPACE__.'\AbuseIPDBClient::'.$cmd[2], null);
                return true;
            }
        }
        foreach(self::$mainCommands as $cmd){
            if (self::inArguments($arguments, $cmd[0], $cmd[1])){
                self::createHandler();
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
     * @return void   
     * 
     */
    protected static function setOutputFormat(array $arguments): void
    {
        $given = self::getArgumentValue($arguments, 'o', 'output') ?? 'default';
        $output = empty($given) ? 'default' : $given; 
        self::validate(in_array($output, ['default', 'json', 'plaintext']), 'Invalid output argument given.');
        self::$outputFormat = $output ;
    }

    /**
     * Create and register ApiHandler
     * 
     * @access protected 
     * @static
     * 
     * @return void
     * @throws \InvalidArgumentException                        If the given file does not exist
     * @throws \Kristuff\AbuseIPDB\InvalidPermissionException   If the given file is not readable 
     */
    protected static function createHandler(): void
    {
        try {
            $mainConfPath  = self::$configPath . DIRECTORY_SEPARATOR . 'conf.ini';
            $localConfPath = self::$configPath . DIRECTORY_SEPARATOR . 'local.ini';

            // Check main file exists and is readable
            // Even if a local file exists, main file must be here (throws ex otherwise)
            $mainConfigArray  = self::loadConfigFile($mainConfPath, true);
            $localConfigArray = self::loadConfigFile($localConfPath);

            $selfIps = self::extractSelfIpsFromConf($mainConfigArray, $localConfigArray);
            $apiKey  = self::extractApiKeyFromConf($mainConfigArray, $localConfigArray);
            
            self::$api =  new QuietApiHandler($apiKey, $selfIps);
        } catch (\Exception $e) {
            self::error($e->getMessage());
            self::printFooter();
            Program::exit(1);
        }
    }

    /**
     * Extract self ip list from configuration array
     * 
     * @access protected 
     * @static
     * @param array    $conf        The main configuration array
     * @param array    $local       The local configuration array
     * 
     * @return array
     */
    protected static function extractSelfIpsFromConf(array $conf, array $localConf): array
    {
        if (array_key_exists('self_ips', $localConf)){
            return array_map('trim', explode(',', $localConf['self_ips']));
        }
        if (array_key_exists('self_ips', $conf)){
            return array_map('trim', explode(',', $conf['self_ips']));
        }
        return [];
    }

    /**
     * Extract the api key from configuration array
     * 
     * @access protected 
     * @static
     * @param array    $conf        The configuration array
     * 
     * @return array
     */
    protected static function extractApiKeyFromConf(array $conf, $localConf): string
    {
        $key = '';

        if (array_key_exists('api_key', $localConf)){
            $key = $localConf['api_key'];
        }
        
        if (empty($key) && array_key_exists('api_key', $conf)){
            $key = $conf['api_key'];
        }

        if (empty($key)){
            throw new \RuntimeException('Api key is missing.');
        }

        return $key;
    }

    /**
     * Load a config file
     * 
     * @access protected 
     * @static
     * @param string    $path        The configuration file path
     * @param bool      $mandatory   If true, throw ex when the file does not exist.
     * 
     * @return array
     * @throws \RuntimeException                                
     */
    protected static function loadConfigFile(string $path, bool $mandatory = false): array
    {
        if (file_exists($path) && is_file($path)){

            // If main file or a local file is not readable then throws ex.
            if (!is_readable($path)){
                throw new \RuntimeException('The configuration file ['.$path.'] is not readable.');
            }

            $conf = parse_ini_file($path, false);  // load without sections...
            if ($conf === false){
                throw new \RuntimeException('Unable to read configuration file ['.$path.'].');
            }
            return $conf;
        }

        if ($mandatory){
            throw new \RuntimeException('The configuration file ['.$path.'] does not exist.');
        }
        return [];
    }
}