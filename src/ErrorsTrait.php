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

/**
 * Trait Errors
 * 
 */
trait ErrorsTrait
{
    /**
     * Check and print errors in API response. Null response object is considered as no errors
     * 
     * @access protected
     * @static
     * @param object     $response       
     * 
     * @return void     
     */
    protected static function printPlainTextErrors(object $response)
    {
        foreach ($response->errors as $err){
            $text = 'Error: ';
            $text .= self::getErrorDetail($err, 'title');
            $text .= self::getErrorDetail($err, 'statuts');
            $text .= self::getErrorDetail($err, 'parameter', 'source');
            $text .= self::getErrorDetail($err, 'detail');
            $text .= PHP_EOL;
            echo $text;
        }
    }

    /**
     * Get error property if exist
     * 
     * @access protected
     * @static
     * @param object     $error       
     * @param string     $field       
     * 
     * @return void     
     */
    private static function getErrorDetail(object $error, string $field, ?string $parent = null)
    {
        if (!empty($parent)){
            return  !empty($error->$parent) && !empty($error->$parent->$field) ? ' ' . $field . ': ' . $error->$parent->$field : '';
        }

        return !empty($error->$field) ? ' ' . $field . ': ' . $error->$field : '';
    }

}