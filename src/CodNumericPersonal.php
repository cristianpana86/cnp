<?php
/**
 * @author: Cristian Pana
 *
 */
namespace CPANA\CNP;

use CPANA\CNP\Exception\GenericInvalidCNPException;
use CPANA\CNP\Exception\InvalidCNPLengthCNPException;
use CPANA\CNP\Exception\NonNumericValueCNPException;

/**
 * Class CodNumericPersonal
 *
 *
 * @package CPANA\CNP
 */
class CodNumericPersonal
{
    const CNP_LENGTH  = 13;

    const S           = 'S';
    const AA          = 'AA';
    const LL          = 'LL';
    const ZZ          = 'ZZ';
    const JJ          = 'JJ';
    const NNN         = 'NNN';
    const C           = 'C';

    const MALE        = 'BARBAT';
    const FEMALE      = 'FEMEIE';
    const FOREIGN     = 'STRAIN';

    /** @var string  */
    protected $cnpAsString;

    /**
     * @var array
     * Format S AA LL ZZ JJ NNN C
     */
    protected $cnpAsFormattedArray = [
        self::S   => null,
        self::AA  => null,
        self::LL  => null,
        self::ZZ  => null,
        self::JJ  => null,
        self::NNN => null,
        self::C   => null,
    ];

    protected $countyCodes = [
        '01' => 'Alba',
        '02' => 'Arad',
        '03' => 'Arges',
        '04' => 'Bacau',
        '05' => 'Bihor',
        '06' => 'Bistrita-Nasaud',
        '07' => 'Botosani',
        '08' => 'Brasov',
        '09' => 'Braila',
        '10' => 'Buzau',
        '11' => 'Caras-Severin',
        '12' => 'Cluj',
        '13' => 'Constanta',
        '14' => 'Covasna',
        '15' => 'Dambovita',
        '16' => 'Dolj',
        '17' => 'Galati',
        '18' => 'Gorj',
        '19' => 'Harghita',
        '20' => 'Hunedoara',
        '21' => 'Ialomita',
        '22' => 'Iasi',
        '23' => 'Ilfov',
        '24' => 'Maramures',
        '25' => 'Mehedinti',
        '26' => 'Mures',
        '27' => 'Neamt',
        '28' => 'Olt',
        '29' => 'Prahova',
        '30' => 'Satu Mare',
        '31' => 'Salaj',
        '32' => 'Sibiu',
        '33' => 'Suceava',
        '34' => 'Teleorman',
        '35' => 'Timis',
        '36' => 'Tulcea',
        '37' => 'Vaslui',
        '38' => 'Valcea',
        '39' => 'Vrancea',
        '40' => 'Bucuresti',
        '41' => 'Bucuresti S.1',
        '42' => 'Bucuresti S.2',
        '43' => 'Bucuresti S.3',
        '44' => 'Bucuresti S.4',
        '45' => 'Bucuresti S.5',
        '46' => 'Bucuresti S.6',
        '51' => 'Calarasi',
        '52' => 'Giurgiu'
    ];

    /** @var string */
    protected $gender;

    /** @var int */
    protected $year;

    /** @var int */
    protected $month;

    /** @var int */
    protected $day;

    /** @var string */
    protected $countyCode;


    /**
     * Un obiect CodNumericPersonal trebuie sa fie valid din momentul instantierii si este imutabil
     *
     * @param string $cnp
     * @throws InvalidCNPLengthCNPException
     * @throws NonNumericValueCNPException
     * @throws GenericInvalidCNPException
     */
    public function __construct(string $cnp)
    {
        $this->cnpAsString = $cnp;

        $this->validateLengthAndNumeric($cnp);

        $this->cnpAsFormattedArray = $this->getFormattedCNP($cnp);

        $this->gender = $this->identifyGender($this->cnpAsFormattedArray[self::S]);

        $this->year = $this->computeYear($this->cnpAsFormattedArray[self::S], $this->cnpAsFormattedArray[self::AA]);

        $this->month = $this->computeMonth($this->cnpAsFormattedArray[self::LL]);

        $this->day = $this->computeDay($this->cnpAsFormattedArray[self::ZZ], $this->month, $this->year);

        $this->countyCode = $this->computeCountyCode($this->cnpAsFormattedArray[self::JJ]);

        $this->validateCheckDigit($this->cnpAsString);
    }



