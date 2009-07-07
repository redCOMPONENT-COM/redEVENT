<?php $infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_( 'NOTES' ) ); ?>
<?php $k = 0; ?>
<table class="adminform">
    <?php foreach ($this->customfields as $field): ?>
    <tr class="row<?php echo $k = 1 - $k; ?>">
      <td width="100" align="right" class="key">
        <label for="custom" class="hasTip" title="<?php echo JText::_($field->get('name')).'::'.JText::_('USE TAG') .': ['. $field->get('tag') .']'; ?>">
          <?php echo JText::_( $field->name ); ?>:
        </label>
      </td>
      <td>
        <?php echo $field->render(); ?>
      </td>   
    </tr>
    <?php endforeach; ?>
</table>
