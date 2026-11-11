<?php if(empty($tel)) $tel = null; ?>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">顧客情報検索条件</h3>
		</div>
		<?php echo $this->LegacyCrmForm->create('Customer'); ?>
		<table class="table">
			<tbody>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">電話番号（「-」有）</td>
					<td><?php echo $this->LegacyCrmForm->input('s_tel', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control', 'style'=>'width:100%;', 'value'=>$tel )); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">電話番号（「-」無）</td>
					<td><?php echo $this->LegacyCrmForm->input('s_phone1', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control', 'style'=>'width:100%;')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">携帯電話</td>
					<td><?php echo $this->LegacyCrmForm->input('s_mobile', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control', 'style'=>'width:100%;')); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">名前</td>
					<td><?php echo $this->LegacyCrmForm->input('s_customer_name', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control', 'style'=>'width:100%;')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">カナ</td>
					<td><?php echo $this->LegacyCrmForm->input('s_name_kana', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control', 'style'=>'width:100%;')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">郵便番号</td>
					<td><?php echo $this->LegacyCrmForm->input('s_zip', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control', 'style'=>'width:100%;')); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">住所1（県）</td>
					<td><?php echo $this->LegacyCrmForm->input('s_address1', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control', 'style'=>'width:100%;', 'value'=>$tel )); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">住所2（市区郡）</td>
					<td><?php echo $this->LegacyCrmForm->input('s_address2', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control', 'style'=>'width:100%;', 'value'=>$tel )); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">住所3（町地番）</td>
					<td><?php echo $this->LegacyCrmForm->input('s_address3', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control', 'style'=>'width:100%;', 'value'=>$tel )); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">住所4（建物名）</td>
					<td><?php echo $this->LegacyCrmForm->input('s_address4', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control', 'style'=>'width:100%;', 'value'=>$tel )); ?></td>
				</tr>
			</tbody>
		</table>
		<div style="width:95%;margin-bottom:5px;margin-top:5px;" align="center" class="btn_margin">
			<?php echo $this->LegacyCrmForm->submit('検　索', array('div'=>false, 'name' => 'searching', 'type'=>'submit', 'class'=>'btn-success btn-xs', 'style'=>'width:150px;height:30px;cursor:pointer')); ?>
			<?php echo $this->LegacyCrmForm->submit('クリア', array('div'=>false, 'name' => 'clear', 'type'=>'submit', 'class'=>'btn-success btn-xs', 'style'=>'width:150px;height:30px;cursor:pointer')); ?>
			<?php echo $this->LegacyCrmForm->submit('新規登録', array('div'=>false, 'name' => 'add', 'type'=>'submit', 'class'=>'btn-info btn-xs', 'style'=>'width:150px;height:30px;cursor:pointer')); ?>
			<?php
			if($legacyAuth == 9 ){
				echo $this->LegacyCrmForm->submit('CSV 出力', array('div'=>false, 'name' => 'download', 'type'=>'submit', 'class'=>'btn-success btn-xs', 'style'=>'width:150px;height:30px;cursor:pointer'));
			}
			?>
		</div>
	<?php echo $this->LegacyCrmForm->end(); ?>
	</div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">検索結果一覧</h3><span style="float:right;margin-top:-20px"><?php echo $this->Paginator->counter('{{page}}/{{pages}}ページ  {{start}}件目から{{end}}件目 検索結果{{count}}件'); ?></span>
		</div>
		<table class="table table-striped table-hover ">
			<tbody>
				<tr>
					<td width="12%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">電話番号「-」有</td>
					<td width="12%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">電話番号「-」無</td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">名前</td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">カナ</td>
					<td width="8%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">郵便番号</td>
					<td width="8%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">住所1</td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">住所2</td>
					<td width="15%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">住所3</td>
					<td width="15%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">住所4</td>
				</tr>
			</tbody>
			<?php foreach ($customers as $customer): ?>
			<?php
				$bg_color = "";
			/*
			if($customer['Customer']['created_user_id'] != 1 ){
				$bg_color = "#90ee90";
			}else{
				$bg_color = "#ffffff";
			}
			*/
			?>
			<tbody>
				<tr onDblClick="location.href='<?php echo $this->Url->build('/'); ?>customers/view/<?php echo $customer['Customer']['id']; ?> '" style="background-color:<?php echo $bg_color; ?>;">
					<td style="vertical-align:middle;"><?php echo h($customer['Customer']['tel']); ?></td>
					<td style="vertical-align:middle;"><?php echo h($customer['Customer']['phone1']); ?></td>
					<td style="vertical-align:middle;"><?php echo h($customer['Customer']['customer_name']); ?></td>
					<td style="vertical-align:middle;"><?php echo h($customer['Customer']['name_kana']); ?></td>
					<td style="vertical-align:middle;"><?php echo h($customer['Customer']['zip']); ?></td>
					<td style="vertical-align:middle;"><?php echo h($customer['Customer']['address1']); ?></td>
					<td style="vertical-align:middle;"><?php echo h($customer['Customer']['address2']); ?></td>
					<td style="vertical-align:middle;"><?php echo h($customer['Customer']['address3']); ?></td>
					<td style="vertical-align:middle;"><?php echo h($customer['Customer']['address4']); ?></td>
				</tr>
			</tbody>
			<?php endforeach; ?>
		<?php //echo var_dump($customers);  ?>
		</table>

		<div style="width:100%" align="center" class="paginateLinks btn_margin">
			<ul class="pagination pagination-sm">
				<?php echo $this->Paginator->first('<< ' . __('', true), array(), null, array('class'=>'disabled'));?>
				<?php echo $this->Paginator->prev('前へ ' . __('', true), array(), null, array('class'=>'disabled'));?>
				<?php echo $this->Paginator->numbers(); ?>

				<?php echo $this->Paginator->next(__('次へ', true) . '', array(), null, array('class' => 'disabled'));?>
				<?php echo $this->Paginator->last('>>'); ?>

			</ul>
		</div>
	</div>
