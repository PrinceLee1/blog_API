<?php 
require "vendor/autoload.php";
require "config-cloud.php";
include "dbconnect.php";
require_once('mail.php');
require_once("jwtimplement.php");
$jwt = new HandleJwt;


class valid  {
    public function validatenumber($value) {
        return ctype_digit($value)?true:false;
    }

    public function validatealnum($value) {
        return ctype_alnum($value)?true:false;
    }

    public function validateemail($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL)?true:false;
    }
public function validate_url($url){
    return filter_var($url,FILTER_SANITIZE_URL,FILTER_VALIDATE_URL)?true:false;
}
    public function randomize(){
        $val = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $shuffle= str_shuffle($val); 
        $result= substr($shuffle,3,12);
        return $result;
    
    }

    public function sanitize($data) {
$data = trim($data);
$data = stripslashes($data);
$data = htmlspecialchars($data);
$data = addslashes($data);
return $data;
}



    public function imageUpload($file){
       $errors = [];
       $file_name =  $file["name"];
       $file_size = $file["size"];
       $file_tmp = $file["tmp_name"];
       $file_type = $file["type"];
       $file_ext = strtolower(pathinfo($file_name,PATHINFO_EXTENSION));
       $extension = array("jpeg","jpg","png","gif");
       $bytes = 1024;
       $allowedKb = 100;
       $totalBytes = $allowedBytes * $bytes;

       if(!in_array($file_ext,$extension)){
           array_push($errors,"File type is invalid,please select image only");
           return $errors;
       }
       $count = count($errors);
       if($count == 0){
           $n = microtime();
           $n = str_replace(" ",'_',$n);
           $n = str_replace('0.','',$n);
        //    print_r($n);exit;
           if(!$result = \Cloudinary\Uploader::upload($file_tmp,array("public_id"=>$n))){
            // print_r($result);exit;
               return array('error'=>'could not save uploaded file');
           }else{
            // print_r($result);exit;
               return $result;
           }
       }else{
           return $errors;
       }
        }
}

