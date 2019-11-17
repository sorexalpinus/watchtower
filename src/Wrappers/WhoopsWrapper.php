<?php
namespace WatchTower\Wrappers;

use WatchTower\Events\EventInterface;
use WatchTower\Events\ExceptionEvent;
use WatchTower\Exceptions\WatchTowerAwareException;
use Whoops\Handler\Handler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * Class WhoopsWrapper
 * @package WatchTower
 */
class WhoopsWrapper
{

    /**
     * @param Handler $handler
     * @param EventInterface $event
     * @param string $version error-page or minibox
     * @return array|mixed
     */
    public function handle(Handler $handler,EventInterface $event,$version = 'error-page') {
        $whoops = new Run();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->appendHandler($handler);
        $html = $whoops->handleException($event->getException());
        $html = $this->addExtraInfo($html,$event);
        if($version == 'minibox' and $handler instanceof PrettyPageHandler) {
            $html = $this->addExpandFeatures($html);
        }
        return $html;
    }

    /**
     * @param string $html
     * @return string $html
     */
    protected function addExpandFeatures($html) {
        $style = '<style>
            .Whoops.container header {transition: 0s;max-height: 200px;}
            .Whoops.container.collapsed {height:150px;}
            .Whoops.container.collapsed header {padding:10px 10px;}
            .Whoops.container.collapsed .panel.left-panel {width:100%;height:95px;position:static;} 
            .Whoops.container.collapsed .panel.details-container {display:none;}
            .Whoops.container.collapsed .frames-description {display:none;}
            .Whoops.container.collapsed .frames-container {display:none;}
          </style>';
        $html = $style.$html;
        preg_match("/<button id=\"hide-error\"\s(.+?)>(.+?)<\/button>/is",$html,$matches,PREG_OFFSET_CAPTURE);



        $button = $matches[0][0];
        $pos = $matches[0][1];

        $expandButton = '<button action="expand" style="margin-left:5px;" id="expand-error" class="rightButton" title="Hide error message" onclick="toggleExpand(this)">EXPAND</button>';
        $html = substr_replace($html, $expandButton, $pos + strlen($button), 0);
        $html .= '<script>
                document.getElementsByClassName("Whoops")[0].classList.add("collapsed");
                function toggleExpand(button) {
                    if(button.getAttribute("action") == "expand") {
                         button.setAttribute("action","collapse");
                         button.innerHTML = "COLLAPSE";
                         document.getElementsByClassName("Whoops")[0].classList.remove("collapsed");
                    }
                    else {
                         button.setAttribute("action","expand");
                         button.innerHTML = "EXPAND";
                         document.getElementsByClassName("Whoops")[0].classList.add("collapsed");
                    }
                };
        </script>';

        return $html;
    }

    /**
     * @param string $html
     * @param EventInterface $event
     * @return string $html
     */
    protected function addExtraInfo($html, EventInterface $event) {
        if ($event instanceof ExceptionEvent and $event->getException() instanceof WatchTowerAwareException) {
            $extraInfo = $event->getException()->getExtraInfo();
            $fragment = '<div class="details"';
            $pos = strpos($html,$fragment);
            $extraDetails = '<h2 class="details-heading">'.$event->getException()->getTitle() . ': </h2>';
            foreach($extraInfo as $label => $text) {
                $extraDetails .= '<div class="data-table-container" id="data-tables"><label>'.ucfirst($label).'</label><div>'.ucfirst($text).'</div></div>';
            }
            $extraDetails = ' <div class="details">'.$extraDetails.'</div>';
            $html = substr_replace($html, $extraDetails, $pos, 0);
        }
        return $html;
    }
}