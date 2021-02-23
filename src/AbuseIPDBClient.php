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
 * @version    0.9.12
 * @copyright  2020-2021 Kristuff
 */
namespace Kristuff\AbuseIPDB;

use Kristuff\Mishell\Console;
use Kristuff\Mishell\Program;

/**
 * Class AbuseIPDB
 * 
 * The main cli program
 */
class AbuseIPDBClient extends AbstractClient
{
    /**
     * Helper methods
     */
    use CheckTrait, CheckBlockTrait, BulkReportTrait;

    /**
     * The entry point of our app 
     * 
     * @access public
     * @static
     * @param array     $arguments
     * @param string    $keyPath        The key file path
     * 
     * @return void
     */
    public static function start(array $arguments, string $keyPath): void
    {
        // required at least one valid argument
        self::$keyPath = $keyPath; 
        self::validate( !empty($arguments), 'No valid arguments given. Run abuseipdb --help to get help.');
        if (!self::parseCommand($arguments, $keyPath)) {
            self::error('Invalid arguments. Run abuseipdb --help to get help.');
            self::printFooter();
            Program::exit(1);
        }
        Program::exit(0);
    }
   
    /**
     * Register API key in a config file
     *  
     * @access protected
     * @static
     * 
     * @return bool
     */
    protected static function registerApiKey($arguments): void
    {
        self::printTitle(Console::text('  ► Register API key ', 'darkgray'));
        
        $key = self::getArgumentValue($arguments,'S', 'save-key');
        
        if (empty($key)){
            self::error('Null or invalid key argument.');
            self::printFooter();
            Program::exit(1);
        }

        $data = json_encode(['api_key' => $key]);
       
        if (file_put_contents(self::$keyPath, $data, LOCK_EX) === false){
            self::error('An error occurred when writing config file. Make sure to give the appropriate permissions to the config directory.');
            self::printFooter();
            Program::exit(1);
        }
        Console::log(Console::text('  ✓ ', 'green') . Console::text('Your config key file has been successfully created.', 'white'));
        Console::log();   
        self::printFooter();
        Program::exit();
    }
 
