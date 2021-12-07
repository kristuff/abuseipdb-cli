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
 * @version    0.9.17
 * @copyright  2020-2021 Kristuff
 */
namespace Kristuff\AbuseIPDB;

use Kristuff\Mishell\Console;

/**
 * Trait CheckBlock
 */
trait CheckBlockTrait
{
    /**
     * Prints IP detail 
     * 
     * @access protected
     * @static
     * @param object    $response
     * 
     * @return void
     */
    protected static function printCheckBlockDetail(object $response): void
    {
        self::printResult(Console::pad('   Network Address:', 23), $response->data->networkAddress, 'lightyellow');
        self::printResult(Console::pad('   Netmask:', 23), $response->data->netmask, 'lightyellow');
        self::printResult(Console::pad('   Min Address:', 23), $response->data->minAddress, 'lightyellow');
        self::printResult(Console::pad('   Max Address:', 23), $response->data->maxAddress, 'lightyellow');
        self::printResult(Console::pad('   Possible Hosts:', 23), $response->data->numPossibleHosts, 'lightyellow');
        self::printResult(Console::pad('   Address SpaceDesc:', 23), $response->data->addressSpaceDesc, 'lightyellow');
    }   
    
    /**
     * Prints reported IP 
     * 
     * @access protected
     * @static
     * @param object    $response
     * @param int       $maxAge
     * @param int       $limit
     * 
     * @return void
     */
    protected static function printCheckBlockReportedIP(object $response, int $maxAge, int $limit): void
    {
        $nbReports = isset($response->data->reportedAddress) ? count($response->data->reportedAddress) : 0;
        
        if ($nbReports > 0) {
            self::printResult(Console::pad('   Reported addresses:', 23), $nbReports, 'lightyellow');
            $numberDiplayedReports = 0;
               
            foreach ($response->data->reportedAddress as $report){
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
    }   
}