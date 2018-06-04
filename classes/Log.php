<?php

/**
 * Class to insert and retrieve log entries from the database
 */
class Log {
// Properties

    
    /**
     * @var The log entry timestamp
     */
    public $logTimestamp = null;
    
    /**
     * @var The current User's ID
     */
    public $logType = null;

    /**
     * @var The log record desciption
     */
    public $logDescr = null;
    
        /**
     * @var The log record desciption
     */
    public $logIp = null;

     

    /**
     * Sets the object's properties using the values in the supplied array
     *
     * @param assoc The property values
     */
    public function __construct($data = array()) {
        if (isset($data['date']))
            $this->logTimestamp = $data['date'];
        if (isset($data['type']))
            $this->logType = $data['type'];
        if (isset($data['msg']))
            $this->logDescr = $data['msg'];
        if (isset($data['ip']))
            $this->logIp = $data['ip'];

    }

    /**
     * Get log for today
     */
    public static function getLogForToday() {
        try {
            $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

        } catch (Exception $e) {
            echo $e->getMessage() . " Log.php getLogForToday 58";
            exit;
        }
        $st = $conn->prepare("SELECT log.date, log.type,log.msg,log.ip FROM log WHERE log.date >= CURDATE() ORDER BY log.date ASC");

        try {
            $st->execute();

        } catch (Exception $e) {
            echo $e->getMessage() . "Log.php getLogForToday Line 67";
            exit;
        }

        $list = array();

        while ($row = $st->fetch()) {
            $list[] = new Log($row);
        }

        $conn = null;
        return $list;
    }


    /**
     * Insert log record
     */
    public static function insertLog($msg, $type) {

        $ip = $_SERVER['REMOTE_ADDR'];

        try {
            $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

        } catch (Exception $e) {
            echo $e->getMessage() . " Log.php insertLog 86";
            exit;
        }

        $st = $conn->prepare("INSERT INTO log (date, type, msg, ip) VALUES(NOW(), :type , :msg, '$ip')");
        $st->bindValue(":msg", $msg, PDO::PARAM_STR);
        $st->bindValue(":type", $type, PDO::PARAM_STR);

        try {
            $st->execute();

        } catch (Exception $e) {
            echo $e->getMessage() . "Log.php insertLog Line 100";
            exit;
        }

        $conn = null;
    }

}

?>
