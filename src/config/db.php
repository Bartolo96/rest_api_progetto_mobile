<?php
    class db{
        //Properties
        private $dbhost = 'localhost';
        private $dbuser = 'id6273005_nitwx';
        private $dbpass = 'xyjXb$j6mtYDKyBSEtzP';
        private $dbname = 'id6273005_nitwx_progetto_mobile';

        //Connect
        public function connect (){
           $dbConnection = new PDO('mysql:host='.$this->dbhost.';dbname='.$this->dbname, $this->dbuser, $this->dbpass);
           $dbConnection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
           return $dbConnection;
        }

    }