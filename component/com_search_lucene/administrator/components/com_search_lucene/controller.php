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
 * This is a Joomla! 1.5 Search component,
using Zend Lucene.
 *
 * This component file was created using the Joomla Component Creator by Not Web Design
 * http://www.notwebdesign.com/joomla_component_creator/
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.controller' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'helper.php' );

/**
 * search_lucene Controller
 *
 * @package Joomla
 * @subpackage search_lucene
 */
class Search_luceneController extends JController {
  /**
   * Constructor
   * @access private
   * @subpackage search_lucene
   */
  function __construct() {
    //Get View
    if(JRequest::getCmd('view') == '') {
      JRequest::setVar('view', 'default');
    }
    $this->item_type = 'Default';
    parent::__construct();
  }

  function listPlugins() {
    parent::display();
  }

}
?>
