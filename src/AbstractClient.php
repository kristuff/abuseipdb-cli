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
    const SHORT_ARGUMENTS = "o:GLBK:C:d:R:c:m:l:E:V:hvs:t:";

    /**
     * @var array      
     */
    const LONG_ARGUMENTS = ['output:', 'config', 'list', 'blacklist', 'check:', 'check-block:', 'days:', 'report:', 'categories:', 'message:', 'limit:', 'clear:',' bulk-report:', 'help', 'verbose', 'score:', 'version', 'timeout:'];
    
    /**
     * @var string
     */
    const VERSION = 'v0.9.20'; 

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
     * @return bool     true if the command has been found, otherwise false
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
                self::createHandler($arguments);
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
     */
    protected static function setOutputFormat(array $arguments): void
    {
        $given = self::getArgumentValue($arguments, 'o', 'output') ?? 'default';
        $output = empty($given) ? 'default' : $given; 
        self::validate(in_array($output, ['default', 'json', 'plaintext']), 'Invalid output argument given.');
        self::$outputFormat = $output;
    }

    /**
     * Create and register ApiHandler
     * 
     * @access protected 
     * @static
     * @param array     $arguments   
     * 
     * @return void
     */
    protected static function createHandler(array $arguments): void
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
            $timeout = self::extractNumericFromConf('timeout',$mainConfigArray, $localConfigArray, 0);
            
            // look into arguments for possible overwrite for timeout 
            if (self::inArguments($arguments, 't', 'timeout')){
                $timeout = self::getArgumentValue($arguments, 't', 'timeout');
            }

            if (!is_numeric($timeout)){
                throw new \RuntimeException('Invalid timeout argument, must be numeric.');
            }            

            self::$api =  new QuietApiHandler($apiKey, $selfIps, intval($timeout));
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
     * @param array    $localConf   The local configuration array
     * 
     * @return array
     */
    protected static function extractSelfIpsFromConf(array $conf, array $localConf): array
    {
        if (array_key_exists('self_ips', $localConf) && !empty($localConf['self_ips'])){
            return array_map('trim', explode(',', $localConf['self_ips']));
        }
        if (array_key_exists('self_ips', $conf) && !empty($conf['self_ips'])){
            return array_map('trim', explode(',', $conf['self_ips']));
        }
        return [];
    }

    /**
     * Extract the api key from configuration array
     * 
     * @access protected 
     * @static
     * @param array    $conf        The main configuration array
     * @param array    $localConf   The local configuration array
     * 
     * @return string
     * @throws \RuntimeException                                
     */
    protected static function extractApiKeyFromConf(array $conf, array $localConf): string
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
     * Extract numeric value from configuration array
     * 
     * @access protected 
     * @static
     * @param string   $key         The config key 
     * @param array    $conf        The main configuration array
     * @param array    $localConf   The local configuration array
     * @param int      $default     The default value if empty
     *  
     * @return int
     */
    protected static function extractNumericFromConf(string $key, array $conf, array $localConf, int $default): int
    {
        if (array_key_exists($key, $localConf) && is_numeric($localConf[$key])){
            return intval($localConf[$key]);
        }
        
        if (array_key_exists($key, $conf) && is_numeric($conf[$key])){
            return intval($conf[$key]);
        }

        return $default;
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