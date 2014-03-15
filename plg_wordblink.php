<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.html.parameter' );

class plgSystemPlg_wordblink extends JPlugin
{
    var $plugincode;
    var $regex;
    var $countmatch;
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.0
	 */
	public function __construct( &$subject, $config )
	{
	    parent::__construct( $subject, $config );
		$this->plugincode = $this->params->get( 'plugincode', 'wordblink' );
		$this->regex="/(<p\b[^>]*>\s*)?\{".$this->plugincode.".*?(([a-z0-9A-Z]+(\[[0-9]+\])?='[^']+'.*?\|?.*?)*)\}(\s*<\/p>)?/msi";
		$this->countmatch = 2;
	}

	/**
	 * onPrepareContent is rename in Joomla 1.6 to onContentPrepare
	 */
	public function onContentPrepare($context, &$article, &$params, $limitstart=0)
	{
	    return;
	    // Le contenu de cette méthod ne me semble rien effectuer...
		$this->event = 'onContentPrepare';

		$app = JFactory::getApplication();
		if($app->isAdmin()) {
			return;
		}

		// get document types
		$this->_getdoc();

		// Check if fields exists. If article and text does not exists then stop
		if (isset($article)&&isset($article->text))
			$text = &$article->text;
		else
			return true;

		if (isset($article)&&isset($article->introtext))
			$introtext = &$article->introtext;
		else
			$introtext = "";

		// check whether plugin has been unpublished
		// PDF or feed can't show maps so remove it
		if ($this->doctype=='pdf' || $this->doctype=='feed') {
			//$text = preg_replace( $this->regex, '', $text );
			//$introtext = preg_replace( $this->regex, '', $introtext );
			unset($app, $text, $introtext);
			return true;
		}

		// perform the replacement in a normal way, but this has the disadvantage that other plugins
		// can't add information to the mosmap, other later added content is not checked and modules can't be checked
		// $this->_replace( $text );
		// $this->_replace( $introtext );

		// Clean up variables
		unset($app, $text, $introtext);
	}

	/**
	 * Do something onAfterDispatch
	 */
	public function onAfterDispatch()
	{
		$this->event = 'onAfterDispatch';

		$app = JFactory::getApplication();
		if($app->isAdmin()) {
			return;
		}

		// get document types
		$this->_getdoc();

		// FEED
		if ($this->doctype=='feed' && isset($this->document->items)) {
			foreach($this->document->items as $item) {
				$text = &$item->description;
				// Il faudrait remplacer par les mots sans javascript
				$text = preg_replace( $this->regex, '', $text );
			}
			// Clean up variables
			unset($app, $item, $text);
			return true;
		}

		// PDF can't show words blink so replace by the static words
		if ($this->doctype=='pdf') {
			$text = $this->document->getBuffer("component");
			$text = preg_replace( $this->regex, '\1 \2 \3', $text );
			$this->document->setBuffer($text, "component");
			// Clean up variables
			unset($app, $item, $text);
			return true;
		}

		// In other components or leftovers
		$text = $this->document->getBuffer("component");
		if (strlen($text) > 0) {

			// Replace the content
			$this->_replace($text);
			$this->document->setBuffer($text, "component");
		}

		// Clean up variables
		unset($app, $item, $text);
	}

	/**
	 * Do something onAfterRender
	 */
	public function onAfterRender()
	{
		$this->event = 'onAfterRender';

		$app = JFactory::getApplication();
		if($app->isAdmin()) {
			return;
		}

		// get document types
		$this->_getdoc();

		// Get the rendered body text
		$text = JResponse::getBody();

		// PDF or feed can't show maps so replace by the static words
		if ($this->doctype=='pdf'||$this->doctype=='feed') {
			$text = preg_replace( $this->regex, '\1 \2 \3', $text );
			// Set the body text with the replaced result
			JResponse::setBody($text);
			// Clean up variables
			unset($app, $text);
			return true;
		}

		// perform the replacement
		$this->_replace( $text );

		// Set the body text with the replaced result
        JResponse::setBody($text);

		// Clean up variables
		unset($app, $text);
	}

	function _getdoc() {
		if ($this->document==NULL) {
			$this->document = JFactory::getDocument();
			$this->doctype = $this->document->getType();
		}
	}

	function _replace(&$text) {
		$matches = array();
		$text=preg_replace("/&#0{0,2}39;/", '\'', $text);
		preg_match_all($this->regex,$text, $matches, PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
//		print_r($matches);
		// Remove plugincode that are in head of the page
		$matches = $this->_checkhead($text, $matches);

		$cnt = count($matches[0]);
//		print_r($matches);
		if ($cnt>0) {
			if ($this->helper==null) {
				$filename = JPATH_SITE."/plugins/system/plg_wordblink/plg_wordblink_helper.php";

				include_once($filename);
				$this->helper = new plgSystemPlg_wordblink_helper($this->plugincode, $this->regex, $this->document);
			}
			// Process the found {mosmap} codes
			for($counter = 0; $counter < $cnt; $counter++) {
				// Very strange the first match is the plugin code??
				$this->helper->process($matches[0][$counter][0],$matches[0][$counter][1], $matches[$this->countmatch][$counter][0], $text, $counter, $this->event);
			}
		}

		// Clean up variables
		unset($matches, $cnt, $counter, $content, $filename);
	}

	function _checkhead($text, $plgmatches) {
		$result = array(array(), array(), array(), array());
		$cnt = count($plgmatches[0]);
		// Get head location
		$end = stripos($text, '</head>');
		// check if match plugin is the head
		for($counter = 0; $counter < $cnt; $counter++) {
			if (!($plgmatches[0][$counter][1] > 0 &&$plgmatches[0][$counter][1]< $end)) {
					$result[0][] = $plgmatches[0][$counter];
					$result[1][] = $plgmatches[1][$counter];
					$result[2][] = $plgmatches[2][$counter];
					$result[3][] = $plgmatches[3][$counter];
			}
		}

		return $result;
	}
}
