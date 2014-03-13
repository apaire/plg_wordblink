<?php
/*------------------------------------------------------------------------

--------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die( 'Restricted access' );

if (!defined('_CMN_JAVASCRIPT')) define('_CMN_JAVASCRIPT', "<b>JavaScript must be enabled in order for you to use Google Maps.</b> <br/>However, it seems JavaScript is either disabled or not supported by your browser. <br/>To view Google Maps, enable JavaScript by changing your browser options, and then try again.");

class plgSystemPlg_wordblink_helper
{
    var $params;
    var $regex;
    var $document;

    public function __construct($params, $regex, $document)
    {
		// The params of the plugin
		$this->params = $params;
		$this->regex = $regex;
		$this->document = $document;
    }

    function process($match, $match_offset, $params, &$text, $counter, $event) {
        $i =  $match;
    }
}