com_search_lucene
=================


# Introduction

com_search_lucene is a Joomla 1.5 extension set, consisting of a search component, some search plugins and an indexing application. com-search-lucene brings Apache Lucene's indexing and searching capabilities to the Joomla! world. Native Joomla! com_search component uses plugins that directly uses databases search capabilities. This brings the database feature lock-in. For example some search plugins may use Mysql Full-Text Search ability that's available with the MyISAM storage engine but lacking with InnoDB storage engine. So sites depending on these search plugins are locked in to MyISAM and can not use innodb or any other storage engine which lacks full text search.

com_search_lucene can be used with plain php via Zend Framework's Zend-lucene library or with native java lucene via php-java-bridge. For the moment, only indexer can use native lucene library.

# Structure of com_search_lucene
com_search_lucene has full Joomla application integration. There is a component part to replace com_search and there are plugins to change Joomla! com_search's search plugins. com_search_lucene plugins use SearchLucene? plugin type name. These plugins describe how indexing occurs and and makes searches in these indexes. A SearchLucene? Plugin means a new index will be created by the indexer for the plugin.

com_search_lucene Indexer is a self contained Joomla! Application like the xml-rpc app distributed with the Joomla! package. This application is run by a scheduler like linux/unix cron or Windows Scheduled Tasks. At the moment every index, that is described with enabled SearchLucene? Pluginsi is deleted and recreated everytime the indexer runs. So re-indexing is just a new index creation.

There are two Indexer Applications. All written in php.

One using Zend Framework's ZendLucene? php library, not needing any java components. - Caution: This is really a slow indexer.
One using php-java-bridge and native Java Lucene library, depending on php-java-lucene, a java application server, a java vm and Apache Lucene library.
Indexer applications are console Joomla! applications. And can be run from Cron daemon.

# Install
## Installing The indexer
### Preliminary Steps
- Download com_search_lucene indexer package
- Download ZendFramework? Minimal Package
- Extract com_search_lucene indexer package into your Joomla! root directory. A directory named search_lucene should appear.
- Extract ZendFramework? Minimal Package into search_lucene directory. This will create a directory like ZendFramework?-1.9.3PL1-minimal (note: your Zend Library version may be different. It's not advised to use a ZendFramework? version lower than 1.9.3)
- Move or softlink ZendFramework?-1.9.3PL1-minimal/library/Zend directory to search_lucene/Zend directory.


### Installing Indexer with ZendFramework SearchLucene Library

If you would like to use native php and no java, After doing preliminary steps no extra steps are required. You can jump to section named "Configuring com_search_lucene" to learn more about using the indexer.
Installing Indexer with php-java-bridge

If you would like to use Java Lucene, you have to install php-java-bridge. It's installation is not a subject of this document. You have to consult php-java-bridge documentation. These documents can be found at their site.
-After installing php-java-bridge you have to install Apache Lucene into php-java-bridge applications WEB-INF/lib directory, for my debian/tomcat5.5/JavaBridge553? installation this directory is /var/lib/tomcat5.5/webapps/JavaBridgeTemplate553?/WEB-INF/lib/.
-After redeployment of the application you must change two define lines located in the com_search_lucene_java_indexer.php,
          define("JAVA_HOSTS", "localhost:8080");
          define("JAVA_SERVLET", "/JavaBridgeTemplate553/JavaBridge.phpjavabridge");
for your setup. JAVA_HOSTS constant is your application server's url and port, JAVA_SERVLET constant is your php-java-bridge applications path on the application server.

# Installing Component 

Installing the component is a normal component installation procedure.
