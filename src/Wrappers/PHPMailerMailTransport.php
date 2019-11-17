<?php

namespace WatchTower\Wrappers;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class PHPMailerMailTransport
 */
class PHPMailerMailTransport implements MailTransportInterface
{
    /** @var PHPMailer $mailer */
    protected $mailer;

    /**
     * PHPMailerMailTransport constructor.
     * @param PHPMailer $mailer
     */
    public function __construct(PHPMailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param string $body
     * @return void
     */
    public function setBody($body)
    {
        $this->mailer->Body = $body;
    }

    /**
     * @param string $subject
     * @return void
     */
    public function setSubject($subject)
    {
        $this->mailer->Subject = $subject;
    }

    /**
     * @param string $address
     * @param null $name
     * @return void
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function setSender($address, $name = null)
    {
        $this->mailer->setFrom($address,$name);
    }

    /**
     * @param string $address
     * @param null $name
     * @return void
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function addRecipient($address, $name = null)
    {
        $this->mailer->addAddress($address,$name);
    }

    /**
     * @param bool $bool
     * @return void
     */
    public function isHtml($bool)
    {
        $this->mailer->isHTML($bool);
    }

    /**
     * @return bool
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function send() {
        return $this->mailer->send();
    }

    /**
     * @return string
     */
    public function getErrorMessage() {
        return $this->mailer->ErrorInfo;
    }

}