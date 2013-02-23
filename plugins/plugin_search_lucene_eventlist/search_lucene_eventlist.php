<?php
/**
 * Joomla! 1.5 component search_lucene
 *
 * @version $Id: search_lucene_test.php 2009-08-12 07:07:43 svn $
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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.event.plugin' );

if (preg_match('/administrator\/?$/', JPATH_BASE)) {
  $inpath = JPATH_BASE . DS . '..';
} else {
  $inpath = JPATH_BASE;
}
set_include_path(get_include_path() . DS . ':' .  $inpath . '/search_lucene/');
include_once( 'Zend/Search/Lucene.php' );

require_once(JPATH_SITE.DS.'components'.DS.'com_eventlist'.DS.'helpers'.DS.'route.php');

class plgSearchLucenesearch_lucene_eventlist extends JPlugin
{
  var $name = "Eventlist Searching";
  var $pluginName = "search_lucene_eventlist";
  var $version = "0.1";
  var $author = "Onur YALAZI";
  var $areas = array('eventlist' => 'Events');
  
  var $query = 'SELECT 
                  a.id,
                  a.title AS title,
                  a.datdescription AS content,
                  substr(a.datdescription, 1, 255) AS summary,
                  a.dates AS created,
                  CASE WHEN 
                    CHAR_LENGTH(a.alias) 
                  THEN 
                    CONCAT_WS(\':\', a.id, a.alias) 
                  ELSE 
                  a.id END as slug, 
                  CONCAT_WS( " / ", c.catname, a.title ) AS section
                FROM 
                  #__eventlist_events AS a
                INNER JOIN 
                  #__eventlist_categories AS c 
                ON 
                  c.id = a.catsid
                WHERE a.published = 1 AND c.published = 1 AND ( %s )
                LIMIT %s';

	function plgSearchLucenesearch_lucene_eventlist(& $subject)
	{
		parent::__construct($subject);
    $this->db = JFactory::getDBO();
	}

	function introduceSelf( ) {
    return array(
      'name' => $this->name,
      'version' => $this->version,
      'author' => $this->author
    );
	}

  function onSearchLuceneAreas() {
    return $this->areas;
  }
  
  function onSearchLucene($text, $phrase='', $ordering='', $areas=null) {
    if (is_array( $areas ) && count($areas) > 0 ) {
      if (!array_intersect( $areas, array_keys( $this->areas ) )) {
        return array();
      }
    }
    
    $params = JComponentHelper::getParams('com_search_lucene');
    $indexpath = $params->get('indexpath');
    $pluginindex = $indexpath . DS . $this->pluginName;
    
    $index = new Zend_Search_Lucene($pluginindex); 
    if ($index) {
      $rows = $index->find($text);
    } else {
      $rows = array();
    }
    
    return $rows;
  }

  function indexerGetFields() {
    /*
      Returns Documents Fields to be searched for indexer.
      fields named 'id', 'link', 'title' and 'content' are mandatory.
      
    */
    $fields = array(
      array(
        'name' => 'id', // Name Of the field
        'type' => 'unIndexed', // Zend Search Lucene Field Type (like keyword, unIndexed, binary, text, unStored)
      ),
      array(
        'name' => 'link',
        'type' => 'keyword'
      ),
      array(
        'name' => 'title',
        'type' => 'text'
      ),
      array(
        'name' => 'summary',
        'type' => 'text'
      ),
      array(
        'name' => 'content',
        'type' => 'unStored'
      ),
      array(
        'name' => 'created',
        'type' => 'text'
      ),
      array(
        'name' => 'section',
        'type' => 'text'
      ),
      array(
        'name' => 'slug',
        'type' => 'text'
      )
    );
    return $fields;
  }
  function indexerGetData() {
    /* 
      Returns next Document to be indexed for indexer.
      Indexer will call this method unless it gets a boolean false.

      Every time this method is called it must return a document to be indexed
      Until no more documents are to be indexed. Then you should return a boolean false;
    */
    if (!isset($this->counter)) { $this->counter = 0; };

    $sql = sprintf($this->query, ' a.id > ' . $this->counter , 100);
    $this->db->setQuery($sql);
    
    $articles = $this->db->loadObjectList();
    $resultset = array('resultset' => array());
    foreach ($articles as $article) {
      $lastid = $article->id;
      $article->link = EventListHelperRoute::getRoute($article->slug);
      $resultset['resultset'][] = $article;
    }
	unset($articles);
    if ($resultset['resultset'] != null && count($resultset['resultset']) > 0) {
      $this->counter = $lastid;
      return  $resultset;
    } else {
      unset($this->counter);
      return false;
    }
  }
}
