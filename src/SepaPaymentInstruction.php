<?php
/**
 * Datenhaltung fÃ¼r Sepa Lastschriften
 *
 * PHP version 5.3
 *
 * @category Model
 * @package  Lifemeter
 * @author   Reinhard Hampl<reini@dreiwerken.de>
 */


namespace Sepa;

use Sepa\IBANChecker,
	Sepa\BICChecker;

/**
 * Object das die Informationen einer einzelnen Zahlung beinhaltet
 *
 * @category Sepa
 * @package  Lifemeter
 * @author   Reinhard Hampl <reini@dreiwerken.de>
 */

class SepaPaymentInstruction
{
	/**
	 * fortlaufende ID Innerhalb der Datei pro Zahlungsanweisung
	 * @var
	 */
	protected $ReferenzId; //Innerhalb des Payment ID Tags

	/**
	 * Einzuziehender Betrag
	 * @var
	 */
	protected $Betrag; //einzuziehender Betrag

	/**
	 * Lastschriftmandat der einziehenden Partei
	 * @var
	 */
	protected $MandatId;

	/**
	 * Datum, an dem das Mandat fÃ¼r die Lastschrift vom Debitor unterzeichnet wurde
	 * @var \DateTime
	 */
	protected $MandatDateOfSignature;

	/**
	 * Liegt eine Ã„nderung innerhalb des Mandats vor?
	 * Ist dieser Wert true, so muss die schnittstelle um die Ã„nderungsfelder erweitert werden. Diese ist aktuell
	 * noch nicht implementiert.
	 * @var bool
	 */
	protected $AmdmntInd = false;

	//TODO
	//wenn $AmdmntInd == true, mÃ¼ssen hier noch die geÃ¤nderten Angaben Ã¼bergeben werden!

	/**
	 * BIC des Debitors
	 * @var
	 */
	protected $DebitorBIC;

	/**
	 * Name des Debitors
	 * @var
	 */
	protected $DebitorName;

	/**
	 * IBAN des Debitors
	 * @var
	 */
	protected $DebitorIBAN;

	/**
	 * Abweichender Kontoinhaber beim Debitor
	 * @var
	 */
	protected $DebitorAbwName;

	/**
	 * Verwendungszweck fÃ¼r die Lastschrift
	 * @var
	 */
	protected $Verwendungszweck;


	/**
	 * @var \Sepa\IBANChecker
	 */
	private $IBANChecker;

	/**
	 * @var \Sepa\BICChecker
	 */
	private $BICChecker;

	public function __construct()
	{
		$this->IBANChecker = new IBANChecker();
		$this->BICChecker = new BICChecker();
	}

	/**
	 * @param $AmdmntInd
	 */
	public function setAmdmntInd($AmdmntInd)
	{
		$this->AmdmntInd=$AmdmntInd;
	}

	/**
	 * @return bool
	 */
	public function getAmdmntInd()
	{
		return $this->AmdmntInd;
	}

	/**
	 * @param $Betrag
	 * @throws \ErrorException
	 */
	public function setBetrag($Betrag)
	{
		if(strstr($Betrag, ',') > 0)
		{
			throw new \ErrorException('BetrÃ¤ge mÃ¼ssen einen Punkt als Trennzeichen habne, kein Komma!');
			return;
		}

		$this->Betrag=$Betrag;
	}

	/**
	 * @return mixed
	 */
	public function getBetrag()
	{
		return $this->Betrag;
	}

	/**
	 * @param $DebitorAbwName
	 * @throws \ErrorException
	 */
	public function setDebitorAbwName($DebitorAbwName)
	{
		if(strlen($DebitorAbwName) > 70)
		{
			throw new \ErrorException('Debitor Name darf maximal 70 Zeichen enthalten!');
			return;
		}


		$this->checkSpecialChars($DebitorAbwName);

		$this->DebitorAbwName=$DebitorAbwName;

	}

	/**
	 * @return mixed
	 */
	public function getDebitorAbwName()
	{
		return $this->DebitorAbwName;
	}