class user extends dbconn{
    public function getusers() {
        $sql = "SELECT * FROM user_articles";
        $result = $this->connect()->query($sql);
        // $numrows = $result->num_rows;
        if($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function getusers1() {
        $sql = "SELECT * FROM categories";
        $result = $this->connect()->query($sql);
        // $numrows = $result->num_rows;
        if($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function replace($table,$column,$inserts){
        $sql = "REPLACE INTO $table ($column) VALUES ($inserts)";
        $result = $this->connect()->query($sql);
        return $result ? true : false;
    }


    public function tokenVerify($token){
        $time = time();
       $getToken = $this->select_fetch_assoc("user_id","access_tokens","WHERE token='$token'AND date_expires > '$time'");
       if($getToken){
           $selToken = $getToken[0]['user_id'];
           $issuer="http://localhost:4200";
           $audience="http://localhost:4200/#/";
           $user_id = $selToken;
           $enctjwt=$jwt::encyptJwt($issuer,$audience,$user_id);
            
       }
    }
    //a method that runs INSERT queries
    public function insert($tab, $col, $val) {
        $sql = "INSERT INTO $tab ($col) VALUES($val)";
        $result = $this->connect()->query($sql);
        return $result ? true : false;
    }

    //a method that runs SELECT queries
    public function select($col, $tab, $where) {
        $sql = "SELECT $col FROM $tab WHERE $where";
        $result = $this->connect()->query($sql);
        if($result) {
            $found = $result->num_rows;
            return $found>0; //? true : false;
        }
    }


public function forgotPass($email){
    // print_r($email);die;
     $fetch =   $this->select_fetch_assoc("user_id","register","WHERE email='$email'");
    //  var_dump($fetch);die;
    if($fetch){
        $reg_id = $fetch[0]['user_id'];
    // print_r($reg_id);die;

        $token = openssl_random_pseudo_bytes(32);
        $token = bin2hex($token);
        $endTime = strtotime("+15 minutes");
        $insert = $this->replace("access_tokens","user_id,token,time_expires","'$reg_id','$token','$endTime'");
        if ($insert){
        $link = "http://localhost:4200/reset-password/$token";
        $message = "This email is in response to a forgotten password. You can reset your password by clicking this link $link .
        This link will expire within 15 minuites, but if you did not make this request please ignore this mail.";
        $mew = mail::Mailing('noreply@discuss.com','majornwa189@gmail.com','Signup | Verification',$message);
        if ($mew){
            return true ;
        }else{return 'error';}
        }
    }else{return 'invalid request made';}
}
    
// NEW METHOD  

function select_f($col,$tab,$where){
// echo "SELECT $col FROM $tab $where"; die;
   $sel= $this->connect()->query("SELECT $col FROM $tab $where");
    if($sel->num_rows > 0){
        return  true;
    } else {
        return false;
    }
}

function select_logFetch($col,$tab,$where){
    // echo "SELECT $col FROM $tab $where"; die;
       $sel= $this->connect()->query("SELECT $col FROM $tab $where");
        if($sel->num_rows > 0){
            return 1;
        }
    }
//CHECK B4 INSERTION
function Check_Insert($col,$tab,$where){ 
    $sel= $this->connect()->query("SELECT $col FROM $tab WHERE $where");
    if($sel->num_rows<=0){
    $int= $this->connect()->query("INSERT INTO $tab($col)VALUES($val)");
    }else{return false;}
}
   
 //FETCH OUT SINGLE ROW   

    // function Select_Single_Row($col,$tab,$where){
    //     $result = $this->connect()->query("SELECT $col FROM $tab $where");
    //     if($result->num_rows>0){
    //         return $result->fetch_object();
    //     }
    // }

 // END OF NEW METHOD   

    //a method that runs SELECT queries and fetches results
    public function select_fetch($col, $tab, $where) {
        $sql = "SELECT $col FROM $tab $where";
        $result = $this->connect()->query($sql);
        if ($result) {
            $found = $result->num_rows;
            if ($found > 0) {
                while($fetch = $result->fetch_object()) {
                    return $fetch;
                }
            }
        }
    }
    public function select_fetch_assoc($col, $tab, $where) {
        $sql = "SELECT $col FROM $tab $where";
        $result = $this->connect()->query($sql);
        if ($result) {
            $found = $result->num_rows;
            if ($found > 0) {
                while($fetch = $result->fetch_assoc()) {
                    $main[]=$fetch;
                }
            }
            
        }
        return $main;
    }
    public function select_fetch_assoc_and($col, $tab, $where,$and) {
        $sql = "SELECT $col FROM $tab $where $and";
        $result = $this->connect()->query($sql);
        if ($result) {
            $found = $result->num_rows;
            if ($found > 0) {
                while($fetch = $result->fetch_assoc()) {
                    return $fetch;
                }
            }
            
        }
        return $main;
    }
    public function selfetch($col, $tab) {
        $sql = "SELECT $col FROM $tab";
        $result = $this->connect()->query($sql);
        if ($result) {
            $found = $result->num_rows;
            if ($found > 0) {
                while($fetch = $result->fetch_object()) {
                    return $fetch;
                }
            }
        }
    }
    
    public function test($col,$as,$tab){
$sql = "SELECT SUM($col) AS $as FROM $tab";
$result = $this->connect()->query($sql);
$t = count($result);
return $t;
    }


    //a method that runs UPDATE queries
    public function update($tab, $set, $where) {
        $sql = "UPDATE $tab SET $set WHERE $where";
        $result = $this->connect()->query($sql);
        return $result ? true : false;
    }

    //a method that creates database
    public function create_db($dbname) {
        $sql = "CREATE DATABASE IF NOT EXISTS `$dbname`";
        $result = $this->connect()->query($sql);
        return $result ? true : false;
    }

    //method for selecting db
    public function select_db($dbname) {
        $sql = mysqli_select_db($this->connect(), $dbname);
        return $sql ? true : false;
    }

    //method for creating table
    public function create_tab($tabname, $cols) {
        $sql = "CREATE TABLE IF NOT EXISTS $tabname($cols)";
        $result=$this->db->query($sql);
        return $result ? true : false;
    }
    
    public function createtable(){
        $sql = "CREATE TABLE personal_info(id INT(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,fullname VARCHAR(50) NOT NULL, Address VARCHAR(600) NOT NULL, country VARCHAR(50) NOT NULL,state VARCHAR(50) NOT NULL,phone INT(11) NOT NULL) ";
        if ($this->connect()->query($sql)){
            return true;
        }else {
            return false;
        }
    }
    public function createtable1(){
        $sql = "CREATE TABLE articles(id INT(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,userID VARCHAR(50) NOT NULL, Title VARCHAR(600) NOT NULL, article VARCHAR(1000) NOT NULL) ";
        if ($this->connect()->query($sql)){
            return true;
        }else {
            return false;
        }
    }
    //method for selecting db and creating table
    public function sel_db_create_tab($dbname, $tabname,$col= '') {
        $selectdb =  mysqli_select_db($this->connect(), $dbname);
        $sql = "CREATE TABLE IF NOT EXISTS $tabname($cols)";
        $result = $this->connect()->query($sql);
        return $result ? true : false;
    }

    public function delete($tab,$where){
        $sql = "DELETE FROM $tab WHERE $where";
        $result = $this->connect()->query($sql);
        return $result ? true : false;
    }
}

?>
