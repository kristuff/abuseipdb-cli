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
 * @version    0.9.9
 * @copyright  2020-2021 Kristuff
 */
namespace Kristuff\AbuseIPDB;

use Kristuff\AbuseIPDB\SilentApiHandler;
use Kristuff\Mishell\Console;

/**
 * Class AbuseIPDB
 * 
 * The main cli program
 */
class AbuseIPDBClient extends ShellUtils
{

    /**
     * @var string      
     */
    const SHORT_ARGUMENTS = "GLBK:C:d:R:c:m:l:pE:V:hvs:";

    /**
     * @var string      
     */
    const LONG_ARGUMENTS = ['config', 'list', 'blacklist', 'check:', 'check-block:', 'days:', 'report:', 'categories:', 'message:', 'limit:', 'plaintext', 'clear:','bulk-report:', 'help', 'verbose', 'score:','version'];
    
    /**
     * @var string      $version
     */
    const VERSION = 'v0.9.9'; 

    /**
     * @var SilentApiHandler  $api
     */
    private static $api = null; 

    /**
     * @var string      $keyPath
     */
    private static $keyPath = __DIR__ .'/../config/key.json';

    /**
     * The entry point of our app 
     * 
     * @access public
     * @static
     * @param array $arguments
     * 
     * @return void
     */
    public static function start($arguments)
    {

        // prints help, (no need install) ?
        if (self::inArguments($arguments, 'h', 'help')){
            self::printBanner();
            self::printHelp();
            self::safeExit();
        }

        // get key path from current script location (supposed in a bin folder)
        // and check for install then create a new instance of \ApiHandler
        self::$keyPath = dirname(get_included_files()[0]) . '/../config/key.json';
        self::validate( self::checkForInstall(), 'Key file missing.');
        try {
            self::$api = self::fromConfigFile(self::$keyPath);
        } catch (\Exception $e) {
            self::error($e->getMessage());
            self::printFooter();
            self::safeExit(1);
        }
    
        // required at least one valid argument
        self::validate( !empty($arguments), 'No valid arguments given. Run abuseipdb --help to get help.');

        // prints version?  (note: no short arg)
        if (self::inArguments($arguments, 'version', 'version')){
            self::printLogo();
            self::printVersion();
            self::safeExit();
        }

        // prints config ?
        if (self::inArguments($arguments, 'G', 'config')){
            self::printConfig();
            self::safeExit();
        } 

        // prints catgeories ?
        if (self::inArguments($arguments, 'L', 'list')){
            self::printCategories();
            self::safeExit();
        } 
        
        // check request ?
        if (self::inArguments($arguments, 'C', 'check')){
            self::checkIP($arguments);
            self::safeExit();
        }
       
        // check-block request ?
        if (self::inArguments($arguments, 'K', 'check-block')){
            self::checkBlock($arguments);
            self::safeExit();
        }

        // report request ?
        if (self::inArguments($arguments, 'R', 'report')){
            self::reportIP($arguments);
            self::safeExit();
        }

        // report request ?
        if (self::inArguments($arguments, 'V', 'bulk-report')){
            self::bulkReport($arguments);
            self::safeExit();
        }

        // report request ?
        if (self::inArguments($arguments, 'B', 'blacklist')){
            self::getBlacklist($arguments);
            self::safeExit();
        }

        // report request ?
        if (self::inArguments($arguments, 'E', 'clear')){
            self::clearIP($arguments);
            self::safeExit();
        }

        // no valid arguments given, close program
        Console::log();   
        self::error('invalid arguments. Run abuseipdb --help to get help.');
        self::printFooter();
        self::safeExit(1);
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
            throw new \InvalidArgumentException('The file [' . $configPath . '] does not exist.');
        }

        // check file is readable
        if (!is_readable($configPath)){
            throw new InvalidPermissionException('The file [' . $configPath . '] is not readable.');
        }

