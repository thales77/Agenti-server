<?php

/**
 * Class to retrieve item historic data from the database
 */
class SalesHistory {
// Properties

    /**
     * @var La data di vendita dell' articolo
     */
    public $dataVendita = null;

    /**
     * @var Il prezzo di vendita 
     */
    public $prezzoVendita = null;

    /**
     * @var La quantità di vendita
     */
    public $quantitaVendita = null;

    /**
     * @var La filiale di vendita
     */
    public $filialeVendita = null;

    /**
     * @var Codice del articolo
     */
    public $codiceArticolo = null;

    /**
     * @var Descrizione articolo
     */
    public $DescArt = null;

    /**
     * @var Prezzo medio di vendita
     */
    public $prezzoMedio = null;

    /**
     * @var Valore reale di vendita
     */
    public $valoreReale = null;

    /**
     * Sets the object's properties using the values in the supplied array
     *
     * @param assoc The property values
     */
    public function __construct($data = array()) {
        if (isset($data['data']))
            $this->dataVendita = date("d-m-Y", strtotime($data['data']));

        if (isset($data['Prezzo']))
            $this->prezzoVendita = str_replace(".", ",", $data['Prezzo']);

        if (isset($data['Quantita']))
            $this->quantitaVendita = str_replace(".", ",", $data['Quantita']);

        if (isset($data['Filiale']))
            $this->filialeVendita = str_replace("MAGAZZINO ", "", str_replace("CENTRALE ", "", $data['Filiale']));

        if (isset($data['CodArt']))
            $this->codiceArticolo = $data['CodArt'];

        if (isset($data['DescArt']))
            $this->DescArt = $data['DescArt'];

        if (isset($data['Prezzo_medio']))
            $this->prezzoMedio = str_replace(".", ",", $data['Prezzo_medio']);

        if (isset($data['Valore_reale']))
            $this->valoreReale = str_replace(".", ",", $data['Valore_reale']);
    }

    public static function getItemPriceHistory($codiceArticolo, $CodCliente) {

        try {
            $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

        } catch (Exception $e) {
            echo $e->getMessage() . " SalesHistory.php getItemPriceHistory Line 87";
            exit;
        }

        $st = $conn->prepare(" SELECT data, Prezzo, Quantita, Filiale FROM Movim_Magazzino WHERE 
                                             CodArt = :codiceArticolo and
                                             CodCliente = :CodCLiente
                                             ORDER BY data DESC");
        $st->bindValue(":codiceArticolo", $codiceArticolo, PDO::PARAM_STR);
        $st->bindValue(":CodCLiente", $CodCliente, PDO::PARAM_STR);

        try {
            $st->execute();

        } catch (Exception $e) {
            echo $e->getMessage() . "SalesHistory.php getItemPriceHistory Line 93";
            exit;
        }


        $list = array();

        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $list[] = new SalesHistory($row);
        }

        $conn = null;
        return $list;
    }

