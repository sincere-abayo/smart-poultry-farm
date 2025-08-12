<?php
if (!defined('DB_SERVER')) {
    require_once("../initialize.php");
}
class DBConnection
{

    private $host = DB_SERVER;
    private $username = DB_USERNAME;
    private $password = DB_PASSWORD;
    private $database = DB_NAME;

    public $conn;

    public function __construct()
    {
        try {
            if (!isset($this->conn)) {
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
                $this->conn->set_charset("utf8mb4");

                if ($this->conn->connect_error) {
                    throw new Exception('Database connection failed: ' . $this->conn->connect_error);
                }
            }
        } catch (Exception $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }
    public function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        try {
            if (isset($this->conn) && $this->conn instanceof mysqli) {
                $this->conn->close();
            }
        } catch (Exception $e) {
            error_log("Error closing database connection: " . $e->getMessage());
        }
    }

    public function isConnected()
    {
        return isset($this->conn) && $this->conn instanceof mysqli && $this->conn->connect_errno === 0;
    }

    public function reconnect()
    {
        try {
            $this->close();
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
            $this->conn->set_charset("utf8mb4");

            if ($this->conn->connect_error) {
                throw new Exception('Database reconnection failed: ' . $this->conn->connect_error);
            }
            return true;
        } catch (Exception $e) {
            error_log("Database Reconnection Error: " . $e->getMessage());
            throw new Exception("Database reconnection failed. Please try again later.");
        }
    }
}
?>