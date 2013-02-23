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

// Import Joomla! libraries
jimport( 'joomla.application.component.view');

class Search_luceneViewDefault extends JView {
  function display($tpl = null) {

    require_once JPATH_COMPONENT.DS.'models'.DS.'search_lucene.php';


    $model = new Search_luceneModelSearch_lucene();
    $this->results =  $model->getListOfPlugins(); 

    parent::display($tpl);

  }
}

?>
