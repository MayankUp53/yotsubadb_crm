<div class="mtProducts view">
<h2><?php echo __('Mt Product'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($mtProduct['MtProduct']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Disp Cd'); ?></dt>
		<dd>
			<?php echo h($mtProduct['MtProduct']['disp_cd']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Item Name'); ?></dt>
		<dd>
			<?php echo h($mtProduct['MtProduct']['item_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($mtProduct['MtProduct']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created User Id'); ?></dt>
		<dd>
			<?php echo h($mtProduct['MtProduct']['created_user_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Updated'); ?></dt>
		<dd>
			<?php echo h($mtProduct['MtProduct']['updated']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Updated User Id'); ?></dt>
		<dd>
			<?php echo h($mtProduct['MtProduct']['updated_user_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Deleted'); ?></dt>
		<dd>
			<?php echo h($mtProduct['MtProduct']['deleted']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Deleted User Id'); ?></dt>
		<dd>
			<?php echo h($mtProduct['MtProduct']['deleted_user_id']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<?php if ($canWrite): ?><li><?php echo $this->Html->link(__('Edit Mt Product'), array('action' => 'edit', $mtProduct['MtProduct']['id'])); ?> </li><?php endif ?>
		<?php if ($canWrite): ?><li><?php echo $this->Form->postLink(__('Delete Mt Product'), array('action' => 'delete', $mtProduct['MtProduct']['id']), ['confirm' => __('Are you sure you want to delete # %s?', $mtProduct['MtProduct']['id'])]); ?> </li><?php endif ?>
		<li><?php echo $this->Html->link(__('List Mt Products'), array('action' => 'index')); ?> </li>
		<?php if ($canWrite): ?><li><?php echo $this->Html->link(__('New Mt Product'), array('action' => 'add')); ?> </li><?php endif ?>
	</ul>
</div>
