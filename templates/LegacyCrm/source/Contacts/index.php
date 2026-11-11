<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">対応履歴検索条件</h3>
		</div>
		<?php echo $this->LegacyCrmForm->create('Contact'); ?>
		<table class="table">
			<tbody>
				<tr>
					<td width="15%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">登録日</td>
					<td width="35%"><?php echo $this->LegacyCrmForm->input('s_created_from', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control datepicker', 'size'=>'10')); ?>-<?php echo $this->LegacyCrmForm->input('s_created_to', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control datepicker', 'size'=>'10')); ?></td>
					<td width="15%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">更新日</td>
					<td width="35%"><?php echo $this->LegacyCrmForm->input('s_updated_from', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control datepicker', 'size'=>'10')); ?>-<?php echo $this->LegacyCrmForm->input('s_updated_to', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control datepicker', 'size'=>'10')); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">電話番号（「-」有）</td>
					<td><?php echo $this->LegacyCrmForm->input('s_tel', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">電話番号（「-」無し）</td>
					<td><?php echo $this->LegacyCrmForm->input('s_phone1', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100')); ?></td>

				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">携帯電話</td>
					<td><?php echo $this->LegacyCrmForm->input('s_mobile', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control  if100')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">リスト名</td>
					<td><?php echo $this->LegacyCrmForm->input('s_list_name', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control  if100')); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">名前</td>
					<td><?php echo $this->LegacyCrmForm->input('s_customer_name', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control  if100')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">カナ</td>
					<td><?php echo $this->LegacyCrmForm->input('s_name_kana', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control  if100')); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">住所1（県）</td>
					<td><?php echo $this->LegacyCrmForm->input('s_address1', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control  if100')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">住所2（市区郡）</td>
					<td><?php echo $this->LegacyCrmForm->input('s_address2', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control  if100')); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">住所3（町地番）</td>
					<td><?php echo $this->LegacyCrmForm->input('s_address3', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control  if100')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">住所4（建物名）</td>
					<td><?php echo $this->LegacyCrmForm->input('s_address4', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control  if100')); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">年齢</td>
					<td><?php echo $this->LegacyCrmForm->input('s_age', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control  if100')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">年代</td>
					<td><?php echo $this->LegacyCrmForm->input('s_nendai', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control  if100', 'options' => $nendaiOpt, 'empty'=>'-----'   )); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">結果</td>
					<td><?php echo $this->LegacyCrmForm->input('s_kekka', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control  if100', 'options' => $dispositionOpt, 'empty'=>'-----'   )); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">先送り日</td>
					<td><?php echo $this->LegacyCrmForm->input('s_sakiokuri_date_from', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control datepicker', 'size'=>'10')); ?>-<?php echo $this->LegacyCrmForm->input('s_sakiokuri_date_to', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control datepicker', 'size'=>'10')); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">購入商品</td>
					<td><?php echo $this->LegacyCrmForm->input('s_product', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control if100', 'options' => $productOpt, 'empty'=>'-----'   )); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">購入金額合計</td>
					<td><?php echo $this->LegacyCrmForm->input('s_total_price_from', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control', 'size'=>'10')); ?>-<?php echo $this->LegacyCrmForm->input('s_total_price_to', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control', 'size'=>'10')); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">購入動機</td>
					<td><?php echo $this->LegacyCrmForm->input('s_reason', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control if100', 'options' => $statusOpt, 'empty'=>'-----')); ?></td>

				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">AP担当者ID</td>
					<td><?php echo $this->LegacyCrmForm->input('s_ap_id', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100' )); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">AP担当者名</td>
					<td><?php echo $this->LegacyCrmForm->input('s_ap_name', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100' )); ?></td>
				</tr>
			</tbody>
		</table>
		<div style="width:95%;margin-bottom:5px;margin-top:5px;" align="center" class="btn_margin">
			<?php echo $this->LegacyCrmForm->submit('検　索', array('div'=>false, 'name' => 'searching', 'type'=>'submit', 'class'=>'btn-success btn-xs', 'style'=>'width:150px;height:30px;cursor:pointer')); ?>
			<?php echo $this->LegacyCrmForm->submit('クリア', array('div'=>false, 'name' => 'clear', 'type'=>'submit', 'class'=>'btn-success btn-xs', 'style'=>'width:150px;height:30px;cursor:pointer')); ?>
			<?php
			if($legacyAuth == 9 ){
				echo $this->LegacyCrmForm->submit('CSV 出力', array('div'=>false, 'name' => 'download', 'type'=>'submit', 'class'=>'btn-success btn-xs', 'style'=>'width:150px;height:30px;cursor:pointer'));
			}
			?>
		</div>
		<?php echo $this->LegacyCrmForm->end();?>
	</div>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">検索結果一覧</h3><span style="float:right;margin-top:-20px"><?php echo $this->Paginator->counter('{{page}}/{{pages}}ページ  {{start}}件目から{{end}}件目 検索結果{{count}}件'); ?></span>
		</div>
		<table class="table table-striped table-hover ">
			<tbody>
				<tr>
					<td width="9%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">更新日時</td>
					<td width="9%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">電話番号</td>
					<td width="12%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">リスト名</td>
					<td width="5%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">結果</td>
					<td width="16%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">商品名</td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">名前</td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">カナ</td>
					<td width="4%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">年齢</td>
					<td width="9%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">先送り日時</td>
					<td width="5%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">AP ID</td>
					<td width="11%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">AP名</td>
				</tr>
			</tbody>
			<?php //echo var_dump($contacts)?>

			<?php foreach ($contacts as $row): ?>
			<?php
				//$bg_color = "#ffb6c1";
				$bg_color = "#ffffff";

			?>
			<tbody>
				<tr onDblClick="location.href='<?php echo $this->Url->build('/'); ?>contacts/view/<?php echo $row['Contact']['id']; ?> '" style="background-color:<?php echo $bg_color; ?>;">
					<td style="vertical-align:middle;"><?php echo date ("Y-m-d H:i:s", strtotime($row['Contact']['updated'])); ?></td>
					<td style="vertical-align:middle;"><?php echo h($row['Customer']['tel']); ?></td>
					<td style="vertical-align:middle;"><?php echo h($row['Contact']['list_name']); ?></td>
					<td style="vertical-align:middle;"><?php if(!empty($dispositionOpt[$row['Contact']['kekka']]) ) echo h($dispositionOpt[$row['Contact']['kekka']]); ?></td>
					<td style="vertical-align:middle;"><?php if(!empty($productOpt[$row['Contact']['product_name1']]) ) echo h($productOpt[$row['Contact']['product_name1']]); ?></td>
					<td style="vertical-align:middle;"><?php echo h($row['Customer']['customer_name']); ?></td>
					<td style="vertical-align:middle;"><?php echo h($row['Customer']['name_kana']); ?></td>
					<td style="vertical-align:middle;"><?php echo h($row['Customer']['age']); ?></td>
					<td style="vertical-align:middle;"><?php echo h(substr($row['Contact']['sakiokuri_datetime'],0,16)); ?></td>
					<td style="vertical-align:middle;"><?php echo h($row['Contact']['ap_id']); ?></td>
					<td style="vertical-align:middle;"><?php echo h($row['Contact']['ap_name']); ?></td>
				</tr>
			</tbody>
			<?php endforeach; ?>
		<?php //echo var_dump($contacts);  ?>
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
