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
if (file_exists(JPATH_BASE . DS . 'components/com_extend/helpers/route.php')) {
	include_once( JPATH_BASE . DS . 'components/com_extend/helpers/route.php');
} else if (file_exists(JPATH_BASE . DS . '../components/com_extend/helpers/route.php')) {
	include_once( JPATH_BASE . DS . '../components/com_extend/helpers/route.php');
} else {
	die('Could not found com_extend/helpers_route.php');
}
class plgSearchLucenesearch_lucene_extend extends JPlugin
{
  var $name = "Extend Searching";
  var $pluginName = "search_lucene_extend";
  var $version = "0.1";
  var $author = "Onur YALAZI";
  var $areas = array();
  

	var	$query	= "
    SELECT
     a.id,
     a.title,
     concat_ws(' ', a.introtext, a.fulltext, a.body) as content,
     substr(concat_ws(' ', a.introtext, a.fulltext, a.body), 1, 255) as summary,
     concat_ws(' ', [[TYPEFIELDS]] ) as textfields,
     a.created,
     a.catid,
     '[[TYPENAME]]' type
    FROM
      #__extend_articles a
    LEFT JOIN
      #__extend_types_[[TYPENAME]] f
    ON
      f.article_id = a.id
    WHERE
      [[WHERE]]
    ";

	function plgSearchLucenesearch_lucene_extend(& $subject)
	{
		parent::__construct($subject);
    $this->db = JFactory::getDBO();
    $this->setAreas();
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
    } else if (count($areas) == 0) {
	$areas = array();
	foreach ($this->areas as $k => $v) {
	      $areas[] = $k;
	}
    }

    $results = array();

    $params = JComponentHelper::getParams('com_search_lucene');
    $indexpath = $params->get('indexpath');
    $pluginindex = $indexpath . DS . $this->pluginName;
    
    $index = new Zend_Search_Lucene($pluginindex); 
    $search = '(';
    foreach ($areas as $area) {
      if (array_key_exists($area, $this->areas)) {
        $search .= ' type:' . $area;
      }
    }
    $search .= ')';
    if ($index && strlen($text) > 3) {
      $rows = $index->find($text . ' AND ' .  $search);
    } else {
      $rows = array();
    }
  
    return $rows;
  }

  function indexerGetFields() {
    /*
      Returns Documents Fields to be searched for indexer.
      fields named 'id', 'link', 'title',  and 'content' are mandatory.

      created field is mandatory but may be an empty string.
      
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
        'name' => 'textfields',
        'type' => 'unStored'
      ),
      array(
        'name' => 'created',
        'type' => 'text'
      ),
      array(
        'name' => 'type',
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
    if (!isset($this->current_type_index)) { $this->current_type_index = 0; };

    $where = " a.id > " . $this->counter . " AND a.type = " . $this->extend_types[$this->current_type_index] ;
    $ts = $this->getFieldsForType($this->extend_types[$this->current_type_index]);
    $type_fields = array();
    foreach ($ts as $type) {
      $type_fields[] = 'COALESCE( f.' . $type->column_name . ')';
    }
    $type_fields = implode(',', $type_fields);

    $sql = str_replace('[[WHERE]]', $where, $this->query);
    $sql = str_replace('[[TYPEFIELDS]]', $type_fields, $sql);
    $sql = str_replace('[[TYPENAME]]', $this->extend_types['name' . $this->current_type_index], $sql);
   
    echo $sql;
    $this->db->setQuery($sql . ' LIMIT 100');

    $articles = $this->db->loadObjectList();
    $resultset = array('resultset' => array());
    foreach ($articles as $article) {
      $lastid = $article->id;
      $article->link = ExtendHelperRoute::getArticleRoute($article->id, $article->catid);
      $resultset['resultset'][] = $article;
    }
    if (count($resultset['resultset']) > 0) {
      $this->counter = $lastid;
      return $resultset; //INDEX nothing yet
    } else {
      unset($this->counter);
      if (++$this->current_type_index == (count($this->extend_types)/2)) {
        echo "bitti";
        return false;
      }
      echo "changing type";
      return array('resultset' => array()); //Hey do not stop I will give you another type
    }
  }

  protected function setAreas() {
    $query = 'Select id, table_name, name from #__extend_types where published = 1' ;
    $this->db->setQuery($query);
    $this->areas = array();
    $this->extend_types = array();

    $results = $this->db->loadObjectList();
    $c = 0;
    foreach ($results as $area) {
      $this->areas[$area->table_name] = $area->name;
      $this->extend_types[$c] = $area->id;
      $this->extend_types['name' . $c++] = $area->table_name;
    }
  }
  protected function getFieldsForType($typeid) {
    $query = "select * from #__extend_fields f where f.article_type = $typeid and f.field_type = 'text'";
    $this->db->setQuery($query);
    return $this->db->loadObjectList();
  }
}