    public static function getClientSalesHistory($CodCliente, $listOffset, $perPage) {
        try {
            $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

        } catch (Exception $e) {
            echo $e->getMessage() . " SalesHistory.php getClientSalesHistory Line 123";
            exit;
        }

        $listOffset = (int)$listOffset;
        $perPage = (int)$perPage;       
        
        /*Sql select for counting number of rows returned by main query which follows*/
        $result = $conn->prepare("SELECT count(*) AS total
                                                    FROM (SELECT DATEDIFF(Movim_Magazzino.data , CURDATE()) AS diff
                                                    FROM Movim_Magazzino 
                                                    INNER JOIN Clienti ON Movim_Magazzino.CodCliente = Clienti.CODICE
                                                    INNER JOIN Listini_Norm ON Movim_Magazzino.CodArt = Listini_Norm.CodiceArticolo
                                                    WHERE Movim_Magazzino.CodCliente = :CodCliente
                                                    AND Listini_Norm.FasciaSconto = Clienti.SCONTO
                                                    HAVING diff > -365) AS row_count");
        $result->bindValue(":CodCliente", $CodCliente, PDO::PARAM_STR);


        try {
            $result->execute();

        } catch (Exception $e) {
            echo $e->getMessage() . "SalesHistory.php getClientSalesHistory Line 129";
            exit;
        }

        $numOfRows = $result->fetchColumn();
        
        
        /*Main query is constructed here*/
        $st = $conn->prepare("SELECT Movim_Magazzino.CodCliente,
                                       Movim_Magazzino.CodArt,
                                       Listini_Norm.DescrizioneArticolo AS DescArt,
                                       Movim_Magazzino.Prezzo,
                                       Movim_Magazzino.Quantita,
                                       Movim_Magazzino.data,
                                       DATEDIFF(Movim_Magazzino.data , CURDATE()) AS diff
                                       FROM Movim_Magazzino
                                       INNER JOIN Clienti ON Movim_Magazzino.CodCliente = Clienti.CODICE
                                       INNER JOIN Listini_Norm ON Movim_Magazzino.CodArt = Listini_Norm.CodiceArticolo
                                       WHERE Movim_Magazzino.CodCliente = :CodCliente AND
                                       Listini_Norm.FasciaSconto = Clienti.SCONTO
                                       HAVING diff > -365 
                                       ORDER BY data DESC, DescArt ASC LIMIT :listOffset, :perPage");    
        
        
        $st->bindValue(":CodCliente", $CodCliente, PDO::PARAM_STR);
        $st->bindValue(":listOffset", $listOffset, PDO::PARAM_INT);
        $st->bindValue(":perPage", $perPage, PDO::PARAM_INT);

        try {
            $st->execute();

        } catch (Exception $e) {
            echo $e->getMessage() . "SalesHistory.php getClientSalesHistory Line 152";
            exit;
        }

        $list = array();

        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $list['record'][] = new SalesHistory($row);
        }
        
        $list['numOfRows'] = $numOfRows;
        
        $conn = null;
        return $list;
    }
    
    
    public static function getClientMaggiorSalesHistory($CodCliente, $listOffset, $perPage) {
        try {
            $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

        } catch (Exception $e) {
            echo $e->getMessage() . " SalesHistory.php getClientMaggiorSalesHistory Line 201";
            exit;
        }

        $listOffset = (int)$listOffset;
        $perPage = (int)$perPage;      
        
         /*Sql select for counting number of rows returned by main query which follows*/
        $result = $conn->prepare("SELECT count(*) AS total
                                    FROM (SELECT count(*), ROUND(SUM(Movim_Magazzino.Prezzo * Movim_Magazzino.Quantita),2) AS Valore_reale
                                    FROM Movim_Magazzino
                                    INNER JOIN Clienti ON Movim_Magazzino.CodCliente = Clienti.CODICE
                                    INNER JOIN Listini_Norm ON Movim_Magazzino.CodArt = Listini_Norm.CodiceArticolo
                                    WHERE
                                    Movim_Magazzino.CodCliente = :CodCliente AND
                                    Listini_Norm.FasciaSconto = Clienti.SCONTO
                                    GROUP BY CodCliente, CodArt
                                    HAVING Valore_reale >= 100) AS row_count");        
        $result->bindValue(":CodCliente", $CodCliente, PDO::PARAM_STR);

        try {
            $result->execute();

        } catch (Exception $e) {
            echo $e->getMessage() . "SalesHistory.php getClientMaggiorSalesHistory Line 200";
            exit;
        }

        $numOfRows = $result->fetchColumn();
        
        
        /*Main query is constructed here*/
        $st = $conn->prepare(" SELECT Movim_Magazzino.CodCliente,
                                        Movim_Magazzino.CodArt,
                                        Listini_Norm.DescrizioneArticolo AS DescArt,
                                        SUM(Movim_Magazzino.Quantita) AS Quantita,
                                        ROUND(AVG(Movim_Magazzino.Prezzo),2) AS Prezzo_medio,
                                        ROUND(SUM(Movim_Magazzino.Prezzo * Movim_Magazzino.Quantita),2) AS Valore_reale
                                        FROM
                                        Movim_Magazzino
                                        INNER JOIN Clienti ON Movim_Magazzino.CodCliente = Clienti.CODICE
                                        INNER JOIN Listini_Norm ON Movim_Magazzino.CodArt = Listini_Norm.CodiceArticolo
                                        WHERE
                                        Movim_Magazzino.CodCliente = :CodCliente AND
                                        Listini_Norm.FasciaSconto = Clienti.SCONTO
                                        GROUP BY CodCliente, CodArt
                                        HAVING Valore_reale >= 100
                                        ORDER BY CodCliente ASC, Valore_reale DESC LIMIT :listOffset, :perPage");    
        
        
        $st->bindValue(":CodCliente", $CodCliente, PDO::PARAM_STR);
        $st->bindValue(":listOffset", $listOffset, PDO::PARAM_INT);
        $st->bindValue(":perPage", $perPage, PDO::PARAM_INT);

        try {
            $st->execute();

        } catch (Exception $e) {
            echo $e->getMessage() . "SalesHistory.php getClientMaggiorSalesHistory Line 226";
            exit;
        }

        $list = array();

        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $list['record'][] = new SalesHistory($row);
        }
        
        $list['numOfRows'] = $numOfRows;
        
        $conn = null;
        return $list;
    }
    
}

?>
