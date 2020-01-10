<?php
define('MAILGUN_KEY','957af107ee93cb7914b752190cc8dbd1-fd0269a6-67001004');
define('MAILGUN_PUBKEY','pubkey-e63669280dc2bea472b6108c794bf47a');
define('MAILGUN_DOMAIN','sandboxea3c5b5ed70348fd8675bf56435c2e64.mailgun.org');
require('vendor/autoload.php');
use Mailgun\Mailgun;

class mail{
    public function Mailing($from,$to,$subject,$message){
      $msg=  Mailgun::create(MAILGUN_KEY);
        $me = $msg->messages()->send(MAILGUN_DOMAIN,[
            'from' => $from,
            'to' => $to,
            'subject' => $subject,
            'text' => $message
        ]);

        
    }
}






?>