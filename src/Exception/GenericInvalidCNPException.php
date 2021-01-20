<?php
/**
 * @author: Cristian Pana
 * Date: 20.01.2021
 */

namespace CPANA\CNP\Exception;

/**
 * Generic invalid CNP Exception
 *
 * Class InvalidCNPException
 * @package CPANA\CNP\Exception
 */
class GenericInvalidCNPException extends \Exception implements CNPExceptionInterface
{
    const EXCEPTION_GENDER  = 1;
    const EXCEPTION_YEAR    = 2;
    const EXCEPTION_MONTH   = 3;
    const EXCEPTION_DAY     = 4;
    const EXCEPTION_COUNTY  = 5;
}
