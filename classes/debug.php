<?php
if (!isset($_SESSION)) {
    session_start();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../config.php');
require_once('../classes/DBConnection.php');

class Debug extends DBConnection
{
    function __construct()
    {
        parent::__construct();
    }

    function test_register()
    {
        $test_data = [
            'firstname' => 'Test',
            'lastname' => 'User',
            'contact' => '1234567890',
            'gender' => 'Male',
            'email' => 'test@example.com',
            'password' => 'password123',
            'default_delivery_address' => 'Test Address'
        ];

        $_POST = $test_data;

        try {
            $data = "";
            $_POST['password'] = md5($_POST['password']);

            foreach ($_POST as $k => $v) {
                if (!in_array($k, array('id'))) {
                    if (!empty($data))
                        $data .= ",";
                    $data .= " `{$k}`='" . $this->conn->real_escape_string($v) . "' ";
                }
            }

            // Print the SQL that would be executed
            $sql = "INSERT INTO `clients` set {$data}";
            echo "SQL Query: " . $sql . "\n";

            // Try to execute the query
            $save = $this->conn->query($sql);
            if ($save) {
                echo "Query executed successfully\n";
                echo "New user ID: " . $this->conn->insert_id . "\n";
            } else {
                echo "Query failed\n";
                echo "Error: " . $this->conn->error . "\n";
            }

        } catch (Exception $e) {
            echo "Exception occurred: " . $e->getMessage() . "\n";
        }
    }
}

// Run the test
$debug = new Debug();
$debug->test_register();
?>