    /**
     * Prints the help
     * 
     * @access protected
     * @static
     * 
     * @return void
     */
    protected static function printHelp(): void
    {
        self::printBanner();

        Console::log(' ' . Console::text('SYNOPSIS:', 'white', 'underline')); 
        Console::log(' ' . Console::text('    abuseipdb -C ') . 
                           Console::text('IP', 'yellow') . 
                           Console::text(' [-d ') . 
                           Console::text('DAYS', 'yellow') . 
                           Console::text('] [-v] [-l ') . 
                           Console::text('LIMIT', 'yellow') . 
                           Console::text('] [-o ') . 
                           Console::text('FORMAT', 'yellow') . 
                           Console::text(']')); 

        Console::log(' ' . Console::text('    abuseipdb -K ') . 
                           Console::text('NETWORK', 'yellow') . 
                           Console::text(' [-d ') . 
                           Console::text('DAYS', 'yellow') . 
                           Console::text('] [-o ') . 
                           Console::text('FORMAT', 'yellow') . 
                           Console::text(']')); 

        Console::log(' ' . Console::text('    abuseipdb -R ') .
                           Console::text('IP', 'yellow') . ' -c ' .
                           Console::text('CATEGORIES', 'yellow') . ' -m ' .
                           Console::text('MESSAGE', 'yellow') .
                           Console::text(' [-o ') . 
                           Console::text('FORMAT', 'yellow') . 
                           Console::text(']')); 

        Console::log(' ' . Console::text('    abuseipdb -V ') .
                           Console::text('FILE', 'yellow') .
                           Console::text(' [-o ') . 
                           Console::text('FORMAT', 'yellow') . 
                           Console::text(']')); 

        Console::log(' ' . Console::text('    abuseipdb -E ') .
                           Console::text('IP', 'yellow').
                           Console::text(' [-o ') . 
                           Console::text('FORMAT', 'yellow') . 
                           Console::text(']')); 
                           
        Console::log(' ' . Console::text('    abuseipdb -B ') . 
                           Console::text('[-l ') . 
                           Console::text('LIMIT', 'yellow') . 
                           Console::text('] [-s ') . 
                           Console::text('SCORE', 'yellow') . 
                           Console::text('] [-o ') . 
                           Console::text('FORMAT', 'yellow') . 
                           Console::text(']')); 

        Console::log(' ' . Console::text('    abuseipdb -S ' .
                           Console::text('KEY', 'yellow')));

        Console::log(' ' . Console::text('    abuseipdb -L | -G | -h | --version'));
                           
        Console::log();    
        Console::log(' ' . Console::text('OPTIONS:', 'white', 'underline')); 
        Console::log();
        Console::log(Console::text('   -h, --help', 'white')); 
        Console::log('       Prints the current help.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -G, --config', 'white')); 
        Console::log('       Prints the current config.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -L, --list', 'white')); 
        Console::log('       Prints the list report categories.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -C, --check ', 'white') . Console::text('IP', 'yellow', 'underline')); 
        Console::log('       Performs a check request for the given IP address. A valid IPv4 or IPv6 address is required.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -K, --check-block ', 'white') . Console::text('NETWORK', 'yellow', 'underline')); 
        Console::log('       Performs a check-block request for the given network. A valid subnet (v4 or v6) denoted with ', 'lightgray');
        Console::log('       CIDR notation is required.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -d, --days ', 'white') . Console::text('DAYS', 'yellow', 'underline')); 
        Console::log('       For a check or check-block request, defines the maxAgeDays. Min is 1, max is 365, default is 30.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -R, --report ', 'white') . Console::text('IP', 'yellow', 'underline')); 
        Console::log('       Performs a report request for the given IP address. A valid IPv4 or IPv6 address is required.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -V, --bulk-report ', 'white') . Console::text('FILE', 'yellow', 'underline')); 
        Console::log('       Performs a bulk-report request sending a csv file. A valid file name or full path is required.', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -E, --clear ', 'white')); 
        Console::log('       Remove own reports for the given IP address. A valid IPv4 or IPv6 address is required.', 'lightgray');
        Console::log();
        Console::log(Console::text('   -c, --categories ', 'white') . Console::text('CATEGORIES', 'yellow', 'underline')); 
        Console::log('       For a report request, defines the report category(ies). Categories must be separate by a comma.', 'lightgray');
        Console::log('       Some categories cannot be used alone. A category can be represented by its shortname or by its', 'lightgray');
        Console::log(Console::text('       id. Use ','lightgray')  . Console::text('abuseipdb -L', 'white') . Console::text(' to print the categories list.','lightgray'));
        Console::log();    
        Console::log(Console::text('   -m, --message ', 'white') . Console::text('MESSAGE', 'yellow', 'underline')); 
        Console::log('       For a report request, defines the message to send with report. Message is required for all', 'lightgray');
        Console::log('       report requests.', 'lightgray');
        Console::log();
        Console::log(Console::text('   -B, --blacklist ', 'white')); 
        Console::log('       Performs a blacklist request. Default limit is 1000. This limit can ne changed with the', 'lightgray');
        Console::log('       ' . Console::text('--limit', 'white') . Console::text(' parameter. ', 'lightgray'));
        Console::log();    
        Console::log(Console::text('   -l, --limit ', 'white') . Console::text('LIMIT', 'yellow', 'underline')); 
        Console::log('       For a blacklist request, defines the limit.', 'lightgray');
        Console::log('       For a check request with verbose flag, sets the max number of last reports displayed. Default is 10', 'lightgray');
        Console::log('       For a check-block request, sets the max number of IPs displayed. Default is 0 (no limit).', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -o, --output ', 'white') . Console::text('FORMAT', 'yellow', 'underline')); 
        Console::log('       Defines the output format for API requests. Default is a colorized report, possible formats are', 'lightgray');
        Console::log('       '. Console::text('json', 'yellow', 'underline') . ' or ' . Console::text('plaintext', 'yellow', 'underline') . '. Plaintext option prints partial response (blacklist: IPs list, ');
        Console::log('       check or report: confidence score only, check-block: reported IPs list with confidence score, ', 'lightgray');
        Console::log('       bulk-report: number of saved reports, clear: number of deleted reports).', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -s, --score ', 'white'). Console::text('SCORE', 'yellow', 'underline')); 
        Console::log('       For a blacklist request, sets the confidence score minimum. The confidence minimum ', 'lightgray');
        Console::log('       must be between 25 and 100. This parameter is subscriber feature (not honored otherwise, allways 100).', 'lightgray');
        Console::log();    
        Console::log(Console::text('   -v, --verbose ', 'white')); 
        Console::log('       For a check request, display additional fields like the x last reports. This increases ', 'lightgray');
        Console::log(Console::text('       request time and response size. Max number of last reports displayed can be changed with the ', 'lightgray'));
        Console::log('       ' . Console::text('--limit', 'white') . Console::text(' parameter. ', 'lightgray'));
        Console::log();    
        Console::log(Console::text('   --version', 'white')); 
        Console::log('       Prints the current version.', 'lightgray');
        Console::log(); 
        Console::log(Console::text('   -S, --save-key ', 'white') . Console::text('KEY', 'yellow', 'underline')); 
        Console::log('       Save the given API key in the configuration file. Requires writing permissions on the config directory. ', 'lightgray');
        Console::log(); 
    }

    /**
     * Prints the current config and exit
     * 
     * @access protected
     * @static
     * 
     * @return void
     */
    protected static function printConfig(): void
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
    protected static function printCategories(): void
    {
        self::printTitle(Console::text('  ► Report categories list ', 'darkgray'));

        $categories = ApiHandler::getCategories();
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
    protected static function reportIP(array $arguments): void
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
        if (self::hasErrors($report)){
            self::printFooter();
            Program::exit(1);
        }
               
        // ✓ Done: print reported IP and confidence score
        $score = empty($report->data->abuseConfidenceScore) ? 0 : $report->data->abuseConfidenceScore;
        $scoreColor = self::getScoreColor($score);

        switch (self::$outputFormat){
            case self::OUTPUT_JSON:
                echo json_encode($report, JSON_PRETTY_PRINT);
                break;
       
            case self::OUTPUT_DEFAULT:  
                Console::log(
                    Console::text('   ✓', 'green').Console::text(' IP: [', 'white') .
                    Console::text($ip, $scoreColor).Console::text('] successfully reported', 'white')
                );
                Console::log(Console::text('     Confidence score: ', 'white').self::getScoreBadge($score));
                Console::log();
                self::printFooter($time);
                break;

            case self::OUTPUT_PLAINTEXT:
                echo $score.PHP_EOL;
                break;

        }
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
    protected static function bulkReport(array $arguments): void
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
        if (self::hasErrors($response)){
            self::printFooter();
            Program::exit(1);
        }

        // ✓ Done
        switch (self::$outputFormat){
            case self::OUTPUT_JSON:
                echo json_encode($response, JSON_PRETTY_PRINT);
                break;
        
            case self::OUTPUT_DEFAULT:  
                self::printBulkReportDetail($fileName);
                self::printBulkReportSavedReports($response);
                self::printBulkReportErrors($response);
                Console::log();
                self::printFooter($time);
                break;

            case self::OUTPUT_PLAINTEXT:
                $nbSavedReports = isset($response->data->savedReports) ? $response->data->savedReports : 0;
                echo $nbSavedReports . PHP_EOL;
                break;

        }
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
    protected static function clearIP(array $arguments): void
    {
        $ip = self::getArgumentValue($arguments,'E', 'clear');
        self::printTitle(Console::text('  ► Clear reports for IP: ', 'darkgray') . Console::text(escapeshellcmd($ip), 'white'));

        // Peforms request 
        self::printTempMessage();
        $timeStart = microtime(true);
        $response = self::$api->clearAddress($ip)->getObject();     
        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart; // request time
        self::clearTempMessage();

        // check for errors / empty response
        if (self::hasErrors($response)){
            self::printFooter($time);
            Program::exit(1);
        }
        
        // ✓ Done: print deleted report number 
        switch (self::$outputFormat){
            case self::OUTPUT_JSON:
                echo json_encode($response, JSON_PRETTY_PRINT);
                break;
       
            case self::OUTPUT_DEFAULT:  
                Console::log(
                    Console::text('   ✓', 'green') . 
                    Console::text(' Successfull clear request for IP: [', 'white') .
                    Console::text($ip, 'lightyellow') .
                    Console::text(']', 'white')
                );
                self::printResult('     Deleted reports: ', $response->data->numReportsDeleted ?? 0, 'lightyellow');
                Console::log();
                self::printFooter($time);
                break;

            case self::OUTPUT_PLAINTEXT:
                echo ($response->data->numReportsDeleted ?? 0) . PHP_EOL;
                break;

        }
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
    protected static function getBlacklist(array $arguments): void
    {
        self::printTitle(Console::text('  ► Get Blacklist ', 'darkgray'));

        $plainText  = (self::$outputFormat === self::OUTPUT_PLAINTEXT); 
        $limit      = self::getNumericParameter($arguments,'l', 'limit', 1000);
        $scoreMin   = self::getNumericParameter($arguments,'s', 'score', 100);
        
        self::printTempMessage();
        
        // do request 
        $timeStart = microtime(true);
        $response = self::$api->blacklist($limit, $plainText, $scoreMin);
        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart; // request time

        self::clearTempMessage();
    
        // response could be json on error, while plaintext flag is set
        $decodedResponse = $response->getObject();
        if (self::hasErrors($decodedResponse, false)){
            self::printFooter($time);
            Program::exit(1);
        }

        // ✓ Done: print deleted report number 
        switch (self::$outputFormat){
            case self::OUTPUT_JSON:
                echo json_encode($decodedResponse, JSON_PRETTY_PRINT);
                break;
       
            case self::OUTPUT_DEFAULT:  
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

                Console::log();
                self::printFooter($time);
                break;

            case self::OUTPUT_PLAINTEXT:
                // echo response "as is"
                Console::log($response->getPlaintext());
                break;

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
    protected static function checkBlock(array $arguments): void
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
        if (self::hasErrors($check)){
            self::printFooter($time);
            Program::exit(1);
        }

        switch (self::$outputFormat){
            case self::OUTPUT_JSON:
                echo json_encode($check, JSON_PRETTY_PRINT);
                break;
       
            case self::OUTPUT_DEFAULT:  
                self::printCheckBlockDetail($check);
                self::printCheckBlockReportedIP($check,$maxAge,$limit);
                Console::log();
                self::printFooter($time);
                break;

            case self::OUTPUT_PLAINTEXT:
                $nbReports = isset($check->data->reportedAddress) ? count($check->data->reportedAddress) : 0;
                if ($nbReports > 0) {
                    $numberDiplayedReports = 0;
                    foreach ($check->data->reportedAddress as $report){
                        echo ($report->ipAddress) . ' ' . $report->abuseConfidenceScore . PHP_EOL;

                        // counter
                        $numberDiplayedReports++;
                        if ($numberDiplayedReports === $limit) {
                            break;
                        }
                    }
                }
                break;
        }
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
    protected static function checkIP(array $arguments): void
    {
        $ip = self::getArgumentValue($arguments,'C', 'check');
        
        self::printTitle(Console::text('  ► Check IP: ', 'darkgray') . Console::text(escapeshellcmd($ip), 'white') . Console::text('', 'darkgray'));
        
        $verbose            = self::inArguments($arguments,'v', 'verbose');
        $maxAge             = self::getNumericParameter($arguments, 'd', 'days', 30);
        $maxReportsNumber   = self::getNumericParameter($arguments,'l', 'limit', 10);
        $ip                 = self::getArgumentValue($arguments,'C', 'check');

        self::printTempMessage();
        $timeStart = microtime(true);                                           
        $check = self::$api->check($ip, $maxAge, $verbose)->getObject();        
        $timeEnd = microtime(true);                                              
        $time = $timeEnd - $timeStart; // request time
        self::clearTempMessage();

        // check for errors / empty response
        if (self::hasErrors($check)){
            self::printFooter($time);
            Program::exit(1);
        }

        // score and data color (depending of abuseConfidenceScore)
        $score = empty($check->data->abuseConfidenceScore) ? 0 : $check->data->abuseConfidenceScore;

        switch (self::$outputFormat){
            case self::OUTPUT_JSON:
                echo json_encode($check, JSON_PRETTY_PRINT);
                break;
       
            case self::OUTPUT_DEFAULT:  
                $defaultColor = self::getScoreColor($score);
                self::printCheckScore($check);
                self::printCheckDetail($check, $defaultColor);
                self::printCheckReports($check, $maxAge, $defaultColor);
                if ($verbose){
                    self::printCheckLastReports($check, $maxReportsNumber);
                }
                Console::log();
                self::printFooter($time);
                break;

            case self::OUTPUT_PLAINTEXT:
                echo ($check->data->abuseConfidenceScore ?? 0) . PHP_EOL;
                break;

        }
    }
}