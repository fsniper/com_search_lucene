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
  */ 

  // Set flag that this is a parent file
  define( '_JEXEC', 1 );
  define( 'JPATH_BASE', dirname(__FILE__) );
  define( 'DS', DIRECTORY_SEPARATOR );

  if (!file_exists('Zend/Search/Lucene.php')) {
    echo "This software depends on Zend Frameworks Lucene Search Component.\n";
    echo "You should install Zend Framework Minimum Package's library/Zend directory \n";
    echo "alongside this file.\n";
    echo "\n";
    echo "You may download Zend Framework from http://framework.zend.com\n";
    die();
  }

  include 'Zend/Search/Lucene.php';

  require_once JPATH_BASE.DS.'includes'.DS.'defines.php';
  require_once JPATH_BASE.DS.'includes'.DS.'framework.php';

  jimport('joomla.plugin.plugin');

  JError::setErrorHandling( E_ERROR,	 'die' );
  JError::setErrorHandling( E_WARNING, 'echo' );
  JError::setErrorHandling( E_NOTICE,	 'echo' );

  // create the mainframe object
  $mainframe =& JFactory::getApplication('site');
 
  if (!JComponentHelper::isEnabled('com_search_lucene', true)) {
    die('com_lucene_search is not enabled. Indexer is useless.\n');
  }

  $params = JComponentHelper::getParams('com_search_lucene');
  $indexpath = $params->get('indexpath');
  
  if (!file_exists($indexpath) || is_dir(!$indexpath)) {
    die($indexpath . 
        " does not exist or is not a directory. \n" . 
        "Please configure com_search_lucene parameters in the administrator console.\n" .
        "And create index directory to use com_search_lucene\n\n");
  }

  $plugins = JPluginHelper::_load();
  foreach ($plugins as $plugin) {
    if ($plugin->type == 'SearchLucene' ) {
      $startDate = date('Y/m/d H:i');
      if(!is_object($dispatcher)) {
        $dispatcher = & JDispatcher::getInstance();
      }

      $path    = JPATH_PLUGINS.DS.$plugin->type.DS.$plugin->name.'.php';
      $className = 'plg'.$plugin->type.$plugin->name;

      require_once( $path );
      
      $instance = new $className($dispatcher, $plugin);

      $pluginindex = $indexpath . DS . $plugin->name;
      $index = new Zend_Search_Lucene($pluginindex, true);

      $fields = $instance->indexerGetFields();
      if ($fields) {
        while ($data = $instance->indexerGetData()) {

          // Results can be packed in an array of array('resultset' => $results)
          // or can be returned one by one.
          // Every result can be an object or an array.
          // every field will be accessed in object->field or array['field'] manner
          // depending on the result type

          if (is_array($data) && isset($data['resultset']) && count($data['resultset']) > 0 ) {
            foreach($data['resultset'] as $data) {
              indexData($index,$fields,$data);
            }
          } else if ($data != null && $data !== false) {
              indexData($index, $fields, $data);
          }
        }
        $index->optimize();
        $info = new stdClass();
        $info->startDate = $startDate;
        $info->date = date('Y/m/d H:i');
        $info->totalCount = $index->count();
        $info->documentCount = $index->numDocs();

        file_put_contents($pluginindex . '.info' , json_encode($info));
      }
    }
  }
  
  function indexData($index, $fields, $data) {
    if (is_array($data) && !isset($data['content']) ) return;
    if (is_object($data) && !isset($data->content) ) return;

    $doc = new Zend_Search_Lucene_Document();
    foreach ($fields as $field) {
      $type = $field['type'];
      $doc->addField(Zend_Search_Lucene_Field::$type(
        $field['name'], 
        is_array($data) ? $data[$field['name']] : $data->{$field['name']}
      ));
    }
    // Remove old document if indexed before
    $hits = $index->find('id:' . (is_array($data) ? $data['id'] : $data->id));
    foreach ($hits as $hit) {
        $index->delete($hit->id);
    }
    
    //Reindex 
    $index->addDocument($doc); //index this document.
  }

?>
