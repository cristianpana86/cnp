<?php

declare(strict_types=1);
/**
 * @author: Cristian Pana
 * Date: 20.01.2021
 */

namespace CPANA\Test;

use CPANA\CNP\CodNumericPersonal;
use CPANA\CNP\Exception\InvalidCNPLengthCNPException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class CodNumericPersonalTest extends TestCase
{
    protected $invalidCNPLength = '180030303000';

    protected $invalidCNPNonNumeric = '180030303000a';

    protected $validCNP = '6110111015477';

    public function testGetGender(): void
    {
        $cnpObj = new CodNumericPersonal($this->validCNP);

        static::assertSame(
            CodNumericPersonal::FEMALE,
            $cnpObj->getGender()
        );
    }

    public function testYear(): void
    {
        $cnpObj = new CodNumericPersonal($this->validCNP);

        static::assertSame(
            2011,
            $cnpObj->getYear()
        );
    }

    public function testMonth(): void
    {
        $cnpObj = new CodNumericPersonal($this->validCNP);

        static::assertSame(
            1,
            $cnpObj->getMonth()
        );
    }

    public function testDay(): void
    {
        $cnpObj = new CodNumericPersonal($this->validCNP);

        static::assertSame(
            11,
            $cnpObj->getDay()
        );
    }

    public function testCounty(): void
    {
        $cnpObj = new CodNumericPersonal($this->validCNP);

        static::assertSame(
            'Alba',
            $cnpObj->getCounty()
        );
    }

    public function testLength(): void
    {
        $this->expectException(InvalidCNPLengthCNPException::class);

        new CodNumericPersonal($this->invalidCNPLength);
    }

    public function testNumeric(): void
    {
        $this->expectException(\CPANA\CNP\Exception\NonNumericValueCNPException::class);

        new CodNumericPersonal($this->invalidCNPNonNumeric);
    }
}
