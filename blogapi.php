<?php


header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Headers: X-Requested-With, Authorrization, content-type, access-control-allow-origin, access-control-allow-methods, access-control-allow-headers");
$post = json_decode(file_get_contents('php://input'),TRUE);
// require "vendor/mailgun/mailgun-php/src/Mailgun/Mailgun.php";
require 'vendor/autoload.php';
require_once('mail.php');
use Mailgun\Mailgun;
include "jwtimplement.php";
include "myFunctions.php";
$jwt = new HandleJwt;
$class = new user;
$validate = new valid;
$key = $post["key"];
$code = "";
$info = '';
$msg = '';
$response = '';
if($_POST){
    // print_r($_FILES);exit;
  if($_POST['key']=="11"){
  $file = $_FILES['image'];
  $blog_id = $_POST['blog_id'];
  $cookie = $_POST['id'];
  $chkcookie = $jwt::openToken($cookie); 
  $forImage = $validate->imageUpload($file);
   if($forImage){
    $pix = $forImage['secure_url'];
    // print_r($pix);exit;
    $inst = $class->update("user_articles","image='$pix'","blog_id='$blog_id' and user_id='$chkcookie'");

   }
}
}
if($_POST){
  if($_POST['key']=="acctpic"){
  $files = $_FILES['image'];
  $profilecookie = $_POST['id'];
  $chkpcookie = $jwt::openToken($profilecookie); 
  $imageprofile = $validate->imageUpload($files);
   if($imageprofile){
    $profpix = $imageprofile['secure_url'];
$success = $class->update("personal_info","profile_pic='$profpix'","account_id='$chkpcookie'");
   }
}
}

    if(!empty($key)){

//Beginning of Sign-up 
if ($key == "1"){ 
if($validate->validateemail($post['email'])){//Validate Email here with an Outside method validateemail() from myfunctions.php file
    if (!empty($post['name']&& $post['surname'])){//Checks for empty input field
    if(strlen($post['password'])>=6 && $validate->validatealnum($post['password'])){//Checks password lenght and checks if it is alphanumeric
                        $email= $post['email'];
                            $pass = password_hash($post['password'],PASSWORD_DEFAULT);//Hashes the password for security
                                    $name = $post['name'];$surname = $post['surname']; 
                                            $random = $validate->randomize();//Get and generate a random number
                                            $time = date('d-m-Y');
                                            $mailMessage = 'Thank You for signing up on Discuss, we will send you a confirnmation link to verify your account.Please check your Mail. Log in to get personalized story recommendations,
                                            follow authors and topics you love, and interact with stories.';//Message to display in Users mailbox
                 $select = $class->select_fetch("email","register","WHERE email='$email'");
                                if ($select){
                   $code = "01"; $info = 'Email already Exist';}
                    else if( $registerUser = $class->insert("register","name,surname,email,password,user_id,time","'$name', '$surname','$email','$pass','$random','$time'")){
                        $me = mail::Mailing('noreply@discuss.com',$email,'Welcome to Discuss',$mailMessage);//Calls the Mailling method to send mail to user
                        if($me){
                             $code = "00"; $info = "Sign up successfull";  
                        }
                      }else{
                            $code = "01"; $info = "Signup Failed";
                        }
            }else{$code="01"; $info='Password should contain alphabets and numbers and password length should be atleast six(6) characters.';}
        }else{$code = "01"; $info = 'Name or Surname is Empty.';}
        }else{$code="01"; $info ='Invalid Email Address provided.';}
    }
//End of Sign-up

//Beginning of Login
if ($key == "2"){
    $email = $post['email'];
    if ($validate->validateemail($email)){//Validate if its in email format
  $sel = $class->select_fetch("email,password,user_id","register","WHERE email='$email'");
  if($sel){
      if(password_verify($post['password'],$sel->password)){//Verifies if current password matches with the one in database.
        /****This is for JWT to store user */
        $issuer="http://localhost:4200";
        $audience="http://localhost:4200/#/";
        $user_id = $sel->user_id;
        $t=time() + 100;
        $unique_id=sha1($user_id);
        $form=$jwt::encyptJwt($issuer,$audience,$user_id);
        /****End of JWT Action */

            $code = "00"; $info = $form; 
      }else{$code = "01";$info = 'Wrong Password.';}
  }else{
 $code = "01"; $info = 'Invalid Login.';
  }}else{$code = "01"; $info = 'Invalid email provided.';}
            }
        //End of Login
        //Beginning of Write Articles,this part handles the logic when user wants to write a post or Article//
        if($key == "3"){
        $bloG_id = $post['blog_id'];
        $cookie = $post['author_id'];
        $chkcookie = $jwt::openToken($cookie); 
        $title = $post['title'];
        $san = $validate->sanitize($title);//Cleans the inputs from commas,Quotes,escapes strings too
        $cat = $post['category'];
        $article = $post['article'];
        $time = date('d-m-Y');
        $sanArticle = $validate->sanitize($article);//Refer to line 112
        $pub = $post['publish_type'];
        $selTitle = $class->select_fetch_assoc("title,category","user_articles","WHERE blog_id='$bloG_id'");
        if($cat == ""){$cat = $selTitle[0]['category'];}
            if(empty($article)){
                $code = "01"; $info = 'No Article Provided';
            }else if(empty($pub)){
                $code = "01"; $info = 'You should save as "Published" or "Draft"';
            }else{
                $inst = $class->update("user_articles","title='$san',category='$cat',article='$article',publish='$pub',uploadTime='$time'","blog_id='$bloG_id'");
                $code = "00"; $info = 'Article Submitted';    
            }
        }
        //End of Write Articles


        if ($key == "4"){
            $patUser = $post['cookie'];           
            $chkcookie = $jwt::openToken($patUser); 
            $sel = $class->select_fetch_assoc("*","user_articles","WHERE user_id='$chkcookie'");
                            foreach($sel as $value){
                                $value = $sel;
                                if($value){
                                     $code = "00"; $info = $value;
                                }else{
                                    $code = "01"; $info = 'Cannot get Data!';
                                          }
                                       }
                       
        }
        if ($key == "forgot"){
            $forgotEmail = $post['email'];
            if(empty($forgotEmail)){
                $code = "01"; $info = 'Please provide an Email';
            }else if($validate->validateemail($forgotEmail)){
                $lee = $class->forgotPass($forgotEmail);
                if($lee){
                    $code = "00"; $info = "Email sent,please check your email.";
                }
            }else{
                $code = "01"; $info = 'Invalid Email format!';

            }
        }

        if ($key == "allpost"){
            $sel = $class->select_fetch_assoc("*","user_articles","WHERE publish='publish'");
                            foreach($sel as $value){
                                $value = $sel;
                                if($value){
                                     $code = "00"; $info = $value;
                                }else{
                                    $code = "01"; $info = 'Cannot get Data!';
                                          }
                                       }
                       
        }
        if ($key == "check"){
            $paUser = $post['cookies'];        
            $sele = $class->select_fetch_assoc("*","user_articles","WHERE blog_id='$paUser'");
                            
                             $authorid = $sele[0]['user_id'];
                             $blog = $class->select_fetch_assoc("*","user_articles","WHERE user_id='$authorid'");
              if($blog){
                  $code ="00"; $info = $blog;
              }
            }
              if ($key == "account"){
                  $user = $post['user'];
                  $usercookie = $jwt::openToken($user); 
                  $fullname = $post['name'];
                  $country = $post['country'];
                  $state = $post['state'];$address = $post['address'];$phone = $post['phone'];$facebook = $post['facebook'];$twitter = $post['twitter'];$gmail = $post['gmail'];
                  $chkAcct = $class->select_fetch("account_id","personal_info","WHERE account_id='$usercookie'");
                if($validate->validate_url($facebook && $twitter && $gmail)){
                  if(empty($fullname && $country && $state && $phone && $address)){
                    $code = "01";$info = 'Sorry, feilds with "*" are compulsory! ';
                  }elseif($chkAcct){
                    $inst = $class->update("personal_info","fullname='$fullname',address='$address',country='$country',state='$state',phone='$phone',facebook_url='$facebook',twitter_url='$twitter',gmail_url= '$gmail'","account_id='$usercookie'");
                    $code = "00"; $info = 'Account Information Updated successfully!';
                }else{
                    $saveAcct = $class->insert("personal_info","fullname,address,country,state,phone,facebook_url,twitter_url,gmail_url,account_id","'$fullname','$address','$country','$state','$phone','$facebook','$twitter','$gmail','$usercookie'");

                    $code = "00"; $info = 'Account Information Saved!';
                }
            }else{$code = "01"; $info = 'Check if Facebook,Twitter,Gmail are valid Urls';}
              }
        if($key == "19"){
            $actinf = $post['acctinfo'];
            $acctcookie = $jwt::openToken($actinf); 
            $selAcct = $class->select_fetch_assoc("*","personal_info","WHERE account_id='$acctcookie'");
            $actname = $selAcct[0]['fullname'];
            $actaddress = $selAcct[0]['address'];
            $fbAcct = $selAcct[0]['facebook_url'];
            $twiAcct = $selAcct[0]['twitter_url'];
            $gmailAcct = $selAcct[0]['gmail_url'];
            $actcountry = $selAcct[0]['country'];
            $actstate = $selAcct[0]['state'];
            $actnumber = $selAcct[0]['phone'];
            $actimage = $selAcct[0]['profile_pic'];
            $allDetails = [$actname,$actaddress,$fbAcct,$twiAcct,$gmailAcct,$actcountry,$actstate,$actnumber,$actimage];
            // var_dump($allDetails);die;
            if($selAcct){
                $code = "00"; $info = $allDetails;

            }
        }

        if ($key == "getDrafts"){
            $cookieforDrafts = $post['draftscookie'];
            $chkcookie = $jwt::openToken($cookieforDrafts); 
            //  var_dump($chkcookie);die;
            if($chkcookie){
                $getDraft = $class->select_fetch_assoc("*","user_articles","WHERE user_id='$chkcookie' and  publish='draft'");
            $code = "00"; $info = $getDraft;
            }else{$code = "01"; $info = 'No Drafted Article(s)   ';}
            $getpublish = $class->select_fetch_assoc("*","user_articles","WHERE user_id='$chkcookie' and  publish='publish'");
            if($getpublish ){
                $code = "00"; $msg = $getpublish;
            }else{$code = "01"; $msg = 'No Published Article(s)   ';}            

        }

        if ($key == "5"){
            $sel = $class->selfetch("*","categories");
            foreach($sel as $value){
                $value = $class->getusers1($sel);
                if($value){
                     $code = "00"; $info = $value;
                }else{
                    $code = "01"; $info = 'Cannot get Data!';
                          }
                       }
        }
        if ($key == "6"){
            $blogId = $post['blogId'];
            $sel = $class->select_fetch_assoc("*","user_articles","WHERE blog_id='$blogId'");
                if($sel){
                     $code = "00"; $info = $sel;
                }else{
                    $code = "01"; $info = 'Cannot get Data!';
                          }
                       
        }
        if($key == "7"){
            $cookie = $post['name_on_dash'];
            $chkcookie = $jwt::openToken($cookie);
            
                    $getAuthorName = $class->select_fetch_assoc("name","register","WHERE user_id='$chkcookie'");
            $authorName = $getAuthorName[0]['name'];

            $code = "00"; $info = $authorName;
        }
        if($key == "8"){
            $cookie = $post['info_of_user'];
            $chkcookie = $jwt::openToken($cookie);
                    $getinfoUser = $class->select_fetch_assoc("name,surname,email,time","register","WHERE user_id='$chkcookie'");
            $infoUser1 = $getinfoUser[0]['name'];
            $infoUser2 = $getinfoUser[0]['surname'];
            $infoUser3 = $getinfoUser[0]['email'];
            $infoUser4 = $getinfoUser[0]['time'];
            $all = [$infoUser1,$infoUser2,$infoUser3,$infoUser4];
            $code = "00"; $info = $all;

        }

        if ($key == "10"){
            $cookie = $post['author_id'];
                $title = $post['title'];
                $san = $validate->sanitize($title);    
                $cat = $post['category'];
                $timeofUpload = date("d-m-y");
                $blog_id = $validate->randomize();    
                    $chkcookie = $jwt::openToken($cookie);
                    $getAuthorName = $class->select_fetch_assoc("name,surname","register","WHERE user_id='$chkcookie'");
                    $authorName = $getAuthorName[0]['name']; 
                    $authorSur = $getAuthorName[0]['surname'];
                    $nameandSur = $authorName.' '.$authorSur;   
                    $profile = $class->select_fetch_assoc("profile_pic","personal_info"," WHERE account_id='$chkcookie'");
                    $pro_pix = $profile[0]['profile_pic'];
                    $insertPost = $class->insert("user_articles","blog_id,user_id,title,category,author,profile_picture,uploadTime","'$blog_id','$chkcookie','$san','$cat','$nameandSur','$pro_pix','$timeofUpload'");
                    if($insertPost){
                        
                    $code = "00"; $info = $blog_id;
                    }
        }
        
       if ($key == "12"){
           $cookie = $post['get_post'];
           $chkcookie = $jwt::openToken($cookie);
        $sel = $class->select_fetch_assoc("*","user_articles","WHERE user_id='$chkcookie'");
        $count = count($sel);
        if($count){ $code = "00"; $info = $count;}else{$code = "01"; $info = '0';}
        $getDraft = $class->select_fetch_assoc("*","user_articles","WHERE user_id='$chkcookie' and  publish='draft'");
        $count1 = count($getDraft);
        if($getDraft){$code = "00"; $msg = $count1;}else{$code = "01"; $msg = '0';}
        $getPublished = $class->select_fetch_assoc("*","user_articles","WHERE user_id='$chkcookie' and  publish='publish'");
        $count2 = count($getPublished);
        if($getPublished){$code = "00"; $response = $count2;}else{$code = "01"; $response = '0';}
       }
       if($key == "13"){
        $local =  $post['category'];
        if($local){
            $sel = $class->select_fetch_assoc("*","user_articles","WHERE category='$local'");
            $code = "00";$info = $sel;
                }else if($local == null){
                    $code = "01"; $info = 'No Article in this Category!';
                }
       }

       if($key == "delblog"){
           $delId = $post['delId'];
           $delcookie = $jwt::openToken($delId);
           $delete = $class->delete ("user_articles","blog_id='$delcookie'");
           if($delete){
               $code = "00"; $info = 'Deleted Successfully';
           }else{
               $code = "01"; $info = 'Something went wrong';
           }
       }
    }

    
              

echo json_encode(["code"=>$code, 'info'=>$info, 'msg'=>$msg,'response'=>$response] );




?>
