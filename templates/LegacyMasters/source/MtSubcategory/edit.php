<?php echo $this->Form->create($record,array('class'=>'form-horizontal') ); ?>
<?php echo $this->Form->control('id',array('type'=>'hidden'));?>

	<div class="panel panel-warning filterable">
		<div class="panel-heading">
			<h3 class="panel-title">状況区分更新</h3>
		</div>
		<table class="table table-hover ">
			<tbody>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">名称</td>
					<td><?php echo $this->Form->control('item_name', array('label'=>false, 'required' => false,'div'=>false, 'type' => 'text', 'class'=>'form-control' )); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">有効無効</td>
					<td><?php echo $this->Form->control($activeField, array('label'=>false, 'required' => false,'div'=>false, 'type' => 'select', 'class'=>'form-control', 'options' => $activeOpt, 'empty'=>'-----' )); ?></td>
				</tr>
				<tr>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">登録者</td>
					<td width="18%"><?php echo h($opeOpt[$legacyRecord['MtSubcategory']['created_user_id']]); ?></td>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">登録日時</td>
					<td width="18%"><?php echo $legacyRecord['MtSubcategory']['created']; ?></td>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">更新者</td>
					<td width="18%"><?php echo h($opeOpt[$legacyRecord['MtSubcategory']['updated_user_id']]); ?></td>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">更新日時</td>
					<td width="18%"><?php echo $legacyRecord['MtSubcategory']['updated']; ?></td>
				</tr>

			</tbody>
		</table>
		<br />
		<div style="width:95%;" align="center">
			<?php echo $this->Form->submit('キャンセル', array('div'=>false, 'name' => 'back', 'type'=>'submit', 'style'=>'width:150px;height:30px;cursor:pointer', 'class'=>'btn-default btn-xs')); ?>
			<?php echo $this->Form->submit('更　新', array('div'=>false, 'name' => 'add', 'type'=>'submit', 'style'=>'width:150px;height:30px;cursor:pointer', 'class'=>'btn-warning btn-xs')); ?>
		</div>
		<br />
	</div>

<?php echo $this->Form->end(); ?>
<br>
