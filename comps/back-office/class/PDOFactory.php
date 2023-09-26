<?php

class PDOFactory
{
    private static  $pdo_objects	= array();
    private static	$conns = array();

    private $scheme = "mysql";
    private $server = "";
    private $port = "";
    private $dbName = "";
    private $charset = "";
    private $id = "";
    private $pw = "";
    public $db = "";
    public $isDev = true;

    public function __construct()
    {
        global $dbInfo;

        $this->server = $dbInfo['server'];
        $this->port = $dbInfo['port'];
        $this->dbName = $dbInfo['dbName'];
        $this->charset = $dbInfo['charset'];
        $this->id = $dbInfo['id'];
        $this->pw = $dbInfo['pw'];
    }

    public function PDOCreate()
    {
        $dbtype 	= "mysql";
        $server 	= $this->server;
        $port		= isset( $args['port']) ? $args['port']: $this->port;
        $user     	= $this->id;
        $pass     	= $this->pw;
        $database 	= $this->dbName;


        //SET DSN
        $db = null;
        $dsn = '';
        switch( strtolower($dbtype) )
        {
            case 'mysql':
                if($this->isDev) {
                    $dsn = 'mysql:host='.$this->server;
                    $dsn .= ';port='.$this->port;
                    $dsn .= ';dbname='.$this->dbName;
                    $dsn .= ';charset='.$this->charset;
                } else {
                    //SET REAL DB SERVER
                    echo "ERROR";
                }
                break;

            case 'odbc':
                // Server is the DSN for ODBC
                $dsn = 'odbc:'.$server;
                break;
            case 'pgsql':

                $dsn = 'pgsql:host='.$server;

                if( ! empty($port) )
                    $dsn .= ';port='.$port;

                if( ! empty($database) )
                    $dsn .= ';dbname='.$database;

                break;
            case 'mssql':
            case 'dblib':

                $dsn = 'dblib:host='.$server;
                $dsn .= ';charset=utf8';

                if( ! empty($port) )
                    $dsn .= ';port='.$port;

                if( ! empty($database) )
                    $dsn .= ';dbname='.$database;

                break;
            default:
                throw new Exception('Unknown database type: '.$dbtype);
                break;
        }
        $db = new PDO( $dsn, $user, $pass );
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        /*
        * Execute post-creation SQL
        */
        switch( strtolower($dbtype) )
        {
            case 'mysql':
                $db->prepare("set names 'UTF8'")->execute();
                break;
        }
        return $db;
    }

}