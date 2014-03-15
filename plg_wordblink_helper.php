<?php
/*------------------------------------------------------------------------

--------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die( 'Restricted access' );

if (!defined('_CMN_JAVASCRIPT')) define('_CMN_JAVASCRIPT', "<b>JavaScript must be enabled in order for you to use Google Maps.</b> <br/>However, it seems JavaScript is either disabled or not supported by your browser. <br/>To view Google Maps, enable JavaScript by changing your browser options, and then try again.");

class plgSystemPlg_wordblink_helper
{
    var $plugincode;
    var $regex;
    var $document;

    public function __construct($plugincode, $regex, $document)
    {
		// The params of the plugin
		$this->plugincode = $plugincode;
		$this->regex = $regex;
		$this->document = $document;
    }

    function process($match, $match_offset, $params, &$text, $counter, $event) {
        // $text may contain string like: "{wordblink nbMots=3 mot1="TOTO" mot2="TUTU" mot3="TATA" onDuration=1000 transitionDuration=1000}"
        // Search for such a string in $text and retrieve the parameter list.
        $reAll = "#\{" . $this->plugincode . "(\s+.*)\s*\}#";
        preg_match_all($reAll, $text, $parsedParams);
        if (!isset($parsedParams) || !isset($parsedParams[1]) || count($parsedParams[1]) == 0) {
            // No plugin code found
            return;
        }
        $paramList = $parsedParams[1][0];

        // Parse the nbMot parameter
        $nbMots = 0;
        $re = "#nbMots=([0-9]+)#";
        preg_match_all($re, $paramList, $match);
        if (isset($match) && isset($match[1]) && count($match[1]) != 0) {
            $nbMots = $match[1][0];
        }
        unset($match);

        // Parse the MotX parameters
        $mots = array();
        for($i = 1; $i < ($nbMots + 1); $i++) {
            $re = "#mot" . $i . "=((?:['\"][^'\"]+['\"])|(?:[^'\"]\S*))#";
            preg_match_all($re, $paramList, $match);
            if (isset($match) && isset($match[1]) && isset($match[1][0])) {
                $mots[$i - 1] = trim($match[1][0],"'\"");
            }
        }
        unset($match);

        // Parse the onDuration parameter
        $onDuration = 1000;
        $re = "#onDuration=([0-9]+)#";
        preg_match_all($re, $paramList, $match);
        if (isset($match) && isset($match[1]) && count($match[1]) != 0) {
            $onDuration = $match[1][0];
        }
        unset($match);

        // Parse the transitionDuration parameter
        $transitionDuration = 1000;
        $re = "#transitionDuration=([0-9]+)#";
        preg_match_all($re, $paramList, $match);
        if (isset($match) && isset($match[1]) && count($match[1]) != 0) {
            $transitionDuration = $match[1][0];
        }
        unset($match);

        // Handle error cases
        if ($nbMots == 0 || $nbMots != count($mots)) {
            // There was an error in parsing the plugin call string or there is an error in the plugin usage
            return;
        }

        $html = $this->_buildHtml($nbMots, $mots, $onDuration, $transitionDuration);

        // Replace the plg command by the html code
        $text = preg_replace($reAll, $html, $text);

        $this->document->addScript(JURI::root() . "plugins/system/plg_wordblink/js/wordblink.js");
    }

    function _buildHtml($nbMots, $mots, $onDuration, $transitionDuration) {
        $html = "";
        for ($i = 0; $i < $nbMots; $i++) {
            $html .= "<span id='mot" . $i . "'>" . $mots[$i] . "</span> ";
        }
        $html .= "<script type='text/javascript'>wordblink(" . $nbMots . "," . $onDuration . "," . $transitionDuration . ");</script>";
        return $html;
    }
}