        $keyConfig = self::loadJsonFile($configPath);
        $selfIps = [];
        
        // Look for other optional config files in the same directory 
        $selfIpsConfigPath = pathinfo($configPath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . 'self_ips.json';
        if (file_exists($selfIpsConfigPath)){
            $selfIps = self::loadJsonFile($selfIpsConfigPath)->self_ips;
        }

        $app = new SilentApiHandler($keyConfig->api_key, $selfIps);
        
        return $app;
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
    protected static function loadJsonFile(string $filePath, bool $throwError = true)
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
        if (file_exists(self::$keyPath)) {
            return true;
        }
        
        // not installed
        self::printBanner();
        Console::log(' Your config key file was not found. Do you want to create it? ', 'white');
        $create =  Console::ask(' Press Y/y to create a config key file: ', 'white');
            
        if ($create == 'Y' || $create == 'y') {
            $key =     Console::ask(' - Please enter your api key: ', 'white');
            $create =  Console::ask(' A config file will be created in config/ directory. Press Y/y to continue: ', 'white');
            
            if ($create == 'Y' || $create == 'y') {
                $data = json_encode(['api_key' => $key]);
                
                if (file_put_contents(self::$keyPath, $data, LOCK_EX) === false){
                    self::error('An error occured during writing config file. Make sure to give the appropriate permissions do the config directory.');
                    return false;
                }

                // successfull. print message and exit to prevent errors with no arguments 
                Console::log();
                Console::log(Console::text('  ✓ ', 'green') . Console::text('Your config file has been successfully created.', 'white'));
                Console::log('    You can now use abuseipdb.', 'white');
                Console::log();
                self::safeExit();
            }
        }
        // no key file, not created
        return false;    
    }
 
