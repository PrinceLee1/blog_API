<?php
require 'jwt/JWT.php';
define('SECRET_KEY', 'getoffyourolympianheightandlearn');

use Firebase\JWT\JWT;
//here wen made a class and extends it to the jwt.php library
class HandleJwt extends JWT
{
   public function __construct(){ }

   /**
    * This method decrypts the token and returns the user id inside the token
    */
   public static function openTokenfull($token)
   {
       //$jwt = new JWT;
       $decoded = self::decode($token, SECRET_KEY, array('HS256'));
       $decoded_array = (array)$decoded;
       $user = $decoded_array;
       return $user;
   }
   /**
    * This method decrypts the token and returns the user id inside the token
    */
   public static function openToken($token)
   {
       //$jwt = new JWT;
       $decoded = self::decode($token, SECRET_KEY, array('HS256'));
       $decoded_array = (array)$decoded;
      
       $user = $decoded_array['id'];
       return $user;
   }
   /*I TAKE THE DECRYPTED JWT TOKEN AS AN ARGUMENT*/
   public static function checkifcookieisvalid($decryptedJwtArray)
   {
       $expiry_time = $decryptedJwtArray['destruct'];
       return ($expiry_time > time()) ? true : false;
   }
   /**ENCRYPT JWT METHOD */
   public static function encyptJwt($issuer, $audience, $user_id)
   {
       $key = SECRET_KEY; //this is the constant secrete key we defined.
       $token = array(
           "iss" => $issuer, //this means the url address of our server. the jwt library needs to know the sever address that wants to generate the jwt
           "aud" => $audience, // this is the url we want to secure. in our case because we are biulding SPA we are securing the dashboard
           "id" => $user_id, // the identity of the user, in our case is the email address
        //    "bloog" =>$bloog,
            //'u_id' => $unique_id, this is the unique id we get by doing the SHA1 of the email
           // 'destruct' => $tk, this is the destruction time, ie how long the jwt should be alive before destroying it
           "iat" =>time(), // this is the present time that the jwt is generated.
           "nbf" => time() 
       );
       $hu = new JWT; //this is really no necessary because we already extended jwt in this file.
       $jwt = $hu::encode($token, SECRET_KEY); //we use double column (::) to call a static function.
       return $jwt;
   //echo json_encode($jwt);
   }
}


//to get what time will take in 15mins
// since 60 multiply br 15 is 900

//$token_duration = time() + 900
//$token_duration will hold will hold the value of what time will be in 15 mins


?>