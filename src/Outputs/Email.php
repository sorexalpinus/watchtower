<?php

namespace WatchTower\Outputs;

use WatchTower\Events\EventInterface;
use WatchTower\Exceptions\WatchTowerException;
use WatchTower\Wrappers\MailTransportInterface;

/**
 * Class Email
 *
 * @package WatchTower\Outputs
 */
class Email extends OutputTarget
{

    /**
     * @return string $name
     */
    public function getName() {
        return 'email';
    }


    /**
     * File constructor.
     *
     * @param array $config
     * @throws WatchTowerException
     */
    public function __construct($config = [])
    {
        $mandatory = ['email.transport','email.sender','email.subject','email.recipients'];
        $this->config = $this->validateConfig($config,$mandatory);
    }

    /**
     * @param EventInterface $event
     * @param $content
     * @param array $outputStack
     * @return $this|void|OutputTargetInterface
     */
    public function execute(EventInterface $event, $content, $outputStack = [])
    {
        $body = $this->buildEmailBody($outputStack);
        /** @var MailTransportInterface $transport */
        $transport = $this->config['email.transport'];
        $transport->setSender($this->config['email.sender']);
        $transport->setBody($body);
        $subject = is_callable($this->config['email.subject']) ? $this->config['email.subject']($event, $this) : $this->config['email.subject'];
        $transport->setSubject($subject);
        $transport->isHtml(true);
        if (is_array($this->config['email.recipients'])) {
            foreach ($this->config['email.recipients'] as $recipient) {
                if (is_array($recipient)) {
                    $transport->addRecipient($recipient[0], $recipient[1]);
                } else {
                    $transport->addRecipient($recipient);
                }
            }
        }
        $errorMsg = '';

        $success = $transport->send();
        if (!$success) {
            $errorMsg = $transport->getErrorMessage();
        }
        $this->output = [
            'success' => (int)$success,
            'errorMsg' => $errorMsg
        ];
        return $this;
    }

    /**
     * TODO: this needs to be simplified
     *
     * @param $outputStack
     * @return string $body
     */
    public function buildEmailBody($outputStack)
    {
        $body = 'An error/exception occured<br />';
        if (array_key_exists('plaintext', $outputStack['handler']) and strlen($outputStack['handler']['plaintext']) > 0) {
            $body .= '<br />'. $outputStack['handler']['plaintext'] . '<br />';
        }
        if(array_key_exists('targets',$outputStack) and array_key_exists('file',$outputStack['targets']) and array_key_exists('accessLink',$outputStack['targets']['file'])) {
            $body .= "<br /><br />Find more information on the following link: <br /><br />";
            $body .= "<a href='" . $outputStack['targets']['file']['accessLink'] . "'>Click here</a>";
        }
        return $body;
    }

}