<?php

/**
 * Class to retrieve item data from the database
 */
class Item {
// Properties

    /**
     * @var string The item's codice
     */
    public $codiceArticolo = null;

    /**
     * @var string The item's description
     */
    public $descrizione = null;

    /**
     * @var string The item's codice fornitore1
     */
    public $codForn1 = null;

    /**
     * @var string The item's codice fornitore2
     */
    public $codForn2 = null;

    /**
     * @var string The item's fascia sconto
     */
    public $fasciaSconto = null;

    /**
     * @var string The item's sconto1
     */
    public $sconto1 = null;

    /**
     * @var string The item's sconto2
     */
    public $sconto2 = null;

    /**
     * @var string The item's Prezzo lordo
     */
    public $prezzoLordo = null;

    /**
     * @var string The item's Fascia listino
     */
    public $fasciaListino = null;

    /**
     * @var string The item's codice listino
     */
    public $codiceListino = null;

    /**
     * @var string The item's category
     */
    public $categoriaArticolo = null;

    /**
     * @var string The item's Prezzo netto
     */
    public $prezzoNetto = null;

    /**
     * @var string The item's fornitore
     */
    public $fornitoreArticolo = null;

    /**
     * @var string Prezzo promozionale (se esiste)
     */
    public $prezzoProm = null;
    /**
     * @var string Prezzo migliore agenti (se esiste)
     */
    public $PrezzoNettoAgenti = null;   
    /**
     * @var string Descizione promozione
     */
    public $descrProm = null;

    /**
     * @var string Scadenza promozione
     */
    public $scadenzaProm = null;

    /**
     * @var string Disponibilità filiale di Casoria
     */
    public $dispCa = null;

    /**
     * @var string Disponibilità filiale di Caserta
     */
    public $dispCe = null;

    /**
     * @var string Disponibilità filiale di Pozzuoli
     */
    public $dispPo = null;

    /**
     * @var string Disponibilità filiale di totale
     */
    public $dispTot = null;

    /**
     * Sets the object's properties using the values in the supplied array
     *
     * @param assoc The property values
     */
    public function __construct($data = array()) {
        if (isset($data['CodiceArticolo']))
            $this->codiceArticolo = trim($data['CodiceArticolo']);
        if (isset($data['DescrizioneArticolo']))
            $this->descrizione = trim($data['DescrizioneArticolo']);
        if (isset($data['CodiceAlternativo1']))
            $this->codForn1 = trim($data['CodiceAlternativo1']);
        if (isset($data['CodiceAlternativo2']))
            $this->codForn2 = trim($data['CodiceAlternativo2']);
        if (isset($data['FasciaSconto']))
            $this->fasciaSconto = trim($data['FasciaSconto']);
        if (isset($data['Sconto1']))
            $this->sconto1 = trim($data['Sconto1']);
        if (isset($data['Sconto2']))
            $this->sconto2 = trim($data['Sconto2']);
        if (isset($data['PrezzoLordo']))
            $this->prezzoLordo = trim(str_replace("." , "," ,$data['PrezzoLordo']));
        if (isset($data['FasciaListino']))
            $this->fasciaListino = trim($data['FasciaListino']);
        if (isset($data['codice']))
            $this->codiceListino = trim($data['codice']);
        if (isset($data['DESCR']))
            $this->categoriaArticolo = trim($data['DESCR']);
        if (isset($data['PREZZONETTO']))
            $this->prezzoNetto =  trim(str_replace("." , "," ,$data['PREZZONETTO']));
        if (isset($data['FORNITORE']))
            $this->fornitoreArticolo = trim($data['FORNITORE']);
        if (isset($data['PrezzoProm']))
            $this->prezzoProm = trim(str_replace("." , "," ,$data['PrezzoProm']));
        if (isset($data['PrezzoNettoAgenti']))
            $this->PrezzoNettoAgenti = trim(str_replace("." , "," ,$data['PrezzoNettoAgenti']));
        if (isset($data['DescrProm']))
            $this->descProm = trim($data['DescrProm']);
        if (isset($data['ScadenzaProm']))
            $this->scadenzaProm = trim($data['ScadenzaProm']);
        if (isset($data['DispCasoria']))
            $this->dispCa = $data['DispCasoria'];
        if (isset($data['DispCaserta']))
            $this->dispCe = $data['DispCaserta'];
        if (isset($data['DispPozzuoli']))
            $this->dispPo = $data['DispPozzuoli'];
        if (isset($data['DispTotale']))
            $this->dispTot = $data['DispTotale'];
    }

