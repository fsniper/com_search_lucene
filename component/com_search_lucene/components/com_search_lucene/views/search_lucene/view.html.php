<?php
/**
 * Joomla! 1.5 component search_lucene
 *
 * @version $Id: view.html.php 2009-08-12 07:07:43 svn $
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

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the search_lucene component
 */
class Search_luceneViewSearch_lucene extends JView {

  function display($tpl = null) {
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'search.php' );
		global $mainframe;
		$params = &$mainframe->getParams();
		$uri      =& JFactory::getURI();
		
    $error	= '';
		$rows	= null;
		$total	= 0;

		// Get some data from the model
		$areas      = &$this->get('areas');
		$state 		= &$this->get('state');
		$searchword = $state->get('keyword');
		
		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();
		
    $document	= &JFactory::getDocument();
		$document->setTitle( $params->get( 'page_title' ) );
		
    $orders = array();
		
    $lists = array();
		$lists['ordering'] = JHTML::_('select.genericlist',   $orders, 'ordering', 'class="inputbox"', 'value', 'text', $state->get('ordering') );


		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object( $menu )) {
			$menu_params = new JParameter( $menu->params );
			if (!$menu_params->get( 'page_title')) {
				$params->set('page_title',	JText::_( 'Search' ));
			}
		} else {
			$params->set('page_title',	JText::_( 'Search' ));
		}
		
    // log the search
		SearchHelper::logSearch( $searchword);

		//limit searchword

		if(SearchHelper::limitSearchWord($searchword)) {
			$error = JText::_( 'SEARCH_MESSAGE' );
		}

		//sanatise searchword
		if(SearchHelper::santiseSearchWord($searchword, $state->get('match'))) {
			$error = JText::_( 'IGNOREKEYWORD' );
		}

		if (!$searchword && count( JRequest::get('post') ) ) {
			$error = JText::_( 'Enter a search keyword' );
		}

		// put the filtered results back into the model
		// for next release, the checks should be done in the model perhaps...
		$state->set('keyword', $searchword);

		if(!$error)
		{
			$results	= &$this->get('data' );
			$total		= &$this->get('total');
			$pagination	= &$this->get('pagination');
		}
		
		$this->result	= JText::sprintf( 'TOTALRESULTSFOUND', $total  );

		$this->assignRef('pagination',  $pagination);
		$this->assignRef('results',		$results);
		$this->assignRef('lists',		$lists);
		$this->assignRef('params',		$params);

		$this->assign('ordering',		$state->get('ordering'));
		$this->assign('searchword',		$searchword);
		$this->assign('searchareas',	$areas);

		$this->assign('total',			$total);
		$this->assign('error',			$error);
		$this->assign('action', 	  $uri->toString());
    
    parent::display($tpl);
  }
}
?>
