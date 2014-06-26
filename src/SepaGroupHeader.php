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

use Sepa\IBANChecker;

/**
 * Object das die Informationen für den Gruppen Kopf der SEPA Nachricht beinhaltet
 *
 * @category Sepa
 * @package  Lifemeter
 * @author   Reinhard Hampl <reini@dreiwerken.de>
 */

class SepaGroupHeader
{

	/**
	 * Message ID der Pain Nachricht
	 * @var
	 */
	protected $MessageId;

	/**
	 * Erstellungsdatum der Datei
	 * @var
	 */
	protected $CreationDateTime;

	/**
	 * Anzahl der Transaktionen innerhalb der Datei
	 * @var
	 */
	protected $NumberOfTransactions;

	/**
	 * Gesamtbetrag der in der Datei verarbeitet wird
	 * @var
	 */
	protected $ControlSum;

	/**
	 * Name des Veranlassers
	 * @var
	 */
	protected $InitiatingParty_Name;

	/**
	 * @return mixed
	 */
	public function getControlSum()
	{
		return $this->ControlSum;
	}

	/**
	 * @param $CreationDateTime
	 */
	public function setCreationDateTime($CreationDateTime)
	{
		$this->CreationDateTime=$CreationDateTime;
	}

	/**
	 * @return mixed
	 */
	public function getCreationDateTime()
	{
		return $this->CreationDateTime;
	}

	/**
	 * Prüft dabei dass der Name nicht länger als 70 Zeichen ist.
	 *
	 * @param $InitiatingParty_Name
	 * @throws \ErrorException
	 */
	public function setInitiatingPartyName($InitiatingParty_Name)
	{
		if(strlen($InitiatingParty_Name) > 70)
		{
			throw new \ErrorException('Zahlungsempfänger Name darf maximal 70 Zeichen enthalten!');
			return;
		}

		$this->InitiatingParty_Name=$InitiatingParty_Name;
	}

	/**
	 * @return mixed
	 */
	public function getInitiatingPartyName()
	{
		return $this->InitiatingParty_Name;
	}

	/**
	 * @param $MessageId
	 */
	public function setMessageId($MessageId)
	{
		$this->MessageId=$MessageId;
	}

	/**
	 * @return mixed
	 */
	public function getMessageId()
	{
		return $this->MessageId;
	}

	/**
	 * @return mixed
	 */
	public function getNumberOfTransactions()
	{
		return $this->NumberOfTransactions;
	}
}