    /**
     * Get item list
     */
    public static function getItemList($searchTerm, $fasciaSconto, $searchOptions, $listOffset, $perPage) {
        if ($searchTerm != "") {

            try {
                $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
                $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

            } catch (Exception $e) {
                echo $e->getMessage() . " Item.php getItemtList 173";
                exit;
            }

            $listOffset = (int)$listOffset;
            $perPage = (int)$perPage;

            
            /*Sql select for counting number of rows returned by main query which follows*/
            $countSql = "SELECT count(*) AS total
                                FROM (SELECT count(CodiceArticolo)
                                FROM Listini_Norm
                                WHERE (";
            
            
            
            $sql = "SELECT Listini_Norm.CodiceArticolo,
                                     Listini_Norm.DescrizioneArticolo,
                                     Listini_Norm.CodiceAlternativo1,
                                     Listini_Norm.CodiceAlternativo2,
                                     Listini_Norm.FORNITORE,
                                     sum(if(Giacenze.IDMAG = 27 or Giacenze.IDMAG = 28, Giacenze.GIACENZA, 0)) as DispCasoria,
                                     sum(if(Giacenze.IDMAG = 29, Giacenze.GIACENZA, 0)) as DispCaserta,
                                     sum(if(Giacenze.IDMAG = 30, Giacenze.GIACENZA, 0)) as DispPozzuoli,
                                     COALESCE(sum(Giacenze.GIACENZA),0) as DispTotale
                                     FROM Listini_Norm 
                                     LEFT JOIN Giacenze ON Listini_Norm.CodiceArticolo = Giacenze.CODART
                                     WHERE (";

            $options = array();

            if (in_array("descrizione", $searchOptions)) {
                $options[] = "Listini_Norm.DescrizioneArticolo like concat('%', :searchTerm, '%')";
            }

            if (in_array("codiceSider", $searchOptions)) {
                $options[] = "Listini_Norm.CodiceArticolo like concat('%', :searchTerm, '%')";
            }
            
            if (in_array("fornitore", $searchOptions)) {
                $options[] = "Listini_Norm.FORNITORE like concat('%', :searchTerm, '%')";
            }
            
            if (in_array("codiceForn", $searchOptions)) {
                $options[] = "Listini_Norm.CodiceAlternativo1 like concat('%', :searchTerm, '%') OR
                    Listini_Norm.CodiceAlternativo2 like concat('%', :searchTerm, '%')";
            }

            if (count($options) < 1) {
                $options[] = "Listini_Norm.DescrizioneArticolo like concat('%', :searchTerm, '%')";
            }

            if (count($options) > 1) {
                $andOr = "OR";
            } else {
                $andOr = "";
            }
            
            /*Sql select for counting number of rows returned by main query is constructed here*/
            $countSql .= implode(" {$andOr} ", $options) . ") AND Listini_Norm.FasciaSconto = :fasciaSconto
                                                              GROUP BY Listini_Norm.CodiceArticolo) as row_count";
            $result = $conn->prepare($countSql); 
            $result->bindValue(":searchTerm", $searchTerm, PDO::PARAM_STR);
            $result->bindValue(":fasciaSconto", $fasciaSconto, PDO::PARAM_STR);

            try {
                $result->execute();

            } catch (Exception $e) {
                echo $e->getMessage() . "Item.php getItemList Line 242";
                exit;
            }

            $numOfRows = $result->fetchColumn();

            /*Main query is constructed here*/
            $sql .= implode(" {$andOr} ", $options) . ") AND Listini_Norm.FasciaSconto = :fasciaSconto
                                                       GROUP BY Listini_Norm.CodiceArticolo
                                                       ORDER BY DispTotale DESC, Listini_Norm.DescrizioneArticolo LIMIT :listOffset, :perPage";

            $st = $conn->prepare($sql);
            $st->bindValue(":searchTerm", $searchTerm, PDO::PARAM_STR);
            $st->bindValue(":fasciaSconto", $fasciaSconto, PDO::PARAM_STR);
            $st->bindValue(":listOffset", $listOffset, PDO::PARAM_INT);
            $st->bindValue(":perPage", $perPage, PDO::PARAM_INT);


            try {
                $st->execute();

            } catch (Exception $e) {
                echo $e->getMessage() . "Item.php getItemList Line 264";
                exit;
            }


            $list = array();

            /*return list array with records retrieved from main query and number of rows from the secondary query*/
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                $list['record'][] = new Item($row);
            }

            $list['numOfRows'] = $numOfRows;
            
            $conn = null;
            return $list;
        }
    }

    /**
     * Get item details using codice articolo
     */
    public static function getItemById($codiceArticolo, $fasciaSconto) {

        try {
            $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

        } catch (Exception $e) {
            echo $e->getMessage() . " Item.php getItemById 293";
            exit;
        }
                   
        //Trova il prezzo dal listino clienti normale
            $st = $conn->prepare("SELECT Listini_Norm.CodiceArticolo,
                                     Listini_Norm.DescrizioneArticolo,
                                     Listini_Norm.CodiceAlternativo1,
                                     Listini_Norm.CodiceAlternativo2,
                                     Listini_Norm.FasciaSconto,
                                     Listini_Norm.Sconto1,
                                     Listini_Norm.Sconto2,
                                     Listini_Norm.PREZZONETTO,
                                     Listini_Norm.PrezzoLordo,
                                     Listini_Norm.FORNITORE,
                                     Listini_Prom.PrezzoNetto AS PrezzoProm,
                                     Listini_Prom.DescrListino AS DescrProm,
                                     Listini_Prom.DataFine AS ScadenzaProm,
                                     Listini_Pers.PrezzoNetto as PrezzoNettoAgenti,
                                     sum(if(Giacenze.IDMAG = 27 or Giacenze.IDMAG = 28, Giacenze.GIACENZA, 0)) as DispCasoria,
                                     sum(if(Giacenze.IDMAG = 29, Giacenze.GIACENZA, 0)) as DispCaserta,
                                     sum(if(Giacenze.IDMAG = 30, Giacenze.GIACENZA, 0)) as DispPozzuoli,
                                     COALESCE(sum(Giacenze.GIACENZA),0) as DispTotale
                                     FROM Listini_Norm 
                                     LEFT JOIN Listini_Prom ON Listini_Norm.CodiceArticolo = Listini_Prom.CodiceArticolo
                                     LEFT JOIN Listini_Pers ON Listini_Norm.CodiceArticolo = Listini_Pers.CodiceArticolo AND Listini_Pers.CodListino = 50
                                     LEFT JOIN Giacenze ON Listini_Norm.CodiceArticolo = Giacenze.CODART
                                     WHERE Listini_Norm.CodiceArticolo = :codiceArticolo
                                     AND Listini_Norm.FasciaSconto = :fasciaSconto");
            $st->bindValue(":codiceArticolo", $codiceArticolo, PDO::PARAM_STR);
            $st->bindValue(":fasciaSconto", $fasciaSconto, PDO::PARAM_STR);

        try {
            $st->execute();

        } catch (Exception $e) {
            echo $e->getMessage() . "Item.php getItemList Line 329";
            exit;
        }


        $row = $st->fetch();
        
        $conn = null;

        return new Item($row);
    }

    /**
     * Get item details using barcode
     */
    public static function getItemByBarcode($barcode, $fasciaSconto) {

        try {
            $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

        } catch (Exception $e) {
            echo $e->getMessage() . " Item.php getItemByBarcode 351";
            exit;
        }
                   
        //Trova il prezzo dal listino clienti normale
            $st = $conn->prepare("SELECT Listini_Norm.CodiceArticolo,
                                    Listini_Norm.DescrizioneArticolo,
                                    Listini_Norm.CodiceAlternativo1,
                                    Listini_Norm.CodiceAlternativo2,
                                    Listini_Norm.FasciaSconto,
                                    Listini_Norm.Sconto1,
                                    Listini_Norm.Sconto2,
                                    Listini_Norm.PREZZONETTO,
                                    Listini_Norm.PrezzoLordo,
                                    Listini_Norm.FORNITORE,
                                    Listini_Prom.PrezzoNetto AS PrezzoProm,
                                    Listini_Prom.DescrListino AS DescrProm,
                                    Listini_Prom.DataFine AS ScadenzaProm,
                                    Listini_Pers.PrezzoNetto AS PrezzoNettoAgenti,
                                    Sum(if(Giacenze.IDMAG = 27 or Giacenze.IDMAG = 28, Giacenze.GIACENZA, 0)) AS DispCasoria,
                                    Sum(if(Giacenze.IDMAG = 29, Giacenze.GIACENZA, 0)) AS DispCaserta,
                                    Sum(if(Giacenze.IDMAG = 30, Giacenze.GIACENZA, 0)) AS DispPozzuoli,
                                    COALESCE(sum(Giacenze.GIACENZA),0) AS DispTotale
                                    FROM
                                    Listini_Norm
                                    LEFT JOIN Listini_Prom ON Listini_Norm.CodiceArticolo = Listini_Prom.CodiceArticolo
                                    LEFT JOIN Listini_Pers ON Listini_Norm.CodiceArticolo = Listini_Pers.CodiceArticolo AND Listini_Pers.CodListino = 50
                                    LEFT JOIN Giacenze ON Listini_Norm.CodiceArticolo = Giacenze.CODART
                                    INNER JOIN Barcode ON Barcode.CodArt = Listini_Norm.CodiceArticolo
                                    WHERE Barcode.CodBarre = :barcode
                                    AND Listini_Norm.FasciaSconto = :fasciaSconto
                                    HAVING Listini_Norm.CodiceArticolo IS NOT NULL");
            $st->bindValue(":barcode", $barcode, PDO::PARAM_STR);
            $st->bindValue(":fasciaSconto", $fasciaSconto, PDO::PARAM_STR);

        try {
            $st->execute();

        } catch (Exception $e) {
            echo $e->getMessage() . "Item.php getItemByBarcode Line 382";
            exit;
        }


        $row = $st->fetch();
        
        $conn = null;
        return new Item($row);

    }
}

?>
