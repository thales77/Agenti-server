<?php

require( "config.php" );
session_start();
$action = isset($_GET['action']) ? $_GET['action'] : "";
if (!$action)
    $action = isset($_POST['action']) ? $_POST['action'] : "";


// Carry out the appropriate action
if (!in_array($action, array('login', 'logout', 'searchClient', 'searchItem', 'getItemById', 'ultimiAcquisti', 'aqcuistiMaggiori',
            'matNonAqcuist', 'storicoPrezzi', 'getLog', 'getClientTable', 'getOrderList', 'getOrderDetail', 'getItemByBarcode', 'insertItemToInventory'), true))
    $action = 'goHome';

$action();


/**
 * Logs the user in
 */
function login() {
    // User has posted the login form: attempt to log the user in
    if ($user = User::getByUsername($_POST['username'])) {
        if ($user->checkPassword($_POST['password'])) {
            // Login successful: Create a session and return true
            $user->createLoginSession();
            $logMsg = $_POST['username'] . " ha fatto login"; //log
            Log::insertLog($logMsg, 1);

            $response = array(
                'status' => 'ok',
                'username' => $_SESSION['userId'],
                'full_name' => $_SESSION['full_name'],
                'email' => $_SESSION['email'],
                'usertype' => $_SESSION['userType'],
                'idAgente' => $_SESSION['idAgente'],
            );
            echo json_encode($response);
        } else {
            // Login failed: display an error message to the user
            $logMsg = $_POST['username'] . " password errata"; //log
            Log::insertLog($logMsg, 2);
            $response = array(
                'status' => 'bad password'
            );
            echo json_encode($response);
        }
    } else {
        $logMsg = $_POST['username'] . " utente non esistente"; //log
        Log::insertLog($logMsg, 2);
        // Login failed: display an error message to the user
        $response = array(
            'status' => 'bad username'
        );
        echo json_encode($response);
    }
}

/**
 * Search for clients
 */
function searchClient() {
    $results = array();
    $searchOptions = json_decode($_GET['clientSearchOptions'], true);
    $perPage    = $_GET['perPage'];
    $listOffset = $_GET['listOffset'];
    $results = Client::getClientList($_GET['searchTerm'], $searchOptions, $listOffset, $perPage);
    $logMsg = $_GET['user'] . " ha cercato cliente : " . $_GET['searchTerm']; //log
    Log::insertLog($logMsg, 3);
    //echo $_GET['jsonp_callback'] . '(' . $results . ');' ;

    if ($results['numOfRows'] === '0') {
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");

    } else {
        echo json_encode($results);
    }

}

/**
 * Get the whole client table for storage in client
 */
function getClientTable() {
    $results = array();
    $results = json_encode(Client::getAllClients());
    echo $results;
}

/**
 * Search items page
 */
function searchItem() {
    $searchOptions = json_decode($_GET['itemSearchOptions'], true);
    $perPage    = $_GET['perPage'];
    $listOffset = $_GET['listOffset'];
    $results = Item::getItemList($_GET['searchTerm'], $_GET['fasciaSconto'], $searchOptions, $listOffset, $perPage);
    $logMsg = $_GET['user'] . " ha cercato articolo : " . $_GET['searchTerm']; //log
    Log::insertLog($logMsg, 3);

    if ($results['numOfRows'] === '0') {
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");

    } else {
        echo json_encode($results);
    }


}

function getItemById() {
    $results = json_encode(Item::getItemById($_GET['codiceArticolo'], $_GET['fasciaSconto']));
    echo $results;
}

function getItemByBarcode() {
    $results = Item::getItemByBarcode($_GET['barcode'], $_GET['fasciaSconto']);
    $logMsg = $_GET['user'] . " ha cercato barcode : " . $_GET['barcode']; //log
    Log::insertLog($logMsg, 3);

    if ($results->codiceArticolo !== null) {
        echo json_encode($results);
    } else {
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    }
}

/**
 *
 *  Storico prezzi applicati  a un cliente per l'articolo selezionato
 */
function storicoPrezzi() {
    $results = json_encode(SalesHistory::getItemPriceHistory($_GET['itemId'], $_GET['clientId']));
    echo $results;
}

/**
 *
 *  Storico acquisti di un cliente
 */
function ultimiAcquisti() {
    $perPage    = $_GET['perPage'];
    $listOffset = $_GET['listOffset'];
    $results = json_encode(SalesHistory::getClientSalesHistory($_GET['clientId'], $listOffset, $perPage));
    $logMsg = $_GET['user'] . " ultimi acq. per :" . $_GET['clientId']; //log
    Log::insertLog($logMsg, 3);
    echo $results;
}

/**
 *
 *  Storico acquisti maggiori di un cliente
 */
function aqcuistiMaggiori() {
    $perPage    = $_GET['perPage'];
    $listOffset = $_GET['listOffset'];
    $results = json_encode(SalesHistory::getClientMaggiorSalesHistory($_GET['clientId'], $listOffset, $perPage));
    $logMsg = $_GET['user'] . " acq. maggiori per :" . $_GET['clientId']; //log
    Log::insertLog($logMsg, 3);
    echo $results;
}

/**
 *
 *  Consulta il log
 */
function getLog() {
    $results = json_encode(Log::getLogForToday());
    echo $results;
}

/**
 *
 *  Lista ordini
 */
function getOrderList() {
    $results = json_encode(Order::getOrderList($_GET['idAgente'], $_GET['datefrom'], $_GET['dateto']));
    $logMsg = $_GET['user'] . " ha visualizzato ordini"; //log
    Log::insertLog($logMsg, 3);
    echo $results;
}

/**
 *
 *  Dettaglio ordine
 */
function getOrderDetail() {
    $results = json_encode(Order::getOrderDetail($_GET['orderId']));
    $logMsg = $_GET['user'] . "Dettag. ord.: " . $_GET['orderId']; //log
    Log::insertLog($logMsg, 3);
    echo $results;
}

function goHome() {
    header($_SERVER["SERVER_PROTOCOL"]." 204 No Content");
}

function insertItemToInventory() {
    //set TIpMAg to A
    //CodMag MC, PZ, CE
    //CodAll 0
    //QtaRilM 0

    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $codArt = $request->codArt;
    $TipMag = 'A';
    $CodMag =  $request->CodMag;
    $CodAll = 0;
    $QtaRil = $request->QtaRil;
    $QtaRilM = 0;

     Item::insertItemToInventory($codArt, $TipMag, $CodMag, $CodAll, $QtaRil, $QtaRilM);
    $response = array(
        'status' => 'ok'
    );
    header($_SERVER["SERVER_PROTOCOL"]." 201 Created");
    echo json_encode($response);
 }

?>
