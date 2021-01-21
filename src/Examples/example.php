<?php

declare(strict_types=1);
/**
 * @author: Cristian Pana
 */
require_once dirname(__DIR__, 2).'/vendor/autoload.php';

use CPANA\CNP\CodNumericPersonal;
use CPANA\CNP\Exception\CNPExceptionInterface;
use CPANA\CNP\Exception\GenericInvalidCNPException;
use CPANA\CNP\Exception\InvalidCNPLengthCNPException;
use CPANA\CNP\Exception\NonNumericValueCNPException;

/**
 * 1. Basic usage: try instantiate object, catch errors, on success use object to retrieve info.
 */
$inputString = '180010101a';

try {
    $cnpObj = new CodNumericPersonal($inputString);
} catch (CNPExceptionInterface $e) {
    // Display error to user in order to fix issue
    echo $e->getMessage().PHP_EOL;
} catch (\Exception $e) {
    // Other type of issue, log error display generic error page to user
    // $logger->log($e->getMessage());
    // $this->foward('some_route')
}
// Use newly created object to extract some info
// $county = $cnpObj->getCounty();

/**
 * 2. Detailed error handling.
 */
$inputString = '1800101990000';

try {
    $cnpObj = new CodNumericPersonal($inputString);
} catch (InvalidCNPLengthCNPException | NonNumericValueCNPException $e) {
    // Ex: Display message to user
} catch (GenericInvalidCNPException $e) {
    $code = $e->getCode();
    // take decision based on error code
    switch ($code) {
        case GenericInvalidCNPException::EXCEPTION_COUNTY:
            // take some action like  highlight wrong digits for country JJ
            echo 'Error code:'.$e->getCode().' '.$e->getMessage().PHP_EOL;

            break;

        case GenericInvalidCNPException::EXCEPTION_GENDER:
            // take some specific action ..
            break;

        case GenericInvalidCNPException::EXCEPTION_YEAR:
            // take some specific action ..
            break;

        case GenericInvalidCNPException::EXCEPTION_MONTH:
            // take some specific action ..
        case GenericInvalidCNPException::EXCEPTION_DAY:
            // take some specific action ..
            break;
    }
} catch (CNPExceptionInterface $e) {
    // do something
} catch (\Exception $e) {
    // do something
}

/**
 * 3. Just validation without getting info about what went wrong.
 */
function isValidCNP(string $inputString): bool
{
    try {
        $cnpObj = new CodNumericPersonal($inputString);
    } catch (CNPExceptionInterface $e) {
        return false;
    }

    return true;
}

$inputString = '180010101a';

if (isValidCNP($inputString)) {
    echo 'Is valid';
} else {
    echo 'Not valid';
}
