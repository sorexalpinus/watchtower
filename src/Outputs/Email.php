<?php

namespace WatchTower\Outputs;

use WatchTower\Events\EventInterface;
use WatchTower\Wrappers\MailTransportInterface;

/**
 * Class Email
 * @package WatchTower\Outputs
 */
class Email extends OutputTarget
{
    /**
     * @param EventInterface $event
     * @param $content
     * @param array $globalVars
     * @return $this|void|OutputTargetInterface
     */
    public function execute(EventInterface $event,$content,$globalVars = [])
    {
        $body = $this->buildEmailBody($content,$globalVars);
        /** @var MailTransportInterface $transport */
        $transport = $this->config['transport'];
        $transport->setSender($this->config['sender']);
        $transport->setBody($body);
        $subject = is_callable($this->config['subject']) ? $this->config['subject']($event,$this) : $this->config['subject'];
        $transport->setSubject($subject);
        $transport->isHtml(true);
        if(is_array($this->config['recipients'])) {
            foreach($this->config['recipients'] as $recipient) {
                if(is_array($recipient)) {
                    $transport->addRecipient($recipient[0],$recipient[1]);
                }
                else {
                    $transport->addRecipient($recipient);
                }
            }
        }
        $errorMsg = '';
        $success = $transport->send();
        if(!$success) {
            $errorMsg = $transport->getErrorMessage();
        }
        $this->outputVars = [
            'success' => $success,
            'errorMsg' => $errorMsg
        ];
        return $this;
    }

    /**
     * @param string $content
     * @param array $globalVars
     * @return string $body
     */
    public function buildEmailBody($content,$globalVars) {
        $body = 'An error/exception occured<br />';
        if(array_key_exists('plaintext',$globalVars) and strlen($globalVars['plaintext']) > 0) {
            $body .= $globalVars['plaintext'] . '<br />';
        }
        if(array_key_exists('fileAccessLink',$globalVars) and strlen($globalVars['fileAccessLink']) > 0) {
            $body .= "<br /><br />Find more information on the following link: <br /><br />";
            $body .= "<a href='" . $globalVars['fileAccessLink'] . "'>Click here</a>";
        }
        else {
            $body .= '<br /><br />';
            $body .= $content;
        }
        return $body;
    }

    /**
     * @param string|null $item
     * @return array $defaultConfig
     */
    public function getDefaultConfig($item = null)
    {
        $subjectFunc = function (EventInterface $event, OutputTargetInterface $outputTarget) {
            return 'System '.$event->getType().' - ' . date("Y-m-d H:i:s");
        };
        if (!isset($this->defaultConfig)) {
            $this->defaultConfig = [
                'transport' => ['mandatory'=>true, 'value'=>null],
                'sender'    => ['mandatory'=>true, 'value'=>null],
                'recipients'=> ['mandatory'=>true, 'value'=>null],
                'subject'   => ['mandatory'=>true, 'value'=>$subjectFunc]
            ];
        }
        return $this->defaultConfig;
    }
}