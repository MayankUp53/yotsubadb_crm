<div class="mtDispositions view">
<h2><?php echo __('Mt Disposition'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($mtDisposition['MtDisposition']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Disp Cd'); ?></dt>
		<dd>
			<?php echo h($mtDisposition['MtDisposition']['disp_cd']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Item Name'); ?></dt>
		<dd>
			<?php echo h($mtDisposition['MtDisposition']['item_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($mtDisposition['MtDisposition']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created User Id'); ?></dt>
		<dd>
			<?php echo h($mtDisposition['MtDisposition']['created_user_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Updated'); ?></dt>
		<dd>
			<?php echo h($mtDisposition['MtDisposition']['updated']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Updated User Id'); ?></dt>
		<dd>
			<?php echo h($mtDisposition['MtDisposition']['updated_user_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Deleted'); ?></dt>
		<dd>
			<?php echo h($mtDisposition['MtDisposition']['deleted']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Deleted User Id'); ?></dt>
		<dd>
			<?php echo h($mtDisposition['MtDisposition']['deleted_user_id']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<?php if ($canWrite): ?><li><?php echo $this->Html->link(__('Edit Mt Contract'), array('action' => 'edit', $mtDisposition['MtDisposition']['id'])); ?> </li><?php endif ?>
		<?php if ($canWrite): ?><li><?php echo $this->Form->postLink(__('Delete Mt Contract'), array('action' => 'delete', $mtDisposition['MtDisposition']['id']), ['confirm' => __('Are you sure you want to delete # %s?', $mtDisposition['MtDisposition']['id'])]); ?> </li><?php endif ?>
		<li><?php echo $this->Html->link(__('List Mt Contracts'), array('action' => 'index')); ?> </li>
		<?php if ($canWrite): ?><li><?php echo $this->Html->link(__('New Mt Contract'), array('action' => 'add')); ?> </li><?php endif ?>
	</ul>
</div>
