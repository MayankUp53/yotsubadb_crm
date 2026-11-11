<div class="mtCategories view">
<h2><?php echo __('Mt Category'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($mtSubcategory['MtSubcategory']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Disp Cd'); ?></dt>
		<dd>
			<?php echo h($mtSubcategory['MtSubcategory']['disp_cd']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Item Name'); ?></dt>
		<dd>
			<?php echo h($mtSubcategory['MtSubcategory']['item_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($mtSubcategory['MtSubcategory']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created User Id'); ?></dt>
		<dd>
			<?php echo h($mtSubcategory['MtSubcategory']['created_user_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Updated'); ?></dt>
		<dd>
			<?php echo h($mtSubcategory['MtSubcategory']['updated']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Updated User Id'); ?></dt>
		<dd>
			<?php echo h($mtSubcategory['MtSubcategory']['updated_user_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Deleted'); ?></dt>
		<dd>
			<?php echo h($mtSubcategory['MtSubcategory']['deleted']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Deleted User Id'); ?></dt>
		<dd>
			<?php echo h($mtSubcategory['MtSubcategory']['deleted_user_id']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<?php if ($canWrite): ?><li><?php echo $this->Html->link(__('Edit Mt Subcategory'), array('action' => 'edit', $mtSubcategory['MtSubcategory']['id'])); ?> </li><?php endif ?>
		<?php if ($canWrite): ?><li><?php echo $this->Form->postLink(__('Delete Mt Subcategory'), array('action' => 'delete', $mtSubcategory['MtSubcategory']['id']), ['confirm' => __('Are you sure you want to delete # %s?', $mtSubcategory['MtSubcategory']['id'])]); ?> </li><?php endif ?>
		<li><?php echo $this->Html->link(__('List Mt Subcategory'), array('action' => 'index')); ?> </li>
		<?php if ($canWrite): ?><li><?php echo $this->Html->link(__('New Mt Subcategory'), array('action' => 'add')); ?> </li><?php endif ?>
	</ul>
</div>
