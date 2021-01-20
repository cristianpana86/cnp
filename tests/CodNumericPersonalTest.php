<?php declare(strict_types=1);
/**
 * @author: Cristian Pana
 * Date: 20.01.2021
 */

use CPANA\CNP\Exception\CNPExceptionInterface;
use CPANA\CNP\Exception\InvalidCNPLengthCNPException;
use CPANA\CNP\Exception\GenericInvalidCNPException;
use CPANA\CNP\Exception\NonNumericValueCNPException;
use PHPUnit\Framework\TestCase;


use CPANA\CNP\CodNumericPersonal;

class CodNumericPersonalTest extends TestCase
{
    protected $invalidCNPLength = '180030303000';

    protected $invalidCNPNonNumeric = "180030303000a";

    protected $validCNP = "6110111015477";



    public function testGetGender(): void
    {
        $cnpObj = new CodNumericPersonal($this->validCNP);

        $this->assertEquals(
            CodNumericPersonal::FEMALE,
            $cnpObj->getGender()
        );
    }

    public function testYear(): void
    {
        $cnpObj = new CodNumericPersonal($this->validCNP);

        $this->assertEquals(
            2011,
            $cnpObj->getYear()
        );
    }

    public function testMonth(): void
    {
        $cnpObj = new CodNumericPersonal($this->validCNP);

        $this->assertEquals(
            1,
            $cnpObj->getMonth()
        );
    }

    public function testDay(): void
    {
        $cnpObj = new CodNumericPersonal($this->validCNP);

        $this->assertEquals(
            11,
            $cnpObj->getDay()
        );
    }

    public function testCounty(): void
    {
        $cnpObj = new CodNumericPersonal($this->validCNP);

        $this->assertEquals(
            'Alba',
            $cnpObj->getCounty()
        );
    }

    public function testLength(): void
    {
        $this->expectException(InvalidCNPLengthCNPException::class);

        $cnpObj = new CodNumericPersonal($this->invalidCNPLength);
    }

    public function testNumeric(): void
    {
        $this->expectException(\CPANA\CNP\Exception\NonNumericValueCNPException::class);

        $cnpObj = new CodNumericPersonal($this->invalidCNPNonNumeric);
    }

}