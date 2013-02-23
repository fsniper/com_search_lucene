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

  define("JAVA_DISABLE_AUTOLOAD", true);
  define("JAVA_HOSTS", "localhost:8080");
  define("JAVA_SERVLET", "/JavaBridgeTemplate553/JavaBridge.phpjavabridge");
  include "Java.inc";

  // Set flag that this is a parent file
  define( '_JEXEC', 1 );
  define( 'JPATH_BASE', dirname(__FILE__) );
  define( 'DS', DIRECTORY_SEPARATOR );

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
  
  $analyzer = new java("org.apache.lucene.analysis.standard.StandardAnalyzer");

  foreach ($plugins as $plugin) {
    if ($plugin->type == 'SearchLucene' ) {
      $startDate = date('Y/m/d H:i');
      if(!is_object($dispatcher)) {
        $dispatcher = & JDispatcher::getInstance();
      }

      $path    = JPATH_PLUGINS.DS.$plugin->type.DS.$plugin->name.'.php';
      $className = 'plg'.$plugin->type.$plugin->name;

      require_once( $path );
     
	echo $className; 
      $instance = new $className($dispatcher, $plugin);

      $pluginindex = $indexpath . DS . $plugin->name;
      $index = new java("java.io.File", $pluginindex);
      if (!$index->exists()) {
        $index->mkdir();
      }

      $writer = new java("org.apache.lucene.index.IndexWriter", $index, $analyzer, true);

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
              if ($data!=null) indexData($writer,$fields,$data);
            }
          } else if ($data != null && $data !== false) {
              if ($data!=null) indexData($writer, $fields, $data);
          }
        }
	unset($data);
        $writer->optimize();

        $info = new stdClass();
        $info->startDate = $startDate;
        $info->date = date('Y/m/d H:i');
        $info->documentCount = java_values($writer->docCount());
        $writer->close();

        file_put_contents($pluginindex . '.info' , json_encode($info));
      }
    }
  }
  
  function indexData($writer, $fields, $data) {
    if (is_array($data) && !isset($data['content']) ) return;
    if (is_object($data) && !isset($data->content) ) return;

    $doc = new java("org.apache.lucene.document.Document");
    foreach ($fields as $field) {
      $type = $field['type'];
  
      $store = ($type == 'unStored') ? 
        java('org.apache.lucene.document.Field$Store')->NO : 
        java('org.apache.lucene.document.Field$Store')->YES;

      $index = ($type == 'text' || $type == 'unStored') ? 
        java('org.apache.lucene.document.Field$Index')->TOKENIZED : 
        java('org.apache.lucene.document.Field$Index')->NO;


	$content = is_array($data) ? $data[$field['name']] : $data->{$field['name']};
	if ($content==null) {
		$content = "";
		echo 'id:' . (is_array($data) ? $data['id'] : $data->id) . ' :: ' . $field['name']; 
	}
      $doc->add(
        new java(
          "org.apache.lucene.document.Field",
          $field['name'],
	  $content,
          $store,
          $index
        )
      );
	unset($index);
	unset($store);
    }
    //Reindex 
    $writer->addDocument($doc); //index this document.
	unset($doc);
  }

?>
