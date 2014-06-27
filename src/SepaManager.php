<?php
/**
 * Manager für Sepa Lastschriften
 *
 * PHP version 5.3
 *
 * @category Manager
 * @package  Lifemeter
 * @author   Reinhard Hampl<reini@dreiwerken.de>
 */
namespace Sepa;

use \Sepa\SepaGroupHeader,
	\Sepa\SepaPaymentInstruction;

/**
 * Object
 *
 * @category Sepa
 * @package  Lifemeter
 * @author   Reinhard Hampl <reini@dreiwerken.de>
 */
class SepaManager
{
	/**
	 * Hält die XML Strucktur der SEPA Nachricht
	 * @var
	 */
	private $xml;

	/**
	 * Kopf Informationen zur Pain Datei
	 *
	 * @var \Sepa\SepaGroupHeader
	 */
	private $sepaGroupHeader;

	/**
	 * Kreditor Informationen zur Pain Datei
	 *
	 * @var \Sepa\SepaCreditorInformation
	 */
	private $sepaCreditorInformation;

	/**
	 * Array in dem alle Zahlungsanweisungen enthalten sind (Sepa\SepaPaymentInstructions)
	 *
	 * @var array (Sepa\SepaPaymentInstructions)
	 */
	private $paymentInstructions = array();

	/**
	 * Anzahl der Transaktionen
	 * @var int
	 */
	private $countPayments = 0;

	/**
	 * Summe der kompletten Beträge
	 * @var int
	 */
	private $sumAmount = 0;

	/**
	 * @param \Sepa\SepaGroupHeader $sepaGroupHeader
	 * @param \Sepa\SepaCreditorInformation $sepaCreditorInformation
	 * @param array $paymentInstructions Array mit Sepa\PaymentInstructions
	 */
	public function __construct(\Sepa\SepaGroupHeader $sepaGroupHeader, \Sepa\SepaCreditorInformation $sepaCreditorInformation, array $paymentInstructions)
	{
		$this->sepaGroupHeader = $sepaGroupHeader;
		$this->sepaCreditorInformation = $sepaCreditorInformation;
		$this->paymentInstructions = $paymentInstructions;
	}

	/**
	 * Erstellt die XML Datei und liefert diese zurück
	 *
	 * @return mixed
	 */
	public function createXML()
	{
		if(count($this->paymentInstructions) > 9999999)
		{
			throw new \ErrorException('Es dürfen maximal 9.999.999 Zahlungsanweisungen in einer SEPA Nachricht enthalten sein!');
			return '';
		}

		//XML Grundgerüst einlesen
		$this->xml = simplexml_load_file(__DIR__."/sepa-header.xml");

		//Payment Informations werden entfernt, da diese im Anschluss seperat aufbereitet und hinzugefügt werden.
		unset($this->xml->CstmrDrctDbtInitn[0]->PmtInf[0]);

		//Header Informationen setzen
		$this->setHeaderInformations();

		//Payment Info setzen
		$this->setPaymentInfo();

		//Payment Instructions setzen
		$this->setPaymentInstructions();

		return $this->xml->asXML();
	}

	/**
	 * Versorgt die XML mit den Kopf Informationen
	 */
	private function setHeaderInformations()
	{
		//Header Informationen setzen
		$this->xml->CstmrDrctDbtInitn[0]->GrpHdr[0]->MsgId[0] = $this->sepaGroupHeader->getMessageId();
		$this->xml->CstmrDrctDbtInitn[0]->GrpHdr[0]->CreDtTm[0] = $this->getISODateTimeCreation($this->sepaGroupHeader->getCreationDateTime());
		$this->xml->CstmrDrctDbtInitn[0]->GrpHdr[0]->InitgPty[0]->Nm[0] = $this->sepaGroupHeader->getInitiatingPartyName();
	}

	/**
	 * Versorgt die XML mit den Grundlegenden Zahlungsinformationen
	 */
	private function setPaymentInfo()
	{
		$paymentInformationXML = simplexml_load_file(__DIR__."/sepa-paymentinfo.xml");

		$paymentInformationXML->PmtInfId = $this->sepaCreditorInformation->getPaymentId();
		$paymentInformationXML->ReqdColltnDt = $this->getISODateTime($this->sepaCreditorInformation->getExecutionDate());
		$paymentInformationXML->PmtTpInf[0]->LclInstrm[0]->Cd = $this->sepaCreditorInformation->getBuchungsart();
		$paymentInformationXML->Cdtr[0]->Nm = $this->sepaGroupHeader->getInitiatingPartyName();
		$paymentInformationXML->CdtrAcct[0]->Id[0]->IBAN = $this->sepaCreditorInformation->getCreditorIBAN();
		$paymentInformationXML->CdtrAgt[0]->FinInstnId[0]->BIC = $this->sepaCreditorInformation->getCreditorBIC();
		$paymentInformationXML->CdtrSchmeId[0]->Id[0]->PrvtId[0]->Othr[0]->Id = $this->sepaCreditorInformation->getMandatId();

		$this->simplexml_insert_after($paymentInformationXML, $this->xml->CstmrDrctDbtInitn[0]->GrpHdr[0]);
	}

