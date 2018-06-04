<?php

/**
 * Class to retrieve client data from the database
 */
class Client {
// Properties

    /**
     * @var string The client's Ragione Sociale
     */
    public $ragSociale = null;

    /**
     * @var string The client's codice
     */
    public $codice = null;

    /**
     * @var string The client's partita iva
     */
    public $parIva = null;
    
    /**
     * @var string The client's partita iva
     */
    public $categoriaSconto = null;
    
    /**
     * @var string The client's partita iva
     */
    public $noTelefono = null;
    
    /**
     * @var string The client's partita iva
     */
    public $noCell = null;
  
    /**
     * @var string The client's partita iva
     */
    public $noFax = null;
    
    /**
     * @var string The client's partita iva
     */
    public $email = null;
    
    /**
     * @var string The client's partita iva
     */
    public $indirizzo = null;
    
    /**
     * @var string The client's partita iva
     */
    public $cap = null;
    
    /**
     * @var string The client's partita iva
     */
    public $comune = null;
    
    /**
     * @var string The client's partita iva
     */
    public $provincia = null;
    
    /**
     * @var string The client's partita iva
     */
    public $fattCorrente = null;
    
    /**
     * @var string The client's partita iva
     */
    public $fattPrecedente = null;

    /**
     * @var string saldo Professional
     */
    public $saldoProfessional = null;

    /**
     * @var string saldo Service
     */
    public $saldoService = null;
    
     /**
     * @var string stato cliente
     */
    public $stato = null;

    /**
     * @var string Agente
     */
    public $agente = null;

    /**
     * @var string pagamento cliente
     */
    public $pagamento = null;

    /**
     * @var string indirizziAlt cliente
     */
    public $indirizziAlt = null;

    /**
     * @var string categoria cliente
     */
    public $categoria = null;
    /**
     * Sets the object's properties using the values in the supplied array
     *
     * @param assoc property values
     */
    public function __construct($data = array()) {
        if (isset($data['DESCR1']))
            $this->ragSociale = trim($data['DESCR1']);
        if (isset($data['CODICE']))
            $this->codice = $data['CODICE'];
        if (isset($data['PARIVA']))
            $this->parIva = preg_replace("/[^\.\-\_\@a-zA-Z0-9]/", "", $data['PARIVA']);
        if (isset($data['SCONTO']))
            $this->categoriaSconto = (int) $data['SCONTO'];
        if (isset($data['TEL']))
            $this->noTelefono = preg_replace('/[^0-9]/', '', substr($data['TEL'], 0, 11));
        if (isset($data['Cell']))
            $this->noCell = preg_replace('/[^0-9]/', '', substr($data['Cell'], 0, 11));
        if (isset($data['FAX']))
            $this->noFax = preg_replace('/[^0-9]/', '', substr($data['FAX'], 0, 11));
        if (isset($data['EMAIL']))
            $this->email = preg_replace("/[^\.\-\_\@a-zA-Z0-9]/", "", $data['EMAIL']);
        if (isset($data['INDIRI']))
            $this->indirizzo = preg_replace("/[^\.\-\_\@a-zA-Z0-9]/", " ", $data['INDIRI']); //funky degree symbol in some addresses causing problems
        if (isset($data['CAP']))
            $this->cap = preg_replace("/[^\.\-\_\@a-zA-Z0-9]/", "", $data['CAP']);
        if (isset($data['COMUNE']))
            $this->comune = preg_replace("/[^\.\-\_\@a-zA-Z0-9]/", "", $data['COMUNE']);
        if (isset($data['PROV']))
            $this->provincia = preg_replace("/[^\.\-\_\@a-zA-Z0-9]/", "", $data['PROV']);
        if (isset($data['PROGFATIVA']))
            $this->fattCorrente = str_replace(".", ",", ($data['PROGFATIVA']));
        if (isset($data['PROGFATIVAP']))
            $this->fattPrecedente = str_replace(".", ",", ($data['PROGFATIVAP']));
        if (isset($data['SaldoProf']))
            $this->saldoProfessional = str_replace(".", ",", ($data['SaldoProf'] + $data['SaldoBProf'])); //saldo A+B Professional
        if (isset($data['SaldoService']))
            $this->saldoService = str_replace(".", ",", ($data['SaldoService'] + $data['SaldoBService'])); //saldo A+B Service
        if (isset($data['STATO']))
            $this->stato = (int) $data['STATO'];
        if (isset($data['AGENTE']))
            $this->agente = trim($data['AGENTE']);
        if (isset($data['PAGAMENTO']))
            $this->pagamento = trim($data['PAGAMENTO']);
        if (isset($data['IndirizziAlt']))
            $this->indirizziAlt =  preg_replace("/[^\.\-\_\@a-zA-Z0-9]/", " ", trim($data['IndirizziAlt']));
        if (isset($data['CATEGORIA']))
            $this->categoria = trim($data['CATEGORIA']);

    }

