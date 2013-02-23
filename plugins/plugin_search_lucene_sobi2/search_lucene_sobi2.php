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

class plgSearchLucenesearch_lucene_sobi2 extends JPlugin
{
  var $name = "Sobi2 Searching";
  var $pluginName = "search_lucene_sobi2";
  var $version = "0.1";
  var $author = "Onur YALAZI";
  var $areas = array('sobi2' => 'Sobi2');
  

	var	$query	= "
    SELECT
      s.itemid as id,
      s.title,
      '' as created,
      '[[SOBINAME]]' as section,
      group_concat(data_txt SEPARATOR ' ') as content,
      substr(group_concat(data_txt SEPARATOR ' '),1, 255) as summary,
			CONCAT('index.php?option=com_sobi2&amp;sobi2Task=sobi2Details&amp;sobi2Id=', s.itemid [[SOBIITEM]], '&catid=', c.catid) AS link
    FROM
      #__sobi2_item s
    LEFT JOIN
      #__sobi2_fields_data fd
    ON
        fd.itemid = s.itemid 
    LEFT JOIN
      #__sobi2_cat_items_relations c
    ON
        s.itemid = c.itemid 
    WHERE
      [[WHERE]]
    GROUP BY 
      fd.itemid
    ";

	function plgSearchLucenesearch_lucene_sobi2(& $subject)
	{
		parent::__construct($subject);
    $this->db = JFactory::getDBO();
    $sitemid = $this->getSobiItemId();
    $where_item = $sitemid > 0 ? ",'&amp;ItemId=$sitemid'" : '';

    $this->query = str_replace('[[SOBINAME]]', $this->getSobiComponentName(), $this->query);
    $this->query = str_replace('[[SOBIITEM]]', $where_item, $this->query);
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
        'name' => 'section',
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
    if (!isset($this->counter)) { $this->counter = 0; };

		$where = " s.itemid > " . $this->counter ;
    $sql = str_replace('[[WHERE]]', $where, $this->query);
echo $sql;
    $this->db->setQuery($sql . ' LIMIT 100');


    $sobis = $this->db->loadObjectList();
    $resultset = array('resultset' => array());
    foreach ($sobis as $sobi) {
      $lastid = $sobi->id;
      $resultset['resultset'][] = $sobi;
    }
    if ($resultset['resultset'] != null && count($resultset['resultset']) > 0) {
      $this->counter = $lastid;
      return  $resultset;
    } else {
      unset($this->counter);
      return false;
    }
  }

  protected function getSobiComponentName() {
    $db =& JFactory::getDBO();
    $query	= "SELECT configValue FROM #__sobi2_config WHERE configKey = 'componentName'";
    $db->setQuery( $query );
    $componentName = $db->loadResult();
    if (!$componentName) {
      $componentName = 'SOBI2';
    }
    return $componentName;
  }

  protected function getSobiItemId() {
    $db =& JFactory::getDBO();
    $query	= "SELECT id FROM #__menu WHERE published = 1 AND LOWER(link) LIKE '%option=com_sobi2%'";
    $db->setQuery( $query );
    $sobiItemId = (int) $db->loadResult();
    return $sobiItemId;
  }
}
