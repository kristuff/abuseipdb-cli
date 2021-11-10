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
 * @version    0.9.14
 * @copyright  2020-2021 Kristuff
 */
namespace Kristuff\AbuseIPDB;

use Kristuff\Mishell\Console;

/**
 * Trait BulkReport
 * 
 */
trait BulkReportTrait
{
    /**
     * Print report detail
     * 
     * @access protected
     * @static
     * @param object    $response
     * @param string    $fileName
     * 
     * @return void
     */
    protected static function printBulkReportDetail(string $fileName): void
    {
        Console::log(
            Console::text('   Bulk report for file: [', 'white') .
            Console::text($fileName, 'lightyellow') .
            Console::text('] done!', 'white')
        );
    }

    /**
     * Print report SavedReports
     * 
     * @access protected
     * @static
     * @param object    $response
     * @param string    $fileName
     * 
     * @return void
     */
    protected static function printBulkReportSavedReports(object $response): void
    {
        $nbSavedReports = isset($response->data->savedReports) ? $response->data->savedReports : 0;
        $savedColor = $nbSavedReports > 0 ? 'green' : 'red';
        $savedIcon  = $nbSavedReports > 0 ? '✓' : '✗';
        Console::log(Console::text('   ' . $savedIcon, $savedColor) . self::printResult(' Saved reports:    ', $nbSavedReports, $savedColor, '', false));
    }

    /**
     * Print report errors
     * 
     * @access protected
     * @static
     * @param object    $response
     * @param string    $fileName
     * 
     * @return void
     */
    protected static function printBulkReportErrors(object $response): void
    {
        $nbErrorReports = isset($response->data->invalidReports) ? count($response->data->invalidReports) : 0;
        $errorColor = $nbErrorReports > 0 ? 'red' : 'green';
        $errorIcon  = $nbErrorReports > 0 ? '✗' : '✓';

        Console::log(Console::text('   ' . $errorIcon, $errorColor) . self::printResult(' Invalid reports:  ', $nbErrorReports, $errorColor, '', false));

        if ($nbErrorReports > 0){
            $numberDiplayedReports = 0;
            $defaultColor = 'lightyellow'; // reset color for last reports

            foreach ($response->data->invalidReports as $report){
                $input = $report->input ? escapeshellcmd($report->input) : ''; // in case on blank line, IP is null
                Console::log(Console::text('      →', 'red') . self::printResult(' Input:         ', $input, $defaultColor, '', false));
                self::printResult('        Error:         ', $report->error, $defaultColor);
                self::printResult('        Line number:   ', $report->rowNumber, $defaultColor);
                
                // counter
                $numberDiplayedReports++;
            }
        }
    }
}