    /**
     * Retrieve client list matching the search string ordered by fatturato
     */
    public static function getClientList($searchTerm, $searchOptions, $listOffset, $perPage) {

        if ($searchTerm != "") {

            try {
                $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
                $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

            } catch (Exception $e) {
                echo $e->getMessage() . " Client.php getClientList 176";
                exit;
            }

            $listOffset = (int)$listOffset;
            $perPage = (int)$perPage;
            
            /*Sql select for counting number of rows returned by main query which follows*/
            $countSql = "SELECT count(*)
                           FROM Clienti 
                           WHERE ";
            
            /*main sql query*/
            $sql = "SELECT Clienti.DESCR1,
                           Clienti.CODICE,
                           Clienti.PARIVA,
                           Clienti.SCONTO,
                           Clienti.TEL,
                           Clienti.Cell,
                           Clienti.FAX,
                           Clienti.EMAIL,
                           Clienti.INDIRI,
                           Clienti.CAP,
                           Clienti.COMUNE,
                           Clienti.PROV,
                           Clienti.PROGFATIVA,
                           Clienti.PROGFATIVAP,
                           Clienti.STATO,
                           Clienti.AGENTE,
                           Clienti.PAGAMENTO,
                           Clienti.CATEGORIA,
                           COALESCE(saldicliProfessional.saldo,0) AS SaldoProf,
                           COALESCE(saldicliService.saldo,0) AS SaldoService,
                           COALESCE(saldicliProfessional.SaldoX, 0) AS SaldoBProf,
                           COALESCE(saldicliService.SaldoX,0) AS SaldoBService,
                           GROUP_CONCAT(concat_ws('-', Indirizzi_alt.TIPIND, concat_ws(' ', Indirizzi_alt.INDIRI, Indirizzi_alt.CAP, Indirizzi_alt.LOCALITA, Indirizzi_alt.IdZona, Indirizzi_alt.COMUNE)) SEPARATOR ';') as IndirizziAlt
                           FROM Clienti
                           LEFT JOIN saldicliProfessional ON Clienti.CODICE = saldicliProfessional.CODICE
                           LEFT JOIN saldicliService ON saldicliProfessional.PARIVA = saldicliService.PARIVA
                           LEFT JOIN Indirizzi_alt ON Clienti.CODICE = Indirizzi_alt.IDPERSONA
                           WHERE ";

            $options = array();

            if (in_array("ragioneSociale", $searchOptions)) {
                $options[] = "Clienti.DESCR1 like concat('%', :searchTerm, '%') OR Clienti.DESCR2 like concat('%', :searchTerm, '%')";
            }
            if (in_array("codiceCliente", $searchOptions)) {
                $options[] = "Clienti.CODICE like concat('%', :searchTerm, '%')";
            }
            if (in_array("partitaIva", $searchOptions)) {
                $options[] = "Clienti.PARIVA like concat('%', :searchTerm, '%')";
            }
            if (in_array("comune", $searchOptions)) {
                $options[] = "Clienti.COMUNE like concat('%', :searchTerm, '%')";
            }
            if (count($options) < 1) {
                $options[] = "Clienti.DESCR1 like concat('%', :searchTerm, '%') OR Clienti.DESCR2 like concat('%', :searchTerm, '%')";
            }

            if (count($options) > 1) {
                $andOr = "OR";
            }
            else {
                $andOr = "";
            }


            /*Sql select for counting number of rows returned by main query is constructed here*/
            $countSql .= implode(" {$andOr} ", $options);
            $result = $conn->prepare($countSql);
            $result->bindValue(":searchTerm", $searchTerm, PDO::PARAM_STR);

            try {
                $result->execute();

            } catch (Exception $e) {
            echo $e->getMessage() . "Client.php getClientList Line 252";
            exit;
            }



            $numOfRows = $result->fetchColumn();
            
            /*Main query is constructed here*/
            $sql .= implode(" {$andOr} ", $options) . "GROUP BY Clienti.CODICE ORDER BY Clienti.PROGFATIVA + Clienti.PROGFATIVAP DESC LIMIT :listOffset, :perPage";

            $st = $conn->prepare($sql);

            $st->bindValue(":searchTerm", $searchTerm, PDO::PARAM_STR);
            $st->bindValue(":listOffset", $listOffset, PDO::PARAM_INT);
            $st->bindValue(":perPage", $perPage, PDO::PARAM_INT);

            try {
                $st->execute();

            } catch (Exception $e) {
                echo $e->getMessage() . "Client.php getClientList Line 274";
                exit;
            }
            


            //print_r($st->errorInfo());

            $list = array();

            /*return list array with records retrieved from main query and number of rows from the secondary query*/
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                $list['record'][] = new Client($row);
            }
            
            $list['numOfRows'] = $numOfRows;

            $conn = null;
            return $list;
        }
    }
    
    /**
     * Retrieve the client table for storage on the client

    public static function getAllClients() {

            $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);

            $sql = "SELECT * FROM Clienti ";

            $st = $conn->prepare($sql);

            $st->execute();
            $list = array();

            while ($row = $st->fetch()) {
                $list[] = new Client($row);
            }
						
            $conn = null;
            return $list;
    }
     */
	
	
}

?>
