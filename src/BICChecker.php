<?php
	/**
	 * Checker für BIC Nummern
	 *
	 * PHP version 5.3
	 *
	 * @category Checker
	 * @package  Lifemeter
	 * @author   Reinhard Hampl<reini@dreiwerken.de>
	 */
namespace Sepa;


/**
 * Überprüft die Gültigkeit einer BIC
 *
 * @category Sepa
 * @package  Lifemeter
 * @author   Reinhard Hampl <reini@dreiwerken.de>
 */
class BICChecker
{
	private $BIC;


	public function __construct($BIC = '')
	{
		$this->BIC = $BIC;
	}

	/**
	 * Überprüft die gesetzte BIC auf Gültigkeit.
	 * Liefert true, wenn die BIC gültig ist.
	 * Liefert false, wenn die BIC nicht gültig ist.
	 *
	 * @throws \ErrorException Wenn keine BIC gesetzt ist.
	 *
	 * @return bool
	 */
	public function validateBIC()
	{
		if(empty($this->BIC))
		{
			throw new \ErrorException('Es wurde keine BIC gesetzt, die überprüft werden soll!');
			return false;
		}

		$valideBIC = true;
		$valideBIC = $valideBIC && $this->checkLength();

		return $valideBIC;
	}


	/**
	 * Setzt die IBAN die überprüft werden soll
	 * @param $IBAN
	 */
	public function setBIC($BIC)
	{
		$this->BIC=$BIC;
	}

	/**
	 * Liefert die IBAN die überprüft werden soll
	 * @return string
	 */
	private function getBIC()
	{
		return $this->BIC;
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
		if(strlen($this->getBIC()) != 8 && strlen($this->getBIC()) != 11 )
		{
			throw new \ErrorException('Der BIC Code hat die falsche Länge!');
			return false;
		}

		return true;
	}

}