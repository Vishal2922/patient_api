<?php
class Database
{
    private $host = "127.0.0.1";
    private $db_name = "hospital_db";
    private $username = "root";
    private $password = ""; 
    private $port = 3308;  
    public $conn;

    public function getConnection()
    {
        $this->conn = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->db_name,
            $this->port
        );

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        return $this->conn;
    }
}
