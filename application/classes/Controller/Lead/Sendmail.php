<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Controller_Lead_Sendmail extends Controller {

    public $auto_render;

    public function before()
    {
        parent::before();

        $this->auto_render = false;
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // EMAIL 
    // ------------------------------------------------------------------------------------------------------------------------
    public static function send_mail( $title, $sendto, $html )
    {	
        $fields["Subject"]    = $title;
        $fields["Body"]       = $html;
        $fields["addAddress"] = $sendto;

        $mail = Request::factory( '/lead/sendmail/' )
            ->method(Request::POST)
            ->post($fields)
            ->execute()
            ->body();

        //return $mail;
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // EMAIL 
    // ------------------------------------------------------------------------------------------------------------------------
    public function action_index()
    {
        $post = $this->request->post();

        if ( ! $post )
        {
            throw new KO7_Exception('Sem post, favor verificar o script e enviar post');
        }

        $addAddress = $post['addAddress'];
        $Subject    = $post['Subject'];
        $Body       = $post['Body'];

        $mail = new PHPMailer(true); // Passing `true` enables exceptions

        try {
            //Server settings
            $mail->SMTPDebug = 0;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';                       // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'rogeriobbvn@vallery.com.br';       // SMTP username
            $mail->Password = 'Duclac0$';                         // SMTP password
            $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('crm@vallery.com.br', 'Vallery CRM');

            foreach ( $addAddress as $val )
            {
                $mail->AddAddress($val);
            }

            //$mail->addAddress('rogeriobbvn@rolemak.com.br', 'Rogerio');  // Add a recipient
            //$mail->addAddress($addAddress);                    // Name is optional
            //$mail->addReplyTo('info@example.com', 'Information');
            //$mail->addCC('rogeriobbvn@vallery.com.br');
            //$mail->addBCC('rogeriobbvn@vallery.com.br');

            //Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $Subject;
            $mail->Body    = $Body;
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            //echo 'Message has been sent';
        } catch (PHPMailerException $e) {
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }
    }

}