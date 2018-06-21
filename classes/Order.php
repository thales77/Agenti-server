<?php

/**
 * Class to retrieve item data from the database
 */
class Order {
// Properties
    /**
     * @var string order's ID
     */
    public $orderId = null;
    
    /**
     * @var string order's document number
     */
    public $numDoc = null;
    
     /**
     * @var string registration date
     */
    public $dataRegist = null;
    
    /**
     * @var string Client ID
     */
    public $codCliente = null;
    
    /**
     * @var string Client ID
     */
    public $desCli = null;

    /**
     * @var string imponibile
     */
    public $totImp = null;
    
    /**
     * @var string IVA
     */
    public $totIva = null;
    
    /**
     * @var string Payment defined for order
     */
    public $pagamento= null;

    /**
     * @var string stato ordine
     */
    public $stato = null;
     /**
     * @var string Numero riga doc
     */
    public $numRiga = null;
     /**
     * @var string codice articolo
     */
    public $codArt = null;
     /**
     * @var string descrizione articolo
     */
    public $descArt = null;
     /**
     * @var string unita di misura
     */
    public $um = null;
     /**
     * @var string stato riga ordine
     */
    public $statoRiga = null;
     /**
     * @var string quantita
     */
    public $qta = null;
     /**
     * @var string prezzo articolo
     */
    public $prezzo = null;
     /**
     * @var string importo totale riga
     */
    public $impTotRiga = null;
     /**
     * @var string sconto1
     */
    public $sconto1 = null;
     /**
     * @var string sconto2
     */
    public $sconto2 = null;
     /**
     * @var string quantita spedita
     */
    public $quantSped = null;
     /**
     * @var string quantita fatturata
     */
    public $quantFatt = null;
     /**
     * @var string prezzo al netto degli sconti
     */
    public $prezzoNetto = null;
     /**
     * @var string quantita residua
     */
    public $quantRes = null;
     /**
     * @var string importo residuo
     */
    public $importRes = null;
    /**
     * @var string agente
     */
    public $descAgente = null;
    
    
    /**
     * Sets the object's properties using the values in the supplied array
     *
     * @param assoc the property values
     */
    public function __construct($data = array()) {
        if (isset($data['ID']))
            $this->orderId = trim($data['ID']);
        if (isset($data['NUMDOC']))
            $this->numDoc = trim($data['NUMDOC']);
        if (isset($data['DataRegist']))
            $this->dataRegist = trim($data['DataRegist']);
        if (isset($data['CodCliente']))
            $this->codCliente = trim($data['CodCliente']);
        if (isset($data['DesCli']))
            $this->desCli = trim($data['DesCli']);
        if (isset($data['TOTIMP']))
            $this->totImp = trim(str_replace("." , "," ,$data['TOTIMP']));
        if (isset($data['TOTIVA']))
            $this->totIva = trim(str_replace("." , "," ,$data['TOTIVA']));
        if (isset($data['Pagamento']))
            $this->pagamento = trim($data['Pagamento']);
        if (isset($data['STATO']))
            $this->stato = trim($data['STATO']);
        if (isset($data['RIGADOC']))
            $this->numRiga = trim($data['RIGADOC']);
        if (isset($data['CODART']))
            $this->codArt = trim($data['CODART']);
        if (isset($data['DESCART']))
            $this->descArt = preg_replace("/[^\.\-\_\@a-zA-Z0-9]/", " ", trim($data['DESCART']));
        if (isset($data['UM1']))
            $this->um = trim($data['UM1']);
        if (isset($data['StatoRiga']))
            $this->statoRiga = trim($data['StatoRiga']);
        if (isset($data['QTA1']))
            $this->qta = trim($data['QTA1']);
        if (isset($data['PREZZO']))
            $this->prezzo = trim(str_replace("." , "," ,$data['PREZZO']));
        if (isset($data['IMPTOT']))
            $this->impTotRiga = trim(str_replace("." , "," ,$data['IMPTOT']));
        if (isset($data['SCONTO1']))
            $this->sconto1 = trim($data['SCONTO1']);         
        if (isset($data['SCONTO2']))
            $this->sconto2 = trim($data['SCONTO2']); 
        if (isset($data['QTASPE']))
            $this->quantSped = trim($data['QTASPE']);
        if (isset($data['QTAFAT']))
            $this->quantFatt = trim($data['QTAFAT']); 
        if (isset($data['PREZZON']))
            $this->prezzoNetto = trim(str_replace("." , "," ,$data['PREZZON'])); 
        if (isset($data['QtaRes']))
            $this->quantRes = trim($data['QtaRes']);
        if (isset($data['ImpRes']))
            $this->importRes = trim(str_replace("." , "," ,$data['ImpRes']));
        if (isset($data['DescAgente']))
            $this->descAgente = trim($data['DescAgente']);
    }

