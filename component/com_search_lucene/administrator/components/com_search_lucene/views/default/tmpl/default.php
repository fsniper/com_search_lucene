<?php
defined('_JEXEC') or die('Restricted access');
JToolBarHelper::title(JText::_('Com Search Lucene'), 'generic.png');
JToolBarHelper::preferences('com_search_lucene');
?>

<table>
<?php foreach( $this->results as $plugin):  ?>
  <tr>
    <td><?php echo $plugin['name'] ?></td>
    <td><?php echo $plugin['author'] ?></td>
    <td><?php echo $plugin['version'] ?></td>
  </tr>
<?php endforeach; ?>
</table>
