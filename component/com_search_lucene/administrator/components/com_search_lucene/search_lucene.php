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
 * This is a Joomla! 1.5 Search component,
using Zend Lucene.
 *
 * This component file was created using the Joomla Component Creator by Not Web Design
 * http://www.notwebdesign.com/joomla_component_creator/
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Define constants for all pages
 */
define( 'COM_SEARCH_LUCENE_DIR', 'images'.DS.'search_lucene'.DS );
define( 'COM_SEARCH_LUCENE_BASE', JPATH_ROOT.DS.COM_SEARCH_LUCENE_DIR );
define( 'COM_SEARCH_LUCENE_BASEURL', JURI::root().str_replace( DS, '/', COM_SEARCH_LUCENE_DIR ));

// Require the base controller
require_once JPATH_COMPONENT.DS.'controller.php';

// Require the base controller
require_once JPATH_COMPONENT.DS.'helpers'.DS.'helper.php';

// Initialize the controller
$controller = new Search_luceneController( );

// Perform the Request task
$task = JRequest::getCmd('task');
if (empty($task)) $task = 'listPlugins';
$controller->execute( $task );
$controller->redirect();
?>
