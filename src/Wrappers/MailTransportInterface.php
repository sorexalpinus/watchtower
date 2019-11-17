<?php


namespace WatchTower\Wrappers;

/**
 * Whatever mailer you are using, create a wrapper class that implements this interface and pass it to SendEmail handler
 * Interface MailTransportInterface
 * @package WatchTower
 */
interface MailTransportInterface
{

    /**
     * Set e-mail body; make sure that mailer supports html content
     * @param string $body
     * @return mixed
     */
    public function setBody($body);


    /**
     * Set e-mail subject
     * @param string $subject
     * @return mixed
     */
    public function setSubject($subject);

    /**
     * Send e-mail "from"
     * @param string $address
     * @param string|null $name
     * @return mixed
     */
    public function setSender($address,$name = null);

    /**
     * Send e-mail "to"
     * @param string $address
     * @param string|null $name
     * @return mixed
     */
    public function addRecipient($address,$name = null);

    /**
     * Make sure that mailer supports html body
     * @return bool $bool
     */
    public function isHtml($bool);

    /**
     * Sends the e-mail
     * @return bool $bool true on success, false on failure
     */
    public function send();

    /**
     * Error message that is generated in case of failure
     * @return string
     */
    public function getErrorMessage();

}