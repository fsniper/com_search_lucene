<?php
/**
 * Joomla! 1.5 component search_lucene
 *
 * @version $Id: controller.php 2009-08-12 07:07:43 svn $
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

jimport('joomla.application.component.controller');

/**
 * search_lucene Component Controller
 */
class Search_luceneController extends JController {
  function display() {
    // Make sure we have a default view
    if( !JRequest::getVar( 'view' )) {
      JRequest::setVar('view', 'search_lucene' );
    }
    parent::display();
  }
	
  function search() {
		$post['searchword'] = JRequest::getString('searchword', null, 'post');
		$post['ordering']	= JRequest::getWord('ordering', null, 'post');
		$post['searchphrase']	= JRequest::getWord('searchphrase', 'all', 'post');
		$post['limit']  = JRequest::getInt('limit', null, 'post');
		if($post['limit'] === null) unset($post['limit']);

		$areas = JRequest::getVar('areas', null, 'post', 'array');
		if ($areas) {
			foreach($areas as $area) {
				$post['areas'][] = JFilterInput::clean($area, 'cmd');
			}
		}

		// set Itemid id for links
		$menu = &JSite::getMenu();
		$items	= $menu->getItems('link', 'index.php?option=com_search_lucene&view=search');

		if(isset($items[0])) {
			$post['Itemid'] = $items[0]->id;
		}

		unset($post['task']);
		unset($post['submit']);

		$uri = JURI::getInstance();
		$uri->setQuery($post);
		$uri->setVar('option', 'com_search_lucene');


		$this->setRedirect(JRoute::_('index.php'.$uri->toString(array('query', 'fragment')), false));
	}
}
?>
