<?php

namespace WatchTower\Handlers;

use WatchTower\Events\EventInterface;
use WatchTower\Wrappers\WhoopsWrapper;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;


/**
 * Class InAppInfoBoxHandler display error output directly into browser on the top of the screen - use in development only
 * @package WatchTower
 */
class WhoopsMinibox extends Handler
{
    static protected $counter = 0;

    /**
     * @param EventInterface $event
     * @return $this
     */
    public function handle(EventInterface $event)
    {
        self::$counter++;
        $ww = new WhoopsWrapper();
        $whoopsHandler = new PrettyPageHandler();
        $whoopsHandler->handleUnconditionally(true);
        $box = $ww->handle($whoopsHandler,$event);
        $html = $this->wrapIntoMinibox($box,$event);
        $this->output = $html;
        $plainTextHandler = new PlainTextHandler();
        $this->outputVars['plaintext'] = $ww->handle($plainTextHandler,$event);
        return $this;
    }

    /**
     * @param string $html
     * @param EventInterface $event
     * @return string $html
     */
    protected function wrapIntoMinibox($html,EventInterface $event) {
        $style = '';
        $jquery = '';
        $script = '';
        if(self::$counter == 1) {
            $style = '<style>
            .wt-minibox {
                position: relative;
                color: white;
                box-sizing: border-box;
                background-color: #2a2a2a;
                padding: 5px;
                max-height: 180px;
                overflow: hidden;
                transition: 0.5s;
                font-size:10px;
                border-bottom: 1px solid gray;
            }
            .wt-minibox .expand-error {
                position:absolute;
                bottom:5px;
                right:5px;
                cursor: pointer;
                border: 0;
                opacity: .8;
                background: none;
                color: rgba(255, 255, 255, 0.1);
                box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.1);
                border-radius: 3px;
                outline: none !important;
            }
            .wt-minibox .expand-error:hover {
                box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.3);
                color: rgba(255, 255, 255, 0.3);
            }
            .wt-minibox .exc-message {
                font-size:15px;
            }
            .wt-minibox .frame-file {
                font-size:12px;
                padding-top: 3px;
            }
            .wt-mainbox.collapsed {
                display: none;
            }
            .wt-mainbox.expanded {
                display: block;
            }
          </style>';
            $jquery = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>';
            $script = '<script>
                   function toggleExpand() {
                    $("button.expand-error").on("click",function() {
                        
                        if($(this).attr("action") === "expand") {
                              var thisId = $(this).closest(".wt-minibox-wrapper").attr("id");
                             $(".wt-minibox-wrapper").each(function(i,e) {
                                   if($(e).attr("id") !== thisId) {
                                       $(e).remove();
                                   }
                             });
                             $(this).attr("action","collapse");
                             $(this).html("COLLAPSE");
                             $(this).closest(".wt-minibox-wrapper").find(".wt-mainbox").removeClass("collapsed");
                             $("div#"+thisId+" .frame.active").trigger("click"); 
                        }
                        else {
                             $(this).attr("action","expand");
                             $(this).html("EXPAND");
                             $(this).closest(".wt-minibox-wrapper").find(".wt-mainbox").removeClass("expanded").addClass("collapsed"); 
                        }
                    });
                }
                $("document").ready(function(){
                      toggleExpand();
                 });
        </script>';
        }
        $id = 'e_'.str_replace('.','_',$event->getId());
        $expButton = '<button action="expand" style="margin-left:5px;" class="rightButon expand-error" title="Hide error message">EXPAND</button>';
        $minibox = '<div class="exc-message">
                <span>'.$event->getName().': '.$event->getMessage().'</span>
                <div class="frame-file"><strong><span class="delimiter">'.$event->getFile().':'.$event->getLine().'</span></strong></div>
                </div>'.$expButton;
        $html = '<div class="wt-minibox-wrapper" id="'.$id.'"><div class="wt-minibox">'.$minibox.'</div><div class="wt-mainbox collapsed">'.$html.'</div></div>';
        $html = '<div style="position:absolute;float:left;width:100%;">'.$html.'</div>';
        $r = $style.$html.$jquery.$script;
        return $r;
    }
}