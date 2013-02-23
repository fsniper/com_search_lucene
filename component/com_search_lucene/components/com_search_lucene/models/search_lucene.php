<?php
/**
 * Joomla! 1.5 component search_lucene
 *
 * @version $Id: search_lucene.php 2009-08-12 07:07:43 svn $
 * @author Onur YALAZI
 * @package Joomla
 * @subpackage search_lucene
 * @license GNU/GPL
 *
 * This is a Joomla! 1.5 Search component, using Zend Lucene.
 *
 * This component file was created using the Joomla Component Creator by Not Web Design
 * http://www.notwebdesign.com/joomla_component_creator/
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * search_lucene Component search_lucene Model
 *
 * @author      notwebdesign
 * @package		Joomla
 * @subpackage	search_lucene
 * @since 1.5
 */
class Search_luceneModelSearch_lucene extends JModel {
	var $_data = null;
	var $_total = null;
	var $_areas = null;
	var $_pagination = null;

  /**
	* Constructor
	*/
	function __construct() {
		parent::__construct();
		global $mainframe;

		//Get configuration
		$config = JFactory::getConfig();

		// Get the pagination request variables
		$this->setState('limit', 
      $mainframe->getUserStateFromRequest(
        'com_search_lucene.limit', 
        'limit', 
        $config->getValue('config.list_limit'), 
        'int'
      )
    );
		$this->setState('limitstart', JRequest::getVar('limitstart', 0, '', 'int'));

		// Set the search parameters
		$keyword		= urldecode(JRequest::getString('searchword'));
		$match			= JRequest::getWord('searchphrase', 'all');
		$ordering		= JRequest::getWord('ordering', 'newest');
		$this->setSearch($keyword, $match, $ordering);

		//Set the search areas
		$areas = JRequest::getVar('areas');
		$this->setAreas($areas);
  }
	
  function setSearch($keyword, $match = 'all', $ordering = 'newest') {
		if(isset($keyword)) {
			$this->setState('keyword', $keyword);
		}

		if(isset($match)) {
			$this->setState('match', $match);
		}

		if(isset($ordering)) {
			$this->setState('ordering', $ordering);
		}
	}

	function setAreas($active = array(), $search = array()) {
		$this->_areas['active'] = $active;
		$this->_areas['search'] = $search;
	}
	
  function getData() {
		// Lets load the content if it doesn't already exist
		if (empty($this->_data)) {
			$areas = $this->getAreas();

			JPluginHelper::importPlugin( 'SearchLucene');
			$dispatcher =& JDispatcher::getInstance();
			$results = $dispatcher->trigger( 
        'onSearchLucene', array(
                            $this->getState('keyword'),
                            $this->getState('match'),
                            $this->getState('ordering'),
                            $areas['active']
                          ) 
      );

			$rows = array();
			foreach($results AS $result) {
				$rows = array_merge( (array) $rows, (array) $result);
			}
      function scoreSort($a, $b) {
        if ($a->score == $b->score) return 0;
        return ($a->score > $b->score) ? -1 : 1;
      }
      usort($rows, "scoreSort");

			$this->_total	= count($rows);
			if($this->getState('limit') > 0) {
				$this->_data = array_splice($rows, $this->getState('limitstart'), $this->getState('limit'));
			} else {
				$this->_data = $rows;
			}
		}

		return $this->_data;
	}

	function getTotal() {
		return $this->_total;
	}
	
  function getPagination() {
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( 
        $this->getTotal(), 
        $this->getState('limitstart'), 
        $this->getState('limit') 
      );
		}

		return $this->_pagination;
	}

	function getAreas() {
		global $mainframe;

		// Load the Category data
		if (empty($this->_areas['search'])) {
			$areas = array();

			JPluginHelper::importPlugin( 'SearchLucene');
			$dispatcher =& JDispatcher::getInstance();
			$searchareas = $dispatcher->trigger( 'onSearchLuceneAreas' );

			foreach ($searchareas as $area) {
				$areas = array_merge( $areas, $area );
			}

			$this->_areas['search'] = $areas;
		}

		return $this->_areas;
	}
}
?>
