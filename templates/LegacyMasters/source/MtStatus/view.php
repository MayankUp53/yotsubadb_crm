<div class="mtStatuses view">
<h2><?php echo __('Mt Status'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($mtStatus['MtStatus']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Disp Cd'); ?></dt>
		<dd>
			<?php echo h($mtStatus['MtStatus']['disp_cd']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Item Name'); ?></dt>
		<dd>
			<?php echo h($mtStatus['MtStatus']['item_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($mtStatus['MtStatus']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created User Id'); ?></dt>
		<dd>
			<?php echo h($mtStatus['MtStatus']['created_user_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Updated'); ?></dt>
		<dd>
			<?php echo h($mtStatus['MtStatus']['updated']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Updated User Id'); ?></dt>
		<dd>
			<?php echo h($mtStatus['MtStatus']['updated_user_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Deleted'); ?></dt>
		<dd>
			<?php echo h($mtStatus['MtStatus']['deleted']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Deleted User Id'); ?></dt>
		<dd>
			<?php echo h($mtStatus['MtStatus']['deleted_user_id']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<?php if ($canWrite): ?><li><?php echo $this->Html->link(__('Edit Mt Status'), array('action' => 'edit', $mtStatus['MtStatus']['id'])); ?> </li><?php endif ?>
		<?php if ($canWrite): ?><li><?php echo $this->Form->postLink(__('Delete Mt Status'), array('action' => 'delete', $mtStatus['MtStatus']['id']), ['confirm' => __('Are you sure you want to delete # %s?', $mtStatus['MtStatus']['id'])]); ?> </li><?php endif ?>
		<li><?php echo $this->Html->link(__('List Mt Statuses'), array('action' => 'index')); ?> </li>
		<?php if ($canWrite): ?><li><?php echo $this->Html->link(__('New Mt Status'), array('action' => 'add')); ?> </li><?php endif ?>
	</ul>
</div>
