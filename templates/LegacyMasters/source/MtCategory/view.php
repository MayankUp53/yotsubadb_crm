<div class="mtCategories view">
<h2><?php echo __('Mt Category'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($mtCategory['MtCategory']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Disp Cd'); ?></dt>
		<dd>
			<?php echo h($mtCategory['MtCategory']['disp_cd']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Item Name'); ?></dt>
		<dd>
			<?php echo h($mtCategory['MtCategory']['item_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($mtCategory['MtCategory']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created User Id'); ?></dt>
		<dd>
			<?php echo h($mtCategory['MtCategory']['created_user_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Updated'); ?></dt>
		<dd>
			<?php echo h($mtCategory['MtCategory']['updated']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Updated User Id'); ?></dt>
		<dd>
			<?php echo h($mtCategory['MtCategory']['updated_user_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Deleted'); ?></dt>
		<dd>
			<?php echo h($mtCategory['MtCategory']['deleted']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Deleted User Id'); ?></dt>
		<dd>
			<?php echo h($mtCategory['MtCategory']['deleted_user_id']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<?php if ($canWrite): ?><li><?php echo $this->Html->link(__('Edit Mt Category'), array('action' => 'edit', $mtCategory['MtCategory']['id'])); ?> </li><?php endif ?>
		<?php if ($canWrite): ?><li><?php echo $this->Form->postLink(__('Delete Mt Category'), array('action' => 'delete', $mtCategory['MtCategory']['id']), ['confirm' => __('Are you sure you want to delete # %s?', $mtCategory['MtCategory']['id'])]); ?> </li><?php endif ?>
		<li><?php echo $this->Html->link(__('List Mt Categories'), array('action' => 'index')); ?> </li>
		<?php if ($canWrite): ?><li><?php echo $this->Html->link(__('New Mt Category'), array('action' => 'add')); ?> </li><?php endif ?>
	</ul>
</div>
