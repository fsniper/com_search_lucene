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

class plgSearchLucenesearch_lucene_test extends JPlugin
{
  var $name = "Test Searching";
  var $pluginName = "search_lucene_test";
  var $version = "0.1";
  var $author = "Onur YALAZI";
  var $areas = array('testarea' => 'Test Plugin Area');

	function plgSearchLucenesearch_lucene_test(& $subject)
	{
		parent::__construct($subject);
    $this->documents = array(
      array(
        'id' => 0, 
        'link' => 'http://www.mobilada.com', 
        'title' => 'Company: Mobilada', 
        'content' => 'Mobilada is Web and Mobile Applications Development Company.',
        'summary' => 'Mobilada is Web and Mobile Applications Development Company.',
        'created' => '2009-08-15'
      ),
      array(
        'id' => 1, 
        'link' => 'http://www.mobilada.org', 
        'title' => 'Mobilada\'s Open Source Aplications', 
        'content' => 'Mobilada uses and supports of Open Source Software. ' .
                     'Mobilada also offers som of it\'s solutions for the open source community',
        'summary' => 'Mobilada uses and supports of Open Source Software. ' .
                     'Mobilada also offers som of it\'s solutions for the open source community',
        'created' => '2009-08-19'
      ),
      array(
        'id' => 2, 
        'link' => 'http://code.google.com/p/com-twitter', 
        'title' => 'Mobilada\'s Open Source Com_Twitter Application', 
        'content' => '
          com_twitter Project enables any Joomla! powered site to integrate with Twitter\'s OAuth.\' .
          Supplies:
               * com_twitter
               * mod_twitter
               * plugin_auth_twitter 
                    
          Uses:
               * jmathais\'s jmathai\'s twitter-async',
        'summary' => ' com_twitter Project enables any Joomla! powered site to integrate with Twitter\'s OAuth.\'',
        'created' => '2009-02-12'
      )
    );
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
    
    array_map( array('self', 'getContent'), $rows);
    return $rows;
  }
  
  static function getContent($row) {
    $content = "Not Loaded";
    $row->content = $content;
    return $row;
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
        'name' => 'content',
        'type' => 'unStored'
      ),
      array(
        'name' => 'summary',
        'type' => 'text'
      ),
      array(
        'name' => 'created',
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
    if (!isset($this->counter)) { $this->counter = 0; } else { $this->counter++; }
    
    if (isset($this->documents[$this->counter])) {
      return  $this->documents[$this->counter];
    } else {
      unset($this->counter);
      return false;
    }
  }
}