	/**
	 * Versorgt die XML mit den einzelnen Zahlungs Transaktionen.
	 * Im Anschluss wird der summierte Betrag sowie die Anzahl der Transkationen auf die Kopfinformationen geschrieben
	 */
	private function setPaymentInstructions()
	{
		$this->sumAmount = 0;
		$this->countPayments = 0;

		foreach($this->paymentInstructions as $payment)
		{
			/**
			 * @var $payment SepaPaymentInstruction
			 **/

			$paymentXML = $this->getPaymentInstructionSnippet();
			$paymentXML[0]->PmtId[0]->EndToEndId = $payment->getReferenzId();
			$paymentXML[0]->InstdAmt = $payment->getBetrag();
			$paymentXML[0]->DrctDbtTx[0]->MndtRltdInf[0]->MndtId = $payment->getMandatId();
			$paymentXML[0]->DrctDbtTx[0]->MndtRltdInf[0]->DtOfSgntr = $this->getISODateTime($payment->getMandatDateOfSignature());
			$paymentXML[0]->DbtrAgt[0]->FinInstnId[0]->BIC = $payment->getDebitorBIC();
			$paymentXML[0]->Dbtr[0]->Nm = $payment->getDebitorName();
			$paymentXML[0]->DbtrAcct[0]->Id[0]->IBAN = $payment->getDebitorIBAN();
			$debitorName = $payment->getDebitorAbwName();
			if(!empty($debitorName))
			{
				$paymentXML[0]->UltmtDbtr[0]->Nm = $debitorName;
			}
			else
			{
				unset($paymentXML[0]->UltmtDbtr[0]);
			}

			$paymentXML[0]->RmtInf[0]->Ustrd = $payment->getVerwendungszweck();

			//Zahlungsanweisung der XML Anfügen
			$this->simplexml_insert_after($paymentXML, $this->xml->CstmrDrctDbtInitn[0]->PmtInf[0]->CdtrSchmeId);

			$this->countPayments++;
			$this->sumAmount += $payment->getBetrag();
		}

		$this->updateHeadInformations();
	}


	/**
	 * Aktualisiert die Kopfinformationen mit den Prüfwerten
	 */
	private function updateHeadInformations()
	{
		//Gruppen Header aktualisieren
		$this->xml->CstmrDrctDbtInitn[0]->GrpHdr[0]->CtrlSum[0] = $this->sumAmount;
		$this->xml->CstmrDrctDbtInitn[0]->GrpHdr[0]->NbOfTxs[0] = $this->countPayments;

		//Payment Info aktualisieren
		$this->xml->CstmrDrctDbtInitn[0]->PmtInf[0]->CtrlSum = $this->sumAmount;
		$this->xml->CstmrDrctDbtInitn[0]->PmtInf[0]->NbOfTxs = $this->countPayments;
	}

	/**
	 * Liefert anhand des übergebenen Datums einen Datumsstring zurück, der laut Spezifikation für
	 * das Erstellungdatum formatiert ist.
	 *
	 * @param $date
	 * @return string
	 */
	private function getISODateTimeCreation($date)
	{
		return $date->format("Y-m-d")."T".$date->format("H:i:s").$date->format("P");
	}

	/**
	 * Liefert anhand des übergebenen Datums einen Datumsstring zurück.
	 *
	 * @param $date
	 * @return mixed
	 */
	private function getISODateTime($date)
	{
		return $date->format("Y-m-d");
	}

	/**
	 * Liefert ein SimpleXML Object für einen Paymentinstructions Eintrag zurück
	 * @return object
	 */
	private function getPaymentInstructionSnippet()
	{
		//$PaymentInstruction = simplexml_load_file(__DIR__."/sepa-paymentinstruction.xml");
		$PaymentInstruction = simplexml_load_file(__DIR__."/sepa-paymentinstruction.xml");
		return $PaymentInstruction;
	}

	/**
	 * Fügt das $insert Objekt hinter dem $target Objekt in der XML Strucktur ein.
	 *
	 * @param $insert
	 * @param $target
	 * @return \DOMNode
	 */
	private function simplexml_insert_after($insert, $target)
	{
		$target_dom = dom_import_simplexml($target);
		$insert_dom = $target_dom->ownerDocument->importNode(dom_import_simplexml($insert), true);

		if ($target_dom->nextSibling)
		{
			return $target_dom->parentNode->insertBefore($insert_dom, $target_dom->nextSibling);
		}

		return $target_dom->parentNode->appendChild($insert_dom);
	}

}
