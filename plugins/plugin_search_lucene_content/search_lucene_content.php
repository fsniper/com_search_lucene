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

require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

class plgSearchLucenesearch_lucene_content extends JPlugin
{
  var $name = "Content Searching";
  var $pluginName = "search_lucene_content";
  var $version = "0.1";
  var $author = "Onur YALAZI";
  var $areas = array('content' => 'Articles');
  

  var $query = '
      SELECT 
        a.id,
        a.title AS title,
        a.created AS created, 
        a.id AS sectionid,
        CONCAT(a.introtext, a.`fulltext`) AS content,
        substr(concat(a.introtext, a.`fulltext`), 1, 255) AS summary,
        CONCAT_WS( "/", u.title, b.title ) AS section,
        CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,
        CASE WHEN CHAR_LENGTH(b.alias) THEN CONCAT_WS(":", b.id, b.alias) ELSE b.id END as catslug
      FROM 
        #__content AS a
      LEFT OUTER JOIN 
        #__categories AS b ON b.id=a.catid
      LEFT OUTER JOIN 
        #__sections AS u ON u.id = a.sectionid
      WHERE 
        u.published = 1 AND
        b.published = 1 AND
        %s
      GROUP BY a.id 
      ORDER by a.id ASC
      LIMIT %s
    ';

	function plgSearchLucenesearch_lucene_content(& $subject)
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
      fields named 'id', 'link', 'title', 'content' and 'summary' are mandatory.
      summary should be stored and indexed - meaning type text and max 256 chars
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
      ),
      array(
        'name' => 'catslug',
        'type' => 'text'
      )
    );
    return $fields;
  }
  function indexerGetData() {
    /* 
      Returns next Document to be indexed for indexer.
      Indexer will call this method unless it gets a boolean false or a null.

      Every time this method is called it must return a document to be indexed
      Until no more documents are to be indexed. Then you should return a boolean false;
    */
    if (!isset($this->counter)) { $this->counter = 0; };

    $sql = sprintf($this->query, '( a.id > ' . $this->counter . ')' , 1000);
    $this->db->setQuery($sql);
    $articles = $this->db->loadObjectList();
    
    if ($articles==null || (is_array($articles) && count($articles) == 0)) return false;

    $resultset = array('resultset' => array());
    $lastid = 0;
    foreach ($articles as $article) {
      $lastid = $article->id;
      $article->link = ContentHelperRoute::getArticleRoute($article->slug, $article->catslug, $article->sectionid);
      $resultset['resultset'][] = $article;
    }
    if ($resultset['resultset'] != null && count($resultset['resultset']) > 0) {
      $this->counter = $lastid;
      return  $resultset;
    } else {
      unset($this->counter);
      return false;
    }
  }
}
