<?php echo $this->Form->create($record,array('class'=>'form-horizontal') ); ?>

	<div class="panel panel-info filterable">
		<div class="panel-heading">
			<h3 class="panel-title">状況区分新規登録</h3>
		</div>
		<table class="table table-hover ">
			<tbody>

				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">名称</td>
					<td><?php echo $this->Form->control('item_name', array('label'=>false, 'required' => false,'required' => false, 'div'=>false, 'type' => 'text', 'class'=>'form-control' )); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">有効無効</td>
					<td><?php echo $this->Form->control($activeField, array('label'=>false, 'required' => false,'div'=>false, 'type' => 'select', 'class'=>'form-control', 'options' => $activeOpt, 'empty'=>'-----' )); ?></td>
				</tr>
				<tr>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">登録者</td>
					<td width="18%"></td>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">登録日時</td>
					<td width="18%"></td>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">更新者</td>
					<td width="18%"></td>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">更新日時</td>
					<td width="18%"></td>
				</tr>

			</tbody>
		</table>
		<br />
		<div style="width:95%;" align="center">
			<button type="button" style="width:150px;height:30px;cursor:pointer;" onclick="location.href='<?php echo $this->Url->build('/mt_subcategories/index/');?>';" class="btn-default btn-xs" >キャンセル</button>
			<?php echo $this->Form->submit('登　録', array('div'=>false, 'name' => 'add', 'type'=>'submit', 'style'=>'width:150px;height:30px;cursor:pointer', 'class'=>'btn-info btn-xs')); ?>
		</div>
		<br />
	</div>

<?php echo $this->Form->end(); ?>
<br>