    /**
     * Format S AA LL ZZ JJ NNN C
     *
     * S   - sex
     * AA  - an
     * LL  - luna
     * ZZ  - zi
     * JJ  - judet
     * NNN - numar unic pe zi
     * C   - cifră de control
     *
     * @param string $cnp
     * @return array
     */
    protected function getFormattedCNP(string $cnp)
    {
        $arr = str_split($cnp);

        $cnpAsFormattedArray = [
            self::S   => $arr[0],
            self::AA  => $arr[1].$arr[2],
            self::LL  => $arr[3].$arr[4],
            self::ZZ  => $arr[5].$arr[6],
            self::JJ  => $arr[7].$arr[8],
            self::NNN => $arr[9].$arr[10].$arr[11],
            self::C   => $arr[12],
        ];

        return $cnpAsFormattedArray;
    }

    /**
     * Prima cifră a CNP-ului este: (sex masculin / feminin)
     *   ● 1 / 2 - născuți între 1 ianuarie 1900 și 31 decembrie 1999
     *   ● 3 / 4 - născuți între 1 ianuarie 1800 și 31 decembrie 1899
     *   ● 5 / 6 - născuți între 1 ianuarie 2000 și 31 decembrie 2099
     *   ● 7 / 8 - pentru persoanele străine rezidente în România.
     *   ● În plus 9 - pentru persoanele străine.
     *
     * AA este un număr format din 2 cifre și reprezintă ultimele 2 cifre din anul nașterii. O persoană
     * născută în anul 1970 va avea la AA 70. (SAA = 170)
     * Daca o persoana va avea prima cifra cu una din valorile 7,8 (rezidenți) sau 9, atunci se va
     * considera secolul 20. ex SAA =771 anul nașterii va fi 1971.
     *
     * @param string $genderDigit
     * @param string $aa
     * @return string
     * @throws GenericInvalidCNPException
     */
    protected function computeYear(string $genderDigit, string $aa): int
    {
        $year = null;
        // Year in century can take values between 0 to 99
        $yearInCentury = intval($aa);

        switch ($genderDigit) {
            // 1 / 2 - născuți între 1 ianuarie 1900 și 31 decembrie 1999
            case 1:
            case 2:
                $year = $yearInCentury + 1900;
                break;
            // 3 / 4 - născuți între 1 ianuarie 1800 și 31 decembrie 1899
            case 3:
            case 4:
                $year = $yearInCentury + 1800;
                break;
            // 5 / 6 - născuți între 1 ianuarie 2000 și 31 decembrie 2099
            case 5:
            case 6:
                $year = $yearInCentury + 2000;
                break;
            // valorile 7,8 (rezidenți) sau 9, atunci se va considera secolul 20. ex SAA =771 anul nașterii va fi 1971.
            case 7:
            case 8:
            case 9:
                $year = $yearInCentury + 1900;
                break;
            default:
                // This line should not be reached if previous validations are done right
                throw new GenericInvalidCNPException("Nu a putut fi calculat anul folsind parametrul S: {$genderDigit} si parametrul AA: {$aa}!", GenericInvalidCNPException::EXCEPTION_YEAR);
        }

        return $year;
    }

    /**
     * @param string $ll
     * @return int
     * @throws GenericInvalidCNPException
     */
    protected function computeMonth(string $ll): int
    {
        $month = intval($ll);
        if (($month >= 1) && ($month <= 12)) {
            return $month;
        } else {
            throw new GenericInvalidCNPException("Valoarea {$ll} a parametrului LL este invalida!", GenericInvalidCNPException::EXCEPTION_MONTH);
        }
    }

    /**
     * @param string $zz
     * @param int $month
     * @param int $year
     * @return int
     * @throws GenericInvalidCNPException
     */
    protected function computeDay(string $zz, int $month, int $year): int
    {
        $day = (int)$zz;

        if (checkdate($month, $day, $year) === false) {
            throw new GenericInvalidCNPException("Valoarea {$zz} a parametrului ZZ este invalida!", GenericInvalidCNPException::EXCEPTION_DAY);
        }

        return $day;
    }

    /**
     * @param string $jj
     * @return string
     * @throws GenericInvalidCNPException
     */
    protected function computeCountyCode(string $jj): string
    {
        if (!array_key_exists($jj, $this->countyCodes)) {
            throw new GenericInvalidCNPException("Valoarea {$jj} a parametrului JJ este invalida!", GenericInvalidCNPException::EXCEPTION_COUNTY);
        }

        return $jj;
    }

    /**
     * C este cifră de control aflată în relație cu toate celelalte 12 cifre ale CNP-ului. Cifra de control
     * este calculată după cum urmează: fiecare cifră din CNP este înmulțită cu cifra de pe aceeași
     * poziție din numărul 279146358279; rezultatele sunt însumate, iar rezultatul final este împărțit
     * cu rest la 11. Dacă restul este 10, atunci cifra de control este 1, altfel cifra de control este
     * egală cu restul.
     *
     * @param string $cnp
     * @throws GenericInvalidCNPException
     */
    protected function validateCheckDigit(string $cnp): void
    {
        $checsumArrayDefinition = [2, 7, 9, 1, 4, 6, 3, 5, 8, 2, 7, 9];

        $cnpAsArray = str_split($cnp);

        $checksumValue = 0;

        for ($i = 0; $i < 12; $i++) {
            $checksumValue += intval($cnpAsArray[$i]) * $checsumArrayDefinition[$i];
        }

        $checksumValue = $checksumValue % 11;
        if ($checksumValue == 10) {
            $checksumValue = 1;
        }

        if (intval($this->cnpAsFormattedArray[self::C]) !== $checksumValue) {
            throw new GenericInvalidCNPException("Valoarea {$this->cnpAsFormattedArray[self::C]} a parametrului C este invalida!");
        }
    }


    /**
     * Codul numeric personal sau CNP este codul unic al fiecărei persoane născute în România, format din exact 13 cifre
     *
     * @param string $cnp
     * @throws InvalidCNPLengthCNPException
     * @throws NonNumericValueCNPException
     */
    protected function validateLengthAndNumeric(string $cnp): void
    {
        if (self::CNP_LENGTH !== strlen($cnp)) {
            throw new InvalidCNPLengthCNPException("Codul numeric personal trebuie sa contina exact 13 cifre!");
        }

        if (!ctype_digit($cnp)) {
            throw new NonNumericValueCNPException("Codul numeric personal trebuie sa contina doar cifre!");
        }
    }



    /**
     * @param string $genderDigit
     * @throws GenericInvalidCNPException
     * @return string
     */
    protected function identifyGender(string $genderDigit): string
    {
        // This should never happen if previously it was validated that each character in CNP is a number
        if (!in_array($genderDigit, ['1', '2', '3', '4', '5', '6', '7', '8', '9'])) {
            throw new GenericInvalidCNPException("Valoarea cifrei alocata sexului este invalida!", GenericInvalidCNPException::EXCEPTION_GENDER);
        }

        if (in_array(intval($genderDigit), [1,3,5,7])) {
            return self::MALE;
        }

        if (in_array(intval($genderDigit), [2,4,6,8])) {
            return self::FEMALE;
        }

        if (intval($genderDigit) === 9) {
            return self::FOREIGN;
        }
    }

    /**
     * @return string
     */
    public function getGender(): string
    {
        return $this->gender;
    }

    /**
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * @return int
     */
    public function getMonth(): int
    {
        return $this->month;
    }

    /**
     * @return int
     */
    public function getDay(): int
    {
        return $this->day;
    }

    /**
     * @return string
     */
    public function getCountyCode(): string
    {
        return $this->countyCode;
    }

    /**
     * @return mixed
     * @throws GenericInvalidCNPException
     */
    public function getCounty()
    {
        if (isset($this->countyCodes[$this->countyCode])) {
            return $this->countyCodes[$this->countyCode];
        } else {
            throw new GenericInvalidCNPException("Nu a fost gasit judetul pentru codul de judet: {$this->countyCode} !", GenericInvalidCNPException::EXCEPTION_COUNTY);
        }
    }
}