    /**
     * Get order list
     */
    public static function getOrderList($idAgente, $datefrom, $dateto) {
        if ($idAgente != "") {

            try {
                $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
                $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

            } catch (Exception $e) {
                echo $e->getMessage() . " Client.php getOrderList 187";
                exit;
            }

            //if user is admin return all orders
            if ($idAgente == "9999") {
                $sql = "SELECT * FROM Commesse
                              WHERE Commesse.DataRegist <= :dateto
                              AND Commesse.DataRegist >= :datefrom
                              ORDER BY DataRegist DESC, ID DESC";

                $st = $conn->prepare($sql);
                $st->bindValue(":datefrom", $datefrom, PDO::PARAM_STR);
                $st->bindValue(":dateto", $dateto, PDO::PARAM_STR);
            //else return only current agente orders
            } else {
                $sql = "SELECT * FROM Commesse
                              WHERE Commesse.IDAGENTE = :idAgente
                              AND Commesse.DataRegist <= :dateto
                              AND Commesse.DataRegist >= :datefrom
                              ORDER BY DataRegist DESC, ID DESC";

                $st = $conn->prepare($sql);
                $st->bindValue(":idAgente", $idAgente, PDO::PARAM_STR);
                $st->bindValue(":datefrom", $datefrom, PDO::PARAM_STR);
                $st->bindValue(":dateto", $dateto, PDO::PARAM_STR);
            }

            try {
                $st->execute();

            } catch (Exception $e) {
                echo $e->getMessage() . "Order.php getOrderList Line 218";
                exit;
            }

            $list = array();

            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                $list[] = new Order($row);
            }

            $conn = null;
            return $list;
        }
    }

    /**
     * Get order details
     */
    public static function getOrderDetail($orderId) {

        try {
            $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

        } catch (Exception $e) {
            echo $e->getMessage() . " Order.php getOrderDetail Line 244";
            exit;
        }


        if ($orderId != "") {
            $st = $conn->prepare("SELECT CommesseRig.RIGADOC,
                                    CommesseRig.CODART,
                                    CommesseRig.DESCART,
                                    CommesseRig.UM1,
                                    CommesseRig.STATO as StatoRiga,
                                    CommesseRig.QTA1,
                                    CommesseRig.PREZZO,
                                    CommesseRig.IMPTOT,
                                    CommesseRig.SCONTO1,
                                    CommesseRig.SCONTO2,
                                    CommesseRig.QTASPE,
                                    CommesseRig.QTAFAT,
                                    CommesseRig.PREZZON,
                                    CommesseRig.QtaRes,
                                    CommesseRig.ImpRes
                                    FROM
                                    CommesseRig
                                    where CommesseRig.IDCOMTES = :orderId
                                    order by CommesseRig.RIGADOC");
            $st->bindValue(":orderId", $orderId, PDO::PARAM_STR);

            try {
                $st->execute();

            } catch (Exception $e) {
                echo $e->getMessage() . "Order.php getOrderDetail Line 275";
                exit;
            }

            $list = array();

            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                $list[] = new Order($row);
            }

            $conn = null;
            return $list;
        }
    }

}
?>
