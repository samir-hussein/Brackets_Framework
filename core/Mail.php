<?php

namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mail
{
    private static $mail;
    private static $message;

    public function __construct($config)
    {
        self::$mail = new PHPMailer(true);
        self::$message = $this;

        try {
            //Server settings
            // self::$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            self::$mail->isSMTP();                                            //Send using SMTP
            self::$mail->Host       = $config['MAIL_HOST'];                   //Set the SMTP server to send through
            self::$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            self::$mail->Username   = $config['MAIL_USERNAME'];               //SMTP username
            self::$mail->Password   = $config['MAIL_PASSWORD'];               //SMTP password
            self::$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            self::$mail->Port       = $config['MAIL_PORT'];                   //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
            self::$mail->isHTML(true);                                        //Set email format to HTML
        } catch (Exception $e) {
            $error = self::$mail->ErrorInfo;
            echo "Message could not be sent. Mailer Error: {$error}";
        }
    }

    public static function send(callable $callback, string $view = null, array $data = null)
    {
        $message = self::$message;

        if (!is_null($view)) {
            ob_start();
            if (!is_null($data)) {
                extract($data);
            }
            include __DIR__ . "/../views/$view.php";
            self::$mail->Body = ob_get_clean();
        }

        call_user_func($callback, $message);
        try {
            self::$mail->send();
            return true;
        } catch (Exception $e) {
            $error = self::$mail->ErrorInfo;
            echo "Message could not be sent. Mailer Error: {$error}";
        }
    }

    public function from(string $email, $name = null)
    {
        self::$mail->setFrom($email, $name);
    }

    public function to(string $email, $name = null)
    {
        self::$mail->addAddress($email, $name);
    }

    public function subject(string $subject)
    {
        self::$mail->Subject = $subject;
    }

    public function body(string $message)
    {
        self::$mail->Body = $message;
    }

    public function replyTo(string $email)
    {
        self::$mail->addReplyTo($email, 'Information');
    }

    public function CC(string $email)
    {
        self::$mail->addCC($email);
    }

    public function BCC(string $email)
    {
        self::$mail->addBCC($email);
    }

    public function attachment(string $file_path, $file_name = null)
    {
        self::$mail->addAttachment($file_path, $file_name);
    }
}
