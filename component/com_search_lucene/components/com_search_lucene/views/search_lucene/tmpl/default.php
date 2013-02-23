<?php defined('_JEXEC') or die('Restricted access'); ?>


<form id="searchLuceneForm" action="<?php echo JRoute::_( 'index.php?option=com_search_lucene' );?>" method="post" name="searchLuceneForm">
	<table class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<tr>
			<td nowrap="nowrap">
				<label for="search_searchword">
					<?php echo JText::_( 'Search Keyword' ); ?>:
				</label>
			</td>
			<td nowrap="nowrap">
				<input type="text" name="searchword" id="search_searchword" size="30" maxlength="20" value="<?php echo $this->escape($this->searchword); ?>" class="inputbox" />
			</td>
			<td width="100%" nowrap="nowrap">
				<button name="Search" onclick="this.form.submit()" class="button"><?php echo JText::_( 'Search' );?></button>
			</td>
		</tr>
	</table>
	<?php if ($this->params->get( 'search_areas', 1 )) : ?>
		<?php echo JText::_( 'Search Only' );?>:
		<?php foreach ($this->searchareas['search'] as $val => $txt) :
			$checked = is_array( $this->searchareas['active'] ) && in_array( $val, $this->searchareas['active'] ) ? 'checked="checked"' : '';
		?>
		<input type="checkbox" name="areas[]" value="<?php echo $val;?>" id="area_<?php echo $val;?>" <?php echo $checked;?> />
			<label for="area_<?php echo $val;?>"><?php echo JText::_($txt) ?></label>
		<?php endforeach; ?>
	<?php endif; ?>
	
  <table class="searchintro<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<tr>
		<td colspan="3" >
			<br />
			<?php echo JText::_( 'Search Keyword' ) .' <b>'. $this->escape($this->searchword) .'</b>'; ?>
		</td>
	</tr>
	<tr>
		<td>
			<br />
      <?php echo $this->result; ?>
		</td>
	</tr>
</table>

<br />

<input type="hidden" name="task"   value="search" />
</form>

<table class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
  <tr>
    <td>
      <?php foreach( $this->results as $result ) : ?>
        <fieldset>
          <div>
            <?php if ( $result->link ) : ?>
              <a href="<?php echo JRoute::_($result->link); ?>">
                <?php echo $this->escape($result->title); ?>
              </a><br />
            <?php endif; ?>
            <?php echo JText::_('Result Score:') ?> <?php printf("%3.2f", $result->score) ?>
          </div>
          <div>
            <?php echo $result->content; ?>
          </div>
          <?php if ( $this->params->get( 'show_date' )) : ?>
            <div class="small<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
              <?php echo $result->created; ?>
            </div>
          <?php endif; ?>
        </fieldset>
      <?php endforeach; ?>
    </td>
  </tr>
</table>