    /**
     * Prints the help
     * 
     * @access protected
     * @static
     * 
     * @return void
     */
    protected static function printHelp()
    {
        Console::log(' ' . Console::text('SYNOPSIS:', 'white', 'underline')); 
        Console::log(' ' . Console::text('    abuseipdb -C ') . 
                           Console::text('ip', 'yellow') . 
                           Console::text(' [-d ') . 
                           Console::text('days', 'yellow') . 
                           Console::text('] [-v] [-l ') . 
                           Console::text('limit', 'yellow') . 
                           Console::text(']')); 

        Console::log(' ' . Console::text('    abuseipdb -K ') . 
                           Console::text('network', 'yellow') . 
                           Console::text(' [-d ') . 
                           Console::text('days', 'yellow') . 
                           Console::text(']')); 

        Console::log(' ' . Console::text('    abuseipdb -R ' .
                           Console::text('ip', 'yellow') . ' -c ' .
                           Console::text('categories', 'yellow') . ' -m ' .
                           Console::text('message', 'yellow'))); 

        Console::log(' ' . Console::text('    abuseipdb -V ' .
                           Console::text('path', 'yellow')));

        Console::log(' ' . Console::text('    abuseipdb -E ' .
                           Console::text('ip', 'yellow')));
                           
        Console::log(' ' . Console::text('    abuseipdb -B ') . 
                           Console::text('[-l ') . 
                           Console::text('limit', 'yellow') . 
                           Console::text('] [-s ') . 
                           Console::text('score', 'yellow') . 
                           Console::text('] [-p ') . 
                           Console::text('', 'yellow') . 
                           Console::text(']')); 

        Console::log(' ' . Console::text('    abuseipdb -L '));
        Console::log(' ' . Console::text('    abuseipdb -G '));
        Console::log(' ' . Console::text('    abuseipdb -h '));
                           
        Console::log();    
        Console::log(' ' . Console::text('OPTIONS:', 'white', 'underline')); 
        Console::log();
        Console::log(Console::text('   -h, --help', 'white')); 
        Console::log('       Prints the current help. If given, all next arguments are ignored.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -G, --config', 'white')); 
        Console::log('       Prints the current config. If given, all next arguments are ignored.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -L, --list', 'white')); 
        Console::log('       Prints the list report categories. If given, all next arguments are ignored.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -C, --check ', 'white') . Console::text('ip', 'yellow', 'underline')); 
        Console::log('       Performs a check request for the given IP address. A valid IPv4 or IPv6 address is required.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -K, --check-block ', 'white') . Console::text('network', 'yellow', 'underline')); 
        Console::log('       Performs a check-block request for the given network. A valid subnet (v4 or v6) denoted with ', 'lightgray');
        Console::log('       CIDR notation is required.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -d, --days ', 'white') . Console::text('days', 'yellow', 'underline')); 
        Console::log('       For a check or check-block request, defines the maxAgeDays. Min is 1, max is 365, default is 30.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -R, --report ', 'white') . Console::text('ip', 'yellow', 'underline')); 
        Console::log('       Performs a report request for the given IP address. A valid IPv4 or IPv6 address is required.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -V, --bulk-report ', 'white') . Console::text('path', 'yellow', 'underline')); 
        Console::log('       Performs a bulk-report request sending a csv file. A valid file name or full path is required.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -E, --clear ', 'white')); 
        Console::log('       Remove own reports for the given IP address. A valid IPv4 or IPv6 address is required.', 'lightgray');
        Console::log();
        Console::log(Console::text('   -c, --categories ', 'white') . Console::text('categories', 'yellow', 'underline')); 
        Console::log('       For a report request, defines the report category(ies). Categories must be separate by a comma.', 'lightgray');
        Console::log('       Some categories cannot be used alone. A category can be represented by its shortname or by its', 'lightgray');
        Console::log(Console::text('       id. Use ','lightgray')  . Console::text('abuseipdb -L', 'white') . Console::text(' to print the categories list.','lightgray'));
        Console::log();    
        Console::log(Console::text('   -m, --message ', 'white') . Console::text('message', 'yellow', 'underline')); 
        Console::log('       For a report request, defines the message to send with report. Message is required for all', 'lightgray');
        Console::log('       report requests.', 'lightgray');
        Console::log();
        Console::log(Console::text('   -B, --blacklist ', 'white')); 
        Console::log('       Performs a blacklist request. Default limit is 1000. This limit can ne changed with the', 'lightgray');
        Console::log('       ' . Console::text('--limit', 'white') . Console::text(' parameter. ', 'lightgray'));
        Console::log();    
        Console::log(Console::text('   -l, --limit ', 'white') . Console::text('limit', 'yellow', 'underline')); 
        Console::log('       For a blacklist request, defines the limit.', 'lightgray');
        Console::log('       For a check request with verbose flag, sets the max number of last reports displayed. Default is 10', 'lightgray');
        Console::log('       For a check-block request, sets the max number of IPs displayed. Default is 0 (no limit).', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -p, --plaintext ', 'white')); 
        Console::log('       For a blacklist request, output only ip list as plain text.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -s, --score ', 'white')); 
        Console::log('       For a blacklist request, sets the confidence score minimum. The confidence minimum ', 'lightgray');
        Console::log('       must be between 25 and 100. This parameter is subscriber feature (not honored otherwise, allways 100).', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -v, --verbose ', 'white')); 
        Console::log('       For a check request, display additional fields like the x last reports. This increases ', 'lightgray');
        Console::log(Console::text('       request time and response size. Max number of last reports displayed can be changed with the ', 'lightgray'));
        Console::log('       ' . Console::text('--limit', 'white') . Console::text(' parameter. ', 'lightgray'));
        Console::log();    
    }

    /**
     * Prints the current config
     * 
     * @access protected
     * @static
     * 
     * @return void
     */
    protected static function printConfig()
    {
        $conf = self::$api->getConfig();

        self::printTitle(Console::text('  ► Current configuration ', 'darkgray'));
        
        Console::log(Console::text('  api_key:[', 'white') . Console::text($conf['apiKey'], 'green') . Console::text(']', 'white'));
        Console::log(Console::text('  self_ips:', 'white'));
        
        foreach ($conf['selfIps'] as $ip) {
            Console::log(Console::text('    [', 'white') . Console::text($ip, 'green') . Console::text(']', 'white'));   
        }

        Console::log();   
        self::printFooter();
    }

    /**
     * Prints the report categories list
     * 
     * @access protected
     * @static
     * 
     * @return void
     */
    protected static function printCategories()
    {
        self::printTitle(Console::text('  ► Report categories list ', 'darkgray'));

        $categories = self::$api->getCategories();
        $rowHeaders = [
            Console::text('ShortName',      'darkgray') => 15, 
            Console::text('Id',             'darkgray') => 2, 
            Console::text('Full name',      'darkgray') => 18,
            Console::text('Can be alone?',  'darkgray') => 15
        ];
        Console::$verticalSeparator = '  ';
        Console::$verticalInnerSeparator = '  ';
        Console::log(Console::tableRowSeparator($rowHeaders, 'darkgray'));
        Console::log(Console::tableRow($rowHeaders));      
        Console::log(Console::tableRowSeparator($rowHeaders), 'darkgray');
        
        foreach ($categories as $cat) {
            $id = Console::text($cat[1], 'white');
            $standalone = $cat[3] ? Console::text('✓', 'green') . Console::text(' true ', 'lightgray') : 
                                    Console::text('✗', 'red')   . Console::text(' false', 'darkgray');
            $shortName =  Console::text($cat[0], 'white');
            $fullName =   Console::text($cat[2], 'lightgray');

            Console::log(
                Console::TableRowStart().  
                Console::TableRowCell( $shortName , 15).  
                Console::TableRowCell( $id , 2, Console::ALIGN_CENTER).  
                Console::TableRowCell( $fullName , 18).  
                Console::TableRowCell( $standalone , 15,  Console::ALIGN_CENTER)  
            );
        }
        //Console::log(Console::tableRowSeparator($rowHeaders), 'darkgray');
        Console::log();
        self::printFooter();
    }

    /**
     * Perform a report request 
     * 
     * @access protected
     * @static
     * @param array $arguments
     * 
     * @return void
     */
    protected static function reportIP(array $arguments)
    {
        $ip      = self::getArgumentValue($arguments,'R', 'report');
        $cats    = self::getArgumentValue($arguments,'c', 'categories');
        $message = self::getArgumentValue($arguments,'m', 'message');
        
        self::printTitle(Console::text('  ► Report IP: ', 'darkgray') . Console::text(escapeshellcmd($ip), 'white'));
        self::printTempMessage();

        // Peforms request 
        $timeStart = microtime(true);
        $report = self::$api->report($ip, $cats, $message)->getObject();     
        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart; // request time
        self::clearTempMessage();

        // check for errors / empty response
        if (self::printErrors($report)){
            self::printFooter();
            self::safeExit(1);
        }
        
        // ✓ Done: print reported IP and confidence score
        $score = empty($report->data->abuseConfidenceScore) ? 0 : $report->data->abuseConfidenceScore;
        $scoreColor = self::getScoreColor($score);
        Console::log(
            Console::text('   ✓', 'green') . 
            Console::text(' IP: [', 'white') .
            Console::text($ip, $scoreColor) .
            Console::text('] successfully reported', 'white')
        );
        Console::log(Console::text('     Confidence score: ', 'white') . self::getScoreBadge($score));

        Console::log();
        self::printFooter($time);
    }

    /**
     * Perform a bulk-report request 
     * 
     * @access protected
     * @static
     * @param array $arguments
     * 
     * @return void
     */
    protected static function bulkReport(array $arguments)
    {
        $fileName = self::getArgumentValue($arguments,'V', 'bulk-report');

        self::printTitle(Console::text('  ► Bulk report for file: ', 'darkgray') . Console::text(escapeshellcmd($fileName), 'white'));
        self::printTempMessage();

        // Peforms request 
        $timeStart = microtime(true);  
        $response = self::$api->bulkReport($fileName)->getObject();     
        $timeEnd = microtime(true);      
        $time = $timeEnd - $timeStart;  // request time
        self::clearTempMessage();

        // check for errors / empty response
        if (self::printErrors($response)){
            self::printFooter();
            self::safeExit(1);
        }

        // ✓ Done
        Console::log(
            Console::text('   Bulk report for file: [', 'white') .
            Console::text($fileName, 'lightyellow') .
            Console::text('] done!', 'white')
        );

        $nbErrorReports = isset($response->data->invalidReports) ? count($response->data->invalidReports) : 0;
        $nbSavedReports = isset($response->data->savedReports) ? $response->data->savedReports : 0;
        $savedColor = $nbSavedReports > 0 ? 'green' : 'red';
        $errorColor = $nbErrorReports > 0 ? 'red' : 'green';
        $savedIcon  = $nbSavedReports > 0 ? '✓' : '✗';
        $errorIcon  = $nbErrorReports > 0 ? '✗' : '✓';

        Console::log(Console::text('   ' . $savedIcon, $savedColor) . self::printResult(' Saved reports:    ', $nbSavedReports, $savedColor, '', false));
        Console::log(Console::text('   ' . $errorIcon, $errorColor) . self::printResult(' Invalid reports:  ', $nbErrorReports, $errorColor, '', false));

        if ($nbErrorReports > 0){
            $numberDiplayedReports = 0;
            $defaultColor = 'lightyellow'; // reset color for last reports
        
            foreach ($response->data->invalidReports as $report){
                $input = $report->input ? escapeshellcmd($report->input) : ''; // in case on blank line, IP is null
                $line  = Console::text('      →', 'red');
                $line .= self::printResult(' Input:         ', $input, $defaultColor, '', false);
                Console::log($line);
                self::printResult('        Error:         ', $report->error, $defaultColor);
                self::printResult('        Line number:   ', $report->rowNumber, $defaultColor);
                
                // counter
                $numberDiplayedReports++;
            }
        }
        Console::log();
        self::printFooter($time);
    }

    /**
     * Perform a clear-address request 
     * 
     * @access protected
     * @static
     * @param array $arguments
     * 
     * @return void
     */
    protected static function clearIP(array $arguments)
    {
        $ip      = self::getArgumentValue($arguments,'E', 'clear');

        self::printTitle(Console::text('  ► Clear reports for IP: ', 'darkgray') . Console::text(escapeshellcmd($ip), 'white'));

        // Peforms request 
        self::printTempMessage();
        $timeStart = microtime(true); // request startime 
        $response = self::$api->clearAddress($ip)->getObject();     
        $timeEnd = microtime(true);  // request end time 
        $time = $timeEnd - $timeStart; // request time
        self::clearTempMessage();

        // check for errors / empty response
        if (self::printErrors($response)){
            self::printFooter($time);
            self::safeExit(1);
        }

        // ✓ Done: print deleted report number 
        Console::log(
            Console::text('   ✓', 'green') . 
            Console::text(' Successfull clear request for IP: [', 'white') .
            Console::text($ip, 'lightyellow') .
            Console::text(']', 'white')
        );
        
        self::printResult('     Deleted reports: ', $response->data->numReportsDeleted ?? 0, 'lightyellow');
        Console::log();
        self::printFooter($time);
    }

    /**
     * Perform a blacklist request 
     * 
     * @access protected
     * @static
     * @param array $arguments
     * 
     * @return void
     */
    protected static function getBlacklist(array $arguments)
    {
        $plainText  = self::inArguments($arguments,'p','plaintext');  

        if (!$plainText){
            self::printTitle(Console::text('  ► Get Blacklist ', 'darkgray'));
        }

        $limit      = self::getNumericParameter($arguments,'l', 'limit', 1000);
        $scoreMin   = self::getNumericParameter($arguments,'s', 'score', 100);

        if (!$plainText){
            self::printTempMessage();
        }

        // do request 
        $timeStart = microtime(true);           // request startime 
        $response = self::$api->blacklist($limit, $plainText, $scoreMin);     // perform request
        $timeEnd = microtime(true);                         // request end time 
        $time = $timeEnd - $timeStart;                      // request time

        if (!$plainText){
            self::clearTempMessage();
        }

        // response could be json on error, while plaintext flag is set
        $decodedResponse = $response->getObject();
        
        if ($plainText && $response->hasError()){
            self::safeExit(1);
        }

        if (!$plainText && self::printErrors($decodedResponse)){
            self::printFooter($time);
            self::safeExit(1);
        }

        if ($plainText){
            // echo response "as is"
            Console::log($response->getPlaintext());

        } else {
            // print list
            self::printResult('  List generated at: ', self::getDate($decodedResponse->meta->generatedAt), 'lightyellow', '');
            Console::log();

            foreach ($decodedResponse->data as $report){
                $score = empty($report->abuseConfidenceScore) ? 0 : $report->abuseConfidenceScore;
                $defaultColor = self::getScoreColor($score);

                $line  = Console::text('    →', $defaultColor);
                $line .= self::printResult(' IP: ', $report->ipAddress, $defaultColor, '', false);
                $line .= self::printResult(' | Last reported at: ', self::getDate($report->lastReportedAt), $defaultColor, '', false);
                $line .= Console::text(' | Confidence score: ', 'white');
                $line .= self::getScoreBadge($score);
                Console::log($line);
            }
        
            // footer
            Console::log();
            self::printFooter($time);
        }
    }

    /**
     * Perform a check-block request 
     * 
     * @access protected
     * @static
     * @param array $arguments
     * 
     * @return void
     */
    protected static function checkBlock($arguments)
    {
        $network  = self::getArgumentValue($arguments,'K', 'check-block');

        self::printTitle(Console::text('  ► Check network: ', 'darkgray') . Console::text(escapeshellcmd($network), 'white') . Console::text('', 'darkgray'));

        $maxAge   = self::getNumericParameter($arguments, 'd', 'days', 30);
        $limit    = self::getNumericParameter($arguments,'l', 'limit', 0); // 0 mean no limit

        self::printTempMessage();

        $timeStart = microtime(true);                                       
        $check = self::$api->checkBlock($network, $maxAge)->getObject();
        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart; // request time
        self::clearTempMessage();

        // check for errors / empty response
        if (self::printErrors($check)){
            self::printFooter($time);
            self::safeExit(1);
        }

        self::printResult(Console::pad('   Network Address:', 23), $check->data->networkAddress, 'lightyellow');
        self::printResult(Console::pad('   Netmask:', 23), $check->data->netmask, 'lightyellow');
        self::printResult(Console::pad('   Min Address:', 23), $check->data->minAddress, 'lightyellow');
        self::printResult(Console::pad('   Max Address:', 23), $check->data->maxAddress, 'lightyellow');
        self::printResult(Console::pad('   Possible Hosts:', 23), $check->data->numPossibleHosts, 'lightyellow');
        self::printResult(Console::pad('   Address SpaceDesc:', 23), $check->data->addressSpaceDesc, 'lightyellow');

        // print reported addresses
        $nbReports = isset($check->data->reportedAddress) ? count($check->data->reportedAddress) : 0;
        
        if ($nbReports > 0){
            self::printResult(Console::pad('   Reported addresses:', 23), $nbReports, 'lightyellow');
            $numberDiplayedReports = 0;
            $defaultColor = 'lightyellow'; // reset color for last reports
        
            foreach ($check->data->reportedAddress as $report){
                $score = empty($report->abuseConfidenceScore) ? 0 : $report->abuseConfidenceScore;
                $defaultColor = self::getScoreColor($score); // color based on score

                $line  = Console::text('   →', $defaultColor);
                $line .= self::printResult(' IP: ', $report->ipAddress, $defaultColor, '', false);
                $line .= self::printResult(' Country: ', $report->countryCode , $defaultColor, '', false);
                $line .= Console::text(' | Confidence score: ', 'white');
                $line .= self::getScoreBadge($score);
                $line .= self::printResult(' Total reports: ', $report->numReports, $defaultColor, '', false);
                $line .= self::printResult(' Last reported at: ', self::getDate($report->mostRecentReport), $defaultColor, '', false);
                Console::log($line);

                // counter
                $numberDiplayedReports++;

                if ($numberDiplayedReports === $limit || $numberDiplayedReports === $nbReports) {
                    $line  = Console::text('      (', 'white');
                    $line .= Console::text($numberDiplayedReports, 'lightyellow');
                    $line .= Console::text('/', 'white');
                    $line .= Console::text($nbReports, 'lightyellow');
                    $line .= Console::text($numberDiplayedReports > 1 ? ' IPs displayed)': ' IP displayed)', 'white');
                    Console::log($line);
                    break;
                }
            }

        } else {
            // no reports
            $day = $maxAge > 1 ? 'in last '. $maxAge . ' days': ' today';
            Console::log( Console::text('    ✓', 'green') . Console::text(' No IP reported ' . $day));
        }

        // footer
        Console::log();
        self::printFooter($time);
    }

    /**
     * Perform a check request 
     * 
     * @access protected
     * @static
     * @param array $arguments
     * 
     * @return void
     */
    protected static function checkIP($arguments)
    {
        $ip                 = self::getArgumentValue($arguments,'C', 'check');

        self::printTitle(Console::text('  ► Check IP: ', 'darkgray') . Console::text(escapeshellcmd($ip), 'white') . Console::text('', 'darkgray'));

        $verbose            = self::inArguments($arguments,'v', 'verbose');
        $maxAge             = self::getNumericParameter($arguments, 'd', 'days', 30);
        $maxReportsNumber   = self::getNumericParameter($arguments,'l', 'limit', 10);

        self::printTempMessage();

        $timeStart = microtime(true);                                           
        $check = self::$api->check($ip, $maxAge, $verbose)->getObject();        
        $timeEnd = microtime(true);                                              
        $time = $timeEnd - $timeStart; // request time
        self::clearTempMessage();

        // check for errors / empty response
        if (self::printErrors($check)){
            self::printFooter($time);
            self::safeExit(1);
        }

        // score and data color (depending of abuseConfidenceScore)
        $score = empty($check->data->abuseConfidenceScore) ? 0 : $check->data->abuseConfidenceScore;
        $defaultColor = self::getScoreColor($score);
        $line = Console::text(Console::pad('   Confidence score:', 23), 'white');
        $line .= self::getScoreBadge($score);
        Console::log($line);
      
//      self::printResult('   isPublic', $check->data->isPublic, $defaultColor);
//      self::printResult('   ipVersion', $check->data->ipVersion, $defaultColor);
        $line = self::printResult(Console::pad('   Whitelisted:', 23), $check->data->isWhitelisted ? 'true': 'false', $defaultColor, '', false);
        $line .= $check->data->isWhitelisted ? Console::text(' ★', 'green') : ''; 
        Console::log($line);
       
        self::printResult(Console::pad('   Country code:', 23), $check->data->countryCode, $defaultColor);
        
        if (!empty($check->data->countryName)){
            self::printResult(Console::pad('   Country name:', 23), $check->data->countryName, $defaultColor);
        }

        self::printResult(Console::pad('   ISP:', 23), $check->data->isp, $defaultColor);

        if ($check->data->usageType){
            $line = self::printResult(Console::pad('   Usage type:', 23), $check->data->usageType, $defaultColor, '', false);
            $line .= $check->data->usageType === 'Reserved' ? Console::text(' ◆', 'green') : '';
            Console::log($line);
        }

        $hostames = implode(', ', array_filter($check->data->hostnames)) ?? null;
        if (!empty($hostames)){
            self::printResult(Console::pad('   Hostname(s):', 23), $hostames, $defaultColor);
        }

        self::printResult(Console::pad('   Domain:', 23), $check->data->domain, $defaultColor);

        $nbReport = $check->data->totalReports && is_numeric($check->data->totalReports) ? intval($check->data->totalReports) : 0;
        
        if ($nbReport > 0 ){
            $line  = self::printResult(Console::pad('   Total reports:', 23), $nbReport, $defaultColor, '', false);
            $line .= self::printResult(' from ', $check->data->numDistinctUsers, $defaultColor, '', false);
            $line .= Console::text($nbReport > 0 ? ' distinct users': ' user', 'white');
            Console::log($line);

        } else {
            // no reports
            $day = $maxAge > 1 ? 'in last '. $maxAge . ' days': ' today';
            Console::log( Console::text('   ✓', 'green') . Console::text(' Not reported ' . $day));
        }
        
        if (!empty($check->data->lastReportedAt)){
            self::printResult(Console::pad('   Last reported at:', 23), self::getDate($check->data->lastReportedAt), $defaultColor);
        }

        // print last reports
        if ($verbose){
            $nbLastReports = isset($check->data->reports) ? count($check->data->reports) : 0;
            
            if ($nbLastReports > 0){
                Console::log('   Last reports:', 'white');
                $numberDiplayedReports = 0;
                $defaultColor = 'lightyellow'; // reset color for last reports
            
                foreach ($check->data->reports as $lastReport){
                    $categories = [];
                    foreach (array_filter($lastReport->categories) as $catId){
                        $cat = self::$api->getCategoryNamebyId($catId)[0];
                        if ($cat !== false) {
                            $categories[] = $cat;
                        }
                    }

                    $line  = Console::text('    →', $defaultColor);
                    $line .= self::printResult(' reported at: ', self::getDate($lastReport->reportedAt), $defaultColor, '', false);
              //    $line .= self::printResult(' by user: ', $lastReport->reporterId, $defaultColor, '', false);
                    if (isset($lastReport->reporterCountryCode) && isset($lastReport->reporterCountryName)){
                        $line .= Console::text(' from: ', 'white');
                        $line .= self::printResult('', $lastReport->reporterCountryCode, $defaultColor, '', false);
                        $line .= Console::text(' - ', 'white');
                        $line .= self::printResult('', $lastReport->reporterCountryName, $defaultColor, '', false);
                    }
                    $line .= Console::text(' with categor' .  (count($categories) > 1 ? "ies: " : "y: "), 'white');
                    foreach ($categories as $key => $cat) {
                        $line .= Console::text($key==0 ? '' : ',' , 'white') . Console::text($cat, $defaultColor);
                    }
                    Console::log($line);

                    // counter
                    $numberDiplayedReports++;
                    if ($numberDiplayedReports === $maxReportsNumber || $numberDiplayedReports === $nbLastReports) {
                        $line  = Console::text('      (', 'white');
                        $line .= Console::text($numberDiplayedReports, $defaultColor);
                        $line .= Console::text('/', 'white');
                        $line .= Console::text($nbLastReports, $defaultColor);
                        $line .= Console::text($numberDiplayedReports > 1 ? ' reports displayed)': ' report displayed)', 'white');
                        Console::log($line);
                        break;
                    }
                }
            }
        }

        // footer
        Console::log();
        self::printFooter($time);
    }

}