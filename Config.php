<?php

Class Config
{
    private $dbServer;
    private $dbUserName;
    private $dbPassword;
    private $dbName;
    private $connection;

    public function __construct($dbServer, $dbUserName, $dbPassword, $dbName)
    {
        $this->dbServer = $dbServer;
        $this->dbUserName = $dbUserName;
        $this->dbPassword = $dbPassword;
        $this->dbName = $dbName;

        $this->connection = new mysqli($dbServer, $dbUserName, $dbPassword, $dbName);
    }

    function getConnection()
    {
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        return $this->connection;
    }

    function closeConnection(){
        $this->connection->close();
    }
}