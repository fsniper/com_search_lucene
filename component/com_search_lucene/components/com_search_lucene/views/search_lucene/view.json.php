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
    JFactory::getDocument()->setMimeEncoding("application/json");

    $data = $this->get('Data');
    $output = array();
    $c = 0;
    foreach($data as $result) {
      $o = new stdClass();
      $o->score = $result->score;
      $o->link = $result->link;
      $o->title = $result->title;
      $o->summary = $result->summary;
      $output[] = $o;
      if ($c++ > 5) break;
    }

    echo json_encode($output);
    return;
  }
}
?>
