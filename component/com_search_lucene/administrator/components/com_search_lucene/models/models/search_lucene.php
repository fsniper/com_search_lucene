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

// Import Joomla! libraries
jimport('joomla.application.component.model');

class Search_luceneModelSearch_lucene extends JModel {

  function __construct() {
    JPluginHelper::importPlugin( 'SearchLucene' );
    $this->dispatcher =& JDispatcher::getInstance();
    parent::__construct();
  }

  function getListOfPlugins() {
    $results = $this->dispatcher->trigger( 'introduceSelf', array() );
    return $results;
  }
}
?>
