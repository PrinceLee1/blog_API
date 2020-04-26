<?php
class dbconn {
    private $servername;
    private $username;
    private $password;
    private $dbname;

    public function connect() {
        $this->servername = "localhost";
        $this->username = "root";
        $this->password = "";
        $this->dbname = "";

        //to return the db connection
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname)or die(mysqli_connect_error());
        return $conn;
    }
}

// class dbconn {
//     protected $db;
//         function __construct(){
//             $this->db = new mysqli('localhost','newuser','password','blog') or die('Connection Failed');
//             return $this->db;
//         }
    
// }
?>
