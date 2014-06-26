<?php
	/**
	 * Checker für IBAN Nummern
	 *
	 * PHP version 5.3
	 *
	 * @category Checker
	 * @package  Lifemeter
	 * @author   Reinhard Hampl<reini@dreiwerken.de>
	 */
namespace Sepa;


/**
 * Überprüft die Gültigkeit einer IBAN
 *
 * @category Sepa
 * @package  Lifemeter
 * @author   Reinhard Hampl <reini@dreiwerken.de>
 */
class IBANChecker
{
	private $IBAN;


	public function __construct($IBAN = '')
	{
		$this->IBAN = $IBAN;
	}

	/**
	 * Überprüft die gesetzte IBAN auf Gültigkeit.
	 * Liefert true, wenn die IBAN gültig ist.
	 * Liefert false, wenn die IBAN nicht gültig ist.
	 *
	 * @throws \ErrorException Wenn keine IBAN gesetzt ist.
	 *
	 * @return bool
	 */
	public function validateIBAN()
	{
		if(empty($this->IBAN))
		{
			throw new \ErrorException('Es wurde keine IBAN gesetzt, die überprüft werden soll!');
			return false;
		}

		$valideIBAN = true;

		$valideIBAN = $valideIBAN && $this->checkLength();
		$valideIBAN = $valideIBAN && $this->checkCountryIdentification();
		$valideIBAN = $valideIBAN && $this->checkChecksum();

		return $valideIBAN;
	}


	/**
	 * Setzt die IBAN die überprüft werden soll
	 * @param $IBAN
	 */
	public function setIBAN($IBAN)
	{
		$this->IBAN=$IBAN;
	}

	/**
	 * Liefert die IBAN die überprüft werden soll
	 * @return string
	 */
	private function getIBAN()
	{
		return $this->IBAN;
	}

	/**
	 * Überprüft die IBAN anhand des Prüfalgorythmus nach ISO 7064
	 * Liefert true wenn es sich um eine gültige IBAN handelt ansonsten false.
	 *
	 * @return bool
	 */
	private function checkChecksum()
	{
		$validationCode="";

		//ersten vier Zeichen (Länderkennzeichen mit Prüfsumme)
		$firstPart=substr($this->IBAN, 0, 4);

		//LKZ rausholen
		$LKZ=substr($firstPart, 0, 2);
		//Prüfsumme rausholen
		$checkSum=substr($firstPart, 2, 4);

		//Konto Informationen
		$accountInfo=substr($this->IBAN, 4, strlen($this->IBAN) - 1);

		$validationCode=$accountInfo;
		$splittedLKZ=str_split($LKZ);
		foreach ($splittedLKZ as $char)
		{
			$validationCode.=$this->getNumberOfChar($char);
		}

		$validationCode.=$checkSum;
		$mod=bcmod($validationCode, 97);

		//muss 1 ergeben, dann ist die IBAN gültig!
		if(($mod != 1))
		{
			throw new \ErrorException('Pr&uuml;fziffer bei IBAN ist falsch!');
			return false;
		}
		return true;
	}

	/**
	 * Überprüft die Länge der IBAN
	 * Liefert true wenn die IBAN länger 5 Zeichen ist und nicht größer 34 Zeichen ist.
	 *
	 * @return bool
	 * @throws \ErrorException
	 */
	private function checkLength()
	{
		if(strlen($this->getIBAN()) < 5 || strlen($this->getIBAN()) > 34 )
		{
			throw new \ErrorException('Der &uuml;bergeben IBAN Code hat die falsche L&auml;nge! (Zul&auml;ssig ist zwischen 5 und 34 Zeichen)');
			return false;
		}

		return true;
	}

	/**
	 * Überprüft ob die ersten beiden Zeichen Buchstaben sind.
	 * Liefert true wenn die ersten beiden Zeichen Buschstaben sind, ansonsten false.
	 *
	 * @return bool
	 * @throws \ErrorException Die IBAN beginnt nicht mit 2 führenden Buchstaben
	 */
	private function checkCountryIdentification()
	{
		$LKZ = substr($this->getIBAN(),0,2);

		if(!ctype_alpha($LKZ))
		{
			throw new \ErrorException('Die ersten beiden Zeichen des IBAN Codes m&uuml;ssen Buchstaben sein!');
			return false;
		}

		return true;
	}


	/**
	 * Liefert zum übergebene Buchstaben einen nummerischen Wert. Dabei wird die Stelle im Alphabet genommen und
	 * mit 9 addiert.
	 *
	 * @param $charToFind
	 * @return int
	 */
	private function getNumberOfChar($charToFind)
	{
		$StartPoint = ord('A');
		$StartPoint--;
		$CharNumber = ord($charToFind);

		$PositionOfChar = $CharNumber - $StartPoint;

		return $PositionOfChar + 9;
	}

}