	/**
	 * @param $DebitorBIC
	 */
	public function setDebitorBIC($DebitorBIC)
	{
		$this->BICChecker->setBIC($DebitorBIC);

		if(!$this->BICChecker->validateBIC())
		{
			return;
		}

		$this->DebitorBIC=$DebitorBIC;
	}

	/**
	 * @return mixed
	 */
	public function getDebitorBIC()
	{
		return $this->DebitorBIC;
	}

	/**
	 * @param $DebitorIBAN
	 */
	public function setDebitorIBAN($DebitorIBAN)
	{
		$this->IBANChecker->setIBAN($DebitorIBAN);
		if(!$this->IBANChecker->validateIBAN())
		{
			return;
		}

		$this->DebitorIBAN=$DebitorIBAN;
	}

	/**
	 * @return mixed
	 */
	public function getDebitorIBAN()
	{
		return $this->DebitorIBAN;
	}

	/**
	 * @param string $DebitorName
	 * @throws \ErrorException
	 */
	public function setDebitorName($DebitorName)
	{
		if(strlen($DebitorName) > 70 )
		{
			throw new \ErrorException('Debitor Name darf maximal 70 Zeichen enthalten!');
			return;
		}

		$this->checkSpecialChars($DebitorName);

		$this->DebitorName=$DebitorName;
	}

	/**
	 * @return mixed
	 */
	public function getDebitorName()
	{
		return $this->DebitorName;
	}

	/**
	 * @param \DateTime $MandatDateOfSignature
	 */
	public function setMandatDateOfSignature(\DateTime $MandatDateOfSignature)
	{
		$this->MandatDateOfSignature=$MandatDateOfSignature;
	}

	/**
	 * @return \DateTime
	 */
	public function getMandatDateOfSignature()
	{
		return $this->MandatDateOfSignature;
	}

	/**
	 * @param $MandatId
	 */
	public function setMandatId($MandatId)
	{
		$this->MandatId=$MandatId;
	}

	/**
	 * @return mixed
	 */
	public function getMandatId()
	{
		return $this->MandatId;
	}

	/**
	 * @param $ReferenzId
	 */
	public function setReferenzId($ReferenzId)
	{
		$this->ReferenzId=$ReferenzId;
	}

	/**
	 * @return mixed
	 */
	public function getReferenzId()
	{
		return $this->ReferenzId;
	}

	/**
	 * @param $Verwendungszweck
	 * @throws \ErrorException
	 */
	public function setVerwendungszweck($Verwendungszweck)
	{
		if(strlen($Verwendungszweck) > 140)
		{
			throw new \ErrorException("Der Verwendungszweck darf maximal 140 Zeichen enthalten");
			return;
		}
		$this->Verwendungszweck=$Verwendungszweck;
	}

	/**
	 * @return mixed
	 */
	public function getVerwendungszweck()
	{
		return $this->Verwendungszweck;
	}

	/**
	 * Entfernt aus dem übergebenen Text alle nicht zulässigen Sonderzeichen für die Sepa Schnittstelle
	 *
	 * @param string $text
	 * @return string
	 */
	/*public function replaceSpecialChars($text)
	{
		$search = array('ä', 'ü', 'ö', 'ß', 'Ä', 'Ü', 'Ö');
		$replace = array('ae', 'ue', 'oe', 'ss', 'AE', 'UE', 'OE');

		$text = str_replace($search, $replace, $text);

		return $text;
	}*/

	/**
	 * @param $DebitorAbwName
	 * @return void
	 * @throws \ErrorException
	 */
	public function checkSpecialChars($text)
	{
		$retArray = array();
		preg_match('(ö|ä|ü|ß)', $text, $retArray);
		if(!empty($retArray))
		{
			/*
			 * zum ermitteln der Funktion die den Aufruf verursacht hat
			 */
			$trace = debug_backtrace();
			$previousCall = $trace[1];
			throw new \ErrorException('Übergebener Text ('. $text .') enthält nicht zulässige Sonderzeichen! Aufruf aus der Funktion '. $previousCall['function'] . '.');
		}
	}
}
