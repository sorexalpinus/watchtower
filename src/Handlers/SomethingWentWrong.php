<?php
namespace WatchTower\Handlers;

use WatchTower\Events\EventInterface;

/**
 * Class SomethingWentWrong
 * @package WatchTower\Handlers
 */
class SomethingWentWrong extends Handler
{
    /**
     * @param EventInterface $event
     * @return $this
     */
    public function handle(EventInterface $event)
    {
        $html = '<style>'.$this->getStyle().'</style>';
        $html .= '<div class="error-box"><h1>We\'re sorry, but something went wrong.</h1><p>We\'ve been notified about this issue and we\'ll take a look at it shortly.</p></div>';
        $this->output = $html;
        return $this;
    }

    /**
     * @return string $style
     */
    protected function getStyle() {
        $style = '    
               body {
                    color: #666;
                    text-align: center;
                    font-family: arial, sans-serif;
               }    
               div.error-box {
                    width: 25em;
                    padding: 0 4em;
                    margin: 4em auto 0 auto;
                    border: 1px solid #CCC;
                    border-right-color: #999;
                    border-bottom-color: #999;
                    display: block;
                }';
        return $style;
    }
}