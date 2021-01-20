<?php
/**
 * @author: Cristian Pana
 * Date: 20.01.2021
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use CPANA\CNP\CodNumericPersonal;
use CPANA\CNP\Exception\CNPExceptionInterface;

try {
    $cnpObj = new CodNumericPersonal($argv[1]);
} catch (CNPExceptionInterface $e) {
    // Display error to user in order to fix issue
    echo $e->getMessage().PHP_EOL;
} catch (\Exception $e) {
    echo  $e->getMessage().' '.$e->getTraceAsString();
}
