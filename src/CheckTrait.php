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
 * Trait Check
 * 
 */
trait CheckTrait
{
    /**
     * Print confidence score 
     * 
     * @access protected
     * @static
     * @param object    $response
     * 
     * @return void
     */
    protected static function printCheckScore(object $response)
    {
        $score = empty($response->data->abuseConfidenceScore) ? 0 : $response->data->abuseConfidenceScore;
        $line = Console::text(Console::pad('   Confidence score:', 23), 'white') . self::getScoreBadge($score);
        Console::log($line);
    }

    /**
     * Print check IP detail 
     * 
     * @access protected
     * @static
     * @param object    $response
     * @param string    $color
     * 
     * @return void
     */
    protected static function printCheckDetail(object $response, string $color)
    {
        //      self::printResult('   isPublic', $response->data->isPublic, $defaultColor);
        //      self::printResult('   ipVersion', $response->data->ipVersion, $defaultColor);
 
        $line = self::printResult(Console::pad('   Whitelisted:', 23), $response->data->isWhitelisted ? 'true': 'false', $color, '', false);
        $line .= $response->data->isWhitelisted ? Console::text(' ★', 'green') : ''; 
        Console::log($line);
       
        self::printResult(Console::pad('   Country code:', 23), $response->data->countryCode, $color);
        
        if (!empty($response->data->countryName)){
            self::printResult(Console::pad('   Country name:', 23), $response->data->countryName, $color);
        }

        self::printResult(Console::pad('   ISP:', 23), $response->data->isp, $color);

        if ($response->data->usageType){
            $line = self::printResult(Console::pad('   Usage type:', 23), $response->data->usageType, $color, '', false);
            $line .= $response->data->usageType === 'Reserved' ? Console::text(' ◆', 'green') : '';
            Console::log($line);
        }

        $hostames = implode(', ', array_filter($response->data->hostnames)) ?? null;
        if (!empty($hostames)){
            self::printResult(Console::pad('   Hostname(s):', 23), $hostames, $color);
        }

        self::printResult(Console::pad('   Domain:', 23), $response->data->domain, $color);
    }

    /**
     * Print reports data 
     * 
     * @access protected
     * @static
     * @param object    $response
     * @param int       $maxAge
     * @param string    $color
     * 
     * @return void
     */
    protected static function printCheckReports(object $response, int $maxAge, string $color)
    {
        $nbReport = $response->data->totalReports && is_numeric($response->data->totalReports) ? intval($response->data->totalReports) : 0;
        
        if ($nbReport > 0 ){
            $line  = self::printResult(Console::pad('   Total reports:', 23), $nbReport, $color, '', false);
            $line .= self::printResult(' from ', $response->data->numDistinctUsers, $color, '', false);
            $line .= Console::text($nbReport > 0 ? ' distinct users': ' user', 'white');
            Console::log($line);

        } else {
            // no reports
            $day = $maxAge > 1 ? 'in last '. $maxAge . ' days': ' today';
            Console::log( Console::text('   ✓', 'green') . Console::text(' Not reported ' . $day));
        }
        
        if (!empty($response->data->lastReportedAt)){
            self::printResult(Console::pad('   Last reported at:', 23), self::getDate($response->data->lastReportedAt), $color);
        }
    }
    
    /**
     * Print last reports data 
     * 
     * @access protected
     * @static
     * @param object    $response
     * @param bool      $verbose
     * @param int       $maxReportsNumber
     * 
     * @return void
     */
    protected static function printCheckLastReports(object $response, int $maxReportsNumber)
    {
        $nbLastReports = isset($response->data->reports) ? count($response->data->reports) : 0;
            
        if ($nbLastReports > 0){
            Console::log('   Last reports:', 'white');
            $numberDiplayedReports = 0;
            $defaultColor = 'lightyellow'; // reset color for last reports
        
            foreach ($response->data->reports as $report){
                self::printLastReport($report);
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
    
    /**
     * Print single entry in last reports 
     * 
     * @access protected
     * @static
     * @param object    $report
     * 
     * @return array
     */
    private static function printLastReport(object $report)
    {
        $categories = self::getLastReportsCategories($report);
        $defaultColor = 'lightyellow'; // reset color for last reports
                            
        $line  = Console::text('    →', $defaultColor);
        $line .= self::printResult(' reported at: ', self::getDate($report->reportedAt), $defaultColor, '', false);
  //    $line .= self::printResult(' by user: ', $report->reporterId, $defaultColor, '', false);
        if (isset($report->reporterCountryCode) && isset($report->reporterCountryName)){
            $line .= Console::text(' from: ', 'white');
            $line .= self::printResult('', $report->reporterCountryCode, $defaultColor, '', false);
            $line .= Console::text(' - ', 'white');
            $line .= self::printResult('', $report->reporterCountryName, $defaultColor, '', false);
        }
        $line .= Console::text(' with categor' .  (count($categories) > 1 ? "ies: " : "y: "), 'white');
        foreach ($categories as $key => $cat) {
            $line .= Console::text($key==0 ? '' : ',' , 'white') . Console::text($cat, $defaultColor);
        }
        Console::log($line);

       
    }  

    /**
     * Get last report categories array 
     * 
     * @access protected
     * @static
     * @param object    $report
     * 
     * @return array
     */
    private static function getLastReportsCategories(object $report)
    {
        $categories = [];
        foreach (array_filter($report->categories) as $catId){
            $cat = ApiHandler::getCategoryNamebyId($catId)[0];
            if ($cat !== false) {
                $categories[] = $cat;
            }
        }
        return $categories;                          
   }    
}