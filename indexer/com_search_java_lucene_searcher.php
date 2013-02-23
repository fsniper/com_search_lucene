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
  define("QCACHE_BACKEND","none"); 
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
  if ($_SERVER['argc'] < 2) {
    echo "Usage: " . $_SERVER['argv'][0]. " searchkey1 searchkey2 searchkey3 ...";
    die();
  }
  $_q = "";
  for ($i = 1; $i < $_SERVER['argc']; $i++) {
    $_q .= $_SERVER['argv'][$i] . " ";
  }

  $plugins = JPluginHelper::_load();

  $analyzer = new java("org.apache.lucene.analysis.standard.StandardAnalyzer"); 
  $resultList = new java("java.util.LinkedList");
  $queryparser = new java("org.apache.lucene.queryParser.QueryParser", "title", $analyzer);
  $_qobj = $queryparser->parse($_q);
  
  foreach ($plugins as $plugin) {
    if ($plugin->type == 'SearchLucene' ) {
      $startDate = date('Y/m/d H:i');
      if(!isset($dispatcher) || !is_object($dispatcher)) {
        $dispatcher = & JDispatcher::getInstance();
      }

      $path    = JPATH_PLUGINS.DS.$plugin->type.DS.$plugin->name.'.php';
      $className = 'plg'.$plugin->type.$plugin->name;

      require_once( $path );
      
      $instance = new $className($dispatcher, $plugin);

      $pluginindex = $indexpath . DS . $plugin->name;
      $index = new java("java.io.File", $pluginindex);
      if (!$index->exists()) {
        continue;
      }
      $searcher = new java("org.apache.lucene.search.IndexSearcher", $index);
      $phrase = new java("org.apache.lucene.search.MatchAllDocsQuery");
      $hits = $searcher->search($phrase);
      //$hits = $searcher->search($_qobj);
      $n = java_values($hits->length());
      echo $n . " results for " . $plugin->name . "\n";
      if ($n > 0) {
        $iter = $hits->iterator();

        java_begin_document();
        while($n--) {
          $next = $iter->next();
          $title = $next->get("title");
          $content = $next->get("summary");
          $resultList->add($title . "\n" . $content);
        }
        java_end_document();
      }
    }
    $result = java_values($resultList); 
  }
  print_r($result);
  
?>
