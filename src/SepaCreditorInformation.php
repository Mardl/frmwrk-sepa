<?php
	/**
	 * Datenhaltung für Sepa Lastschriften
	 *
	 * PHP version 5.3
	 *
	 * @category Model
	 * @package  Lifemeter
	 * @author   Reinhard Hampl<reini@dreiwerken.de>
	 */


namespace Sepa;

use \Sepa\IBANChecker,
	\Sepa\BICChecker;

/**
 * Object das die Kreditor Informationen für die SEPA Nachricht beinhaltet
 *
 * @category Sepa
 * @package  Lifemeter
 * @author   Reinhard Hampl <reini@dreiwerken.de>
 */

class SepaCreditorInformation
{
	/**
	 * Name des Begünstigten
	 * @var
	 */
	protected $CreditorName;

	/**
	 * BIC des Begünstigten
	 * @var
	 */
	protected $CreditorBIC;

	/**
	 * IBAN des Begünstigten
	 * @var
	 */
	protected $CreditorIBAN;

	/**
	 * Ausführungsdatum der Lastschriften
	 * @var
	 */
	protected $ExecutionDate;

	/**
	 * Mandats-ID des Begünstigten
	 * @var
	 */
	protected $MandatId;

	/**
	 * Eindeutige Message Id für die Zahlung
	 * @var
	 */
	protected $PaymentId;

	/**
	 * @var \Sepa\IBANChecker
	 */
	private $IBANChecker;

	/**
	 * @var \Sepa\BICChecker
	 */
	private $BICChecker;

	/**
	 * @var string CORE, COR1 oder B2B
	 */
	private $Buchungsart = 'CORE';


	public function __construct()
	{
		$this->IBANChecker = new IBANChecker();
		$this->BICChecker = new BICChecker();
	}

	public function setCreditorBIC($CreditorBIC)
	{
		$this->BICChecker->setBIC($CreditorBIC);
		if(!$this->BICChecker->validateBIC())
		{
			return;
		}

		$this->CreditorBIC=$CreditorBIC;
	}

	public function getCreditorBIC()
	{
		return $this->CreditorBIC;
	}

	public function setCreditorIBAN($CreditorIBAN)
	{
		$this->IBANChecker->setIBAN($CreditorIBAN);
		if(!$this->IBANChecker->validateIBAN())
		{
			return;
		}

		$this->CreditorIBAN=$CreditorIBAN;
	}

	public function getCreditorIBAN()
	{
		return $this->CreditorIBAN;
	}

	public function setCreditorName($CreditorName)
	{
		if(strlen($CreditorName) > 70 )
		{
			throw new \ErrorException('Kreditor Name darf maximal 70 Zeichen enthalten');
			return;
		}

		$this->CreditorName=$CreditorName;
	}

	public function getCreditorName()
	{
		return $this->CreditorName;
	}

	public function setExecutionDate($ExecutionDate)
	{
		$this->ExecutionDate=$ExecutionDate;
	}

	public function getExecutionDate()
	{
		return $this->ExecutionDate;
	}

	public function setMandatId($MandatId)
	{
		$this->MandatId=$MandatId;
	}

	public function getMandatId()
	{
		return $this->MandatId;
	}


	public function setBuchungsart($Buchungsart)
	{
		$this->Buchungsart=$Buchungsart;
	}

	public function getBuchungsart()
	{
		return $this->Buchungsart;
	}

	/**
	 * @param  $PaymentId
	 */
	public function setPaymentId($PaymentId)
	{
		$this->PaymentId=$PaymentId;
	}

	/**
	 * @return
	 */
	public function getPaymentId()
	{
		return $this->PaymentId;
	}
}
