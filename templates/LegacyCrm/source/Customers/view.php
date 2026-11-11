<?php //echo var_dump($customer) ; ?>

<?php
//ID・単価をhiddenにセット
for ($i = 0 ; $i < count($products); $i++) {
	//$this->LegacyCrmForm->input($products[$i]['MtProduct']['id'],array('type'=>'hidden', 'value'=>$products[$i]['MtProduct']['unit_price'] ));
	$id = $products[$i]['MtProduct']['id'];
	$val = $products[$i]['MtProduct']['unit_price'];
	echo "<input type=\"hidden\" name=\"$id\" value=\"$val\">";
	//var_dump($id.":".$val);
}
?>

	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">顧客詳細情報</h3><span style="float:right;margin-top:-20px"><button style="width:150px;cursor:pointer;" onclick="location.href='<?php echo $this->Url->build('/customers/edit/' . $customer['Customer']['id']);?>';" class="btn-warning btn-xs" >修　正</button></span>
		</div>
		<table class="table">
			<tbody>
				<tr>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">電話番号（「-」有）</td>
					<td width="10%"><?php echo h($customer['Customer']['tel']); ?></td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">電話番号（「-」無）</td>
					<td width="10%"><?php echo h($customer['Customer']['phone1']); ?></td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">携帯電話</td>
					<td width="10%"><?php echo h($customer['Customer']['mobile']); ?></td>
				</tr>
				<tr>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">名前</td>
					<td width="10%"><?php echo h($customer['Customer']['customer_name']); ?></td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">カナ</td>
					<td width="10%"><?php echo h($customer['Customer']['name_kana']); ?>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">性別</td>
					<td width="10%"><?php if(!empty($genderOpt[$customer['Customer']['gender']]) ) echo h($genderOpt[$customer['Customer']['gender']]); ?></td>
				</tr>
				<tr>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">年齢</td>
					<td width="10%"><?php echo h($customer['Customer']['age']); ?></td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">生年</td>
					<td width="10%"><?php echo h($customer['Customer']['dob']); ?></td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">年代</td>
					<td width="10%"><?php if(!empty($nendaiOpt[$customer['Customer']['nendai']]) ) echo h($nendaiOpt[$customer['Customer']['nendai']]); ?></td>
				</tr>
				<tr>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">郵便番号</td>
					<td width="10%"><?php echo h($customer['Customer']['zip']); ?></td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">住所1（県）</td>
					<td width="10%"><?php echo h($customer['Customer']['address1']); ?></td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">住所2（市区郡）</td>
					<td width="10%"><?php echo h($customer['Customer']['address2']); ?></td>
				</tr>
				<tr>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">住所3（町地番）</td>
					<td width="10%"><?php echo h($customer['Customer']['address3']); ?></td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">住所4（建物名）</td>
					<td width="10%"><?php echo h($customer['Customer']['address4']); ?></td>
				</tr>
			</tbody>
		</table>
	</div>

<?php if( $legacyAuth == 9 ){ ?>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">見込み一覧</h3><span style="float:right;margin-top:-20px"><?php //echo count($contacts); ?></span>
		</div>
		<table class="table table-striped table-hover ">
			<tbody>
				<tr>
					<td width="12%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">日付</td>
					<td width="5%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">曜日</td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">結果</td>
					<td width="20%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">リスト名</td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">AP担当者ID</td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">AP担当者名</td>
					<td width="28%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">商品名</td>

				</tr>
			</tbody>
			<?php foreach ($contacts as $row): ?>
			<?php
				if ($row['Contact']['kekka']<>1){
					continue;
				}
				//$bg_color = "#ffb6c1";
				$bg_color = "#ffffff";
			?>
			<?php
			//日付から曜日を計算
			$date = $row['Contact']['updated'];
			$datetime = new DateTime($date);
			$week = array("日", "月", "火", "水", "木", "金", "土");
			$w = (int)$datetime->format('w')
			?>
			<tbody>
				<tr onDblClick="location.href='<?php echo $this->Url->build('/'); ?>contacts/view/<?php echo $row['Contact']['id']; ?> '" style="background-color:<?php echo $bg_color; ?>;">
					<td style="vertical-align:middle;"><?php echo date ("Y-m-d H:i:s", strtotime($row['Contact']['updated'])); ?></td>
					<td style="vertical-align:middle;"><?php echo $week[$w];?></td>
					<td style="vertical-align:middle;"><?php if(!empty($dispositionAllOpt[$row['Contact']['kekka']]) ) echo h($dispositionAllOpt[$row['Contact']['kekka']]); ?></td>
					<td style="vertical-align:middle;"><?php echo h($row['Contact']['list_name']); ?></td>


					<td style="vertical-align:middle;"><?php echo h($row['Contact']['ap_id']); ?></td>
					<td style="vertical-align:middle;"><?php echo h($row['Contact']['ap_name']); ?></td>
					<td style="vertical-align:middle;"><?php if(!empty($productOpt[$row['Contact']['product_name1']]) ) echo h($productOpt[$row['Contact']['product_name1']]); ?></td>
				</tr>
			</tbody>
			<?php endforeach; ?>
		<?php //echo var_dump($contact);  ?>
		</table>
	</div>
<?php } ?>
<?php if( $legacyAuth == 9 ){ ?>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">受注一覧</h3><span style="float:right;margin-top:-20px"><?php //echo count($contacts); ?></span>
		</div>
		<table class="table table-striped table-hover ">
			<tbody>
				<tr>
					<td width="12%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">日付</td>
					<td width="5%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">曜日</td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">結果</td>
					<td width="20%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">リスト名</td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">AP担当者ID</td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">AP担当者名</td>
					<td width="28%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">商品名</td>

				</tr>
			</tbody>
			<?php foreach ($contacts as $row): ?>
			<?php
				if ($row['Contact']['kekka']<>6){
					continue;
				}
				//$bg_color = "#ffb6c1";
				$bg_color = "#ffffff";
			?>
			<?php
			//日付から曜日を計算
			$date = $row['Contact']['updated'];
			$datetime = new DateTime($date);
			$week = array("日", "月", "火", "水", "木", "金", "土");
			$w = (int)$datetime->format('w')
			?>
			<tbody>
				<tr onDblClick="location.href='<?php echo $this->Url->build('/'); ?>contacts/view/<?php echo $row['Contact']['id']; ?> '" style="background-color:<?php echo $bg_color; ?>;">
					<td style="vertical-align:middle;"><?php echo date ("Y-m-d H:i:s", strtotime($row['Contact']['updated'])); ?></td>
					<td style="vertical-align:middle;"><?php echo $week[$w];?></td>
					<td style="vertical-align:middle;"><?php if(!empty($dispositionAllOpt[$row['Contact']['kekka']]) ) echo h($dispositionAllOpt[$row['Contact']['kekka']]); ?></td>
					<td style="vertical-align:middle;"><?php echo h($row['Contact']['list_name']); ?></td>


					<td style="vertical-align:middle;"><?php echo h($row['Contact']['ap_id']); ?></td>
					<td style="vertical-align:middle;"><?php echo h($row['Contact']['ap_name']); ?></td>
					<td style="vertical-align:middle;"><?php if(!empty($productOpt[$row['Contact']['product_name1']]) ) echo h($productOpt[$row['Contact']['product_name1']]); ?></td>
				</tr>
			</tbody>
			<?php endforeach; ?>
		<?php //echo var_dump($contact);  ?>
		</table>
	</div>
<?php } ?>
<?php if( $legacyAuth == 9 ){ ?>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">先送り一覧</h3><span style="float:right;margin-top:-20px"><?php //echo count($contacts); ?></span>
		</div>
		<table class="table table-striped table-hover ">
			<tbody>
				<tr>
					<td width="12%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">日付</td>
					<td width="5%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">曜日</td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">結果</td>
					<td width="20%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">リスト名</td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">AP担当者ID</td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">AP担当者名</td>
					<td width="28%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">商品名</td>

				</tr>
			</tbody>
			<?php foreach ($contacts as $row): ?>
			<?php
				if ($row['Contact']['kekka']<>9){
					continue;
				}
				//$bg_color = "#ffb6c1";
				$bg_color = "#ffffff";
			?>
			<?php
			//日付から曜日を計算
			$date = $row['Contact']['updated'];
			$datetime = new DateTime($date);
			$week = array("日", "月", "火", "水", "木", "金", "土");
			$w = (int)$datetime->format('w')
			?>
			<tbody>
				<tr onDblClick="location.href='<?php echo $this->Url->build('/'); ?>contacts/view/<?php echo $row['Contact']['id']; ?> '" style="background-color:<?php echo $bg_color; ?>;">
					<td style="vertical-align:middle;"><?php echo date ("Y-m-d H:i:s", strtotime($row['Contact']['updated'])); ?></td>
					<td style="vertical-align:middle;"><?php echo $week[$w];?></td>
					<td style="vertical-align:middle;"><?php if(!empty($dispositionAllOpt[$row['Contact']['kekka']]) ) echo h($dispositionAllOpt[$row['Contact']['kekka']]); ?></td>
					<td style="vertical-align:middle;"><?php echo h($row['Contact']['list_name']); ?></td>


					<td style="vertical-align:middle;"><?php echo h($row['Contact']['ap_id']); ?></td>
					<td style="vertical-align:middle;"><?php echo h($row['Contact']['ap_name']); ?></td>
					<td style="vertical-align:middle;"><?php if(!empty($productOpt[$row['Contact']['product_name1']]) ) echo h($productOpt[$row['Contact']['product_name1']]); ?></td>
				</tr>
			</tbody>
			<?php endforeach; ?>
		<?php //echo var_dump($contact);  ?>
		</table>
	</div>
<?php } ?>
<?php if( $legacyAuth == 9 ){ ?>
	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">対応履歴新規登録</h3>
		</div>
		<?php echo $this->LegacyCrmForm->create('Contact', array('controller' => 'contacts','action' => 'add')); ?>
		<?php echo $this->LegacyCrmForm->input('customer_id',array('type'=>'hidden', 'value'=>$customer['Customer']['id'] )); ?>
		<table class="table">
			<tbody>
				<tr>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">結果</td>
					<td width="15%"><?php echo $this->LegacyCrmForm->input('kekka', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control if100', 'options' => $dispositionOpt, 'empty'=>'-----'   )); ?></td>




					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">リスト名</td>
					<?php if(empty($lead_name)){$lead_name = "";}?>
					<td width="40%"><?php echo $this->LegacyCrmForm->input('list_name', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100 if100','value'=>$lead_name, 'readonly'=>'readonly' )); ?></td>
					<?php //echo $this->LegacyCrmForm->input('list_name', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100 if100' )); ?>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">先送り日時</td>
					<td width="15%"><?php echo $this->LegacyCrmForm->input('sakiokuri_datetime', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100 if100 datetimepicker' ));;?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">AP担当者ID</td>
					<td><?php echo h($auth_loginid);?>
					<?php echo $this->LegacyCrmForm->input('ap_id',array('type'=>'hidden','value'=>$auth_loginid)); ?>
					</td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">AP担当者名</td>
					<td><?php echo h($auth_user_name);?>
					<?php echo $this->LegacyCrmForm->input('ap_name',array('type'=>'hidden','value'=>$auth_user_name)); ?>
					</td>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">AP受注日時</td>
					<td width="18%"><?php echo substr(date("Y/m/d H:i:s"),0,16) . "\n";?></td>
				</tr>
				<tr>
					<td colspan="7">
						<table class="tbl" border="1">
							<tr>
								<td colspan="6" bgcolor="#98fb98">受注票：</td>
							</tr>
							<tr>
								<td width="20%" align="center" bgcolor="#FFD700">商品名</td>
								<td width="10%" align="center" bgcolor="#FFD700">数量</td>
								<td width="10%" align="center" bgcolor="#FFD700">単価</td>
								<td width="10%" align="center" bgcolor="#FFD700">金額</td>
								<td width="25%" align="center" bgcolor="#87cefa">購入動機</td>
								<td width="25%" align="center" bgcolor="#87cefa">症状</td>
							</tr>
							<tr height="50px">
								<td>商品1：<?php echo $this->LegacyCrmForm->input('product_name1', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control product_name1', 'options' => $productOpt, 'empty'=>'-----'   )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('quantity1', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'4','onkeyDown' =>'return numOnly(event)', 'class'=>'form-control numericStyle quantity1' )); ?>個</td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('unit_price1', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15', 'class'=>'form-control numericStyle unit_price1', 'readonly'=>'readonly' )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('price1', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15','onkeyDown' =>'return numOnly(event)' , 'class'=>'form-control numericStyle price1' )); ?></td>
								<td><?php echo $this->LegacyCrmForm->input('reason', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control if100 if100', 'options' => $statusOpt, 'empty'=>'-----'   )); ?></td>
								<td rowspan="3" >
								<?php
								foreach ( $categoryOpt AS $key => $val ):
									echo '<label for="' . h("shoujou{$key}") . '">&nbsp;&nbsp;';
									echo $this->LegacyCrmForm->checkbox("shoujou.", array('value'=>$val), array('id'=>"shoujou{$key}", 'div'=>false));
									echo "&nbsp;&nbsp;".h($val)."&nbsp;&nbsp;&nbsp;&nbsp;</label>";
								endforeach;
								?>
								</td>
							</tr>
							<tr>
								<td>商品2：<?php echo $this->LegacyCrmForm->input('product_name2', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control product_name2', 'options' => $productOpt, 'empty'=>'-----'   )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('quantity2', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'4','onkeyDown' =>'return numOnly(event)', 'class'=>'form-control numericStyle quantity2' )); ?>個</td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('unit_price2', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15', 'class'=>'form-control numericStyle unit_price2', 'readonly'=>'readonly' )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('price2', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15','onkeyDown' =>'return numOnly(event)' , 'class'=>'form-control numericStyle price2' )); ?></td>
								<td>
									<table width="100%">
										<tr><td align="center" bgcolor="#87cefa">印象</td></tr>
									</table>
								</td>
							</tr>
							<tr>
								<td>商品3：<?php echo $this->LegacyCrmForm->input('product_name3', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control product_name3', 'options' => $productOpt, 'empty'=>'-----'   )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('quantity3', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'4','onkeyDown' =>'return numOnly(event)', 'class'=>'form-control numericStyle quantity3' )); ?>個</td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('unit_price3', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15', 'class'=>'form-control numericStyle unit_price3', 'readonly'=>'readonly' )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('price3', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15','onkeyDown' =>'return numOnly(event)' , 'class'=>'form-control numericStyle price3' )); ?></td>
								<td>
								<?php
								foreach ( $impOpt AS $key => $val ):
									echo '<label for="' . h("impression{$key}") . '">&nbsp;&nbsp;';
									echo $this->LegacyCrmForm->checkbox("impression.", array('value'=>$val), array('id'=>"impression{$key}", 'div'=>false));
									echo "&nbsp;&nbsp;".h($val)."&nbsp;&nbsp;&nbsp;&nbsp;</label>";
								endforeach;
								?>
								</td>
							</tr>
							<tr>
								<td>商品4：<?php echo $this->LegacyCrmForm->input('product_name4', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control product_name4', 'options' => $productOpt, 'empty'=>'-----'   )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('quantity4', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'4','onkeyDown' =>'return numOnly(event)', 'class'=>'form-control numericStyle quantity4' )); ?>個</td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('unit_price4', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15', 'class'=>'form-control numericStyle unit_price4', 'readonly'=>'readonly' )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('price4', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15','onkeyDown' =>'return numOnly(event)' , 'class'=>'form-control numericStyle price4' )); ?></td>
								<td colspan="2">
									<table width="100%">
										<tr><td align="center" bgcolor="#87cefa">状況</td></tr>
									</table>
								</td>
							</tr>
							<tr>
								<td>商品5：<?php echo $this->LegacyCrmForm->input('product_name5', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control product_name5', 'options' => $productOpt, 'empty'=>'-----'   )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('quantity5', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'4','onkeyDown' =>'return numOnly(event)', 'class'=>'form-control numericStyle quantity5' )); ?>個</td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('unit_price5', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15', 'class'=>'form-control numericStyle unit_price5', 'readonly'=>'readonly' )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('price5', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15','onkeyDown' =>'return numOnly(event)' , 'class'=>'form-control numericStyle price5' )); ?></td>
								<td colspan="2">
								<?php
								foreach ( $subcategoryOpt AS $key => $val ):
									echo '<label for="' . h("joukyou{$key}") . '">&nbsp;&nbsp;';
									echo $this->LegacyCrmForm->checkbox("joukyou.", array('value'=>$val), array('id'=>"joukyou{$key}", 'div'=>false));
									echo "&nbsp;&nbsp;".h($val)."&nbsp;&nbsp;&nbsp;&nbsp;</label>";
								endforeach;
								?>
								</td>
							</tr>
							<tr>
								<td colspan="3" align="center" bgcolor="#FFD700">購入金額合計</td>
								<td align="center">
								<?php echo $this->LegacyCrmForm->input('total_price', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15', 'class'=>'form-control numericStyle total_price', 'readonly'=>'readonly' )); ?>
								</td>
							</tr>
							<tr>
								<td align="center" bgcolor="#87cefa">デイサービス</td>
								<td colspan="2" align="center" bgcolor="#87cefa">家族環境</td>
								<td colspan="2"width="8%" align="center" bgcolor="#87cefa">服薬</td>
								<td align="center" bgcolor="#87cefa">サプリ</td>
							</tr>
							<tr>

								<td align="center">
									<div class='div_class'>
									<table class="tbl" border="0" align="left">
										<tr>
											<td>週<?php echo $this->LegacyCrmForm->input('day_service_times', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'3','class'=>'form-control day_service_times')); ?>回</td>
										</tr>
									</table>
										<table class="tbl" border="1">
											<tr>
												<th>月</th>
												<th>火</th>
												<th>水</th>
												<th>木</th>
												<th>金</th>
												<th><font color="blue">土</font></th>
												<th><font color="red">日</font></th>
											</tr>
											<tr>
												<td><?php echo $this->LegacyCrmForm->checkbox('day_service_days.', array('type' => 'select', 'id' => 'mon', 'value' => '月', 'checked' => '', 'div' => false, 'label' => false, 'hiddenField' => false));?></td>
												<td><?php echo $this->LegacyCrmForm->checkbox('day_service_days.', array('type' => 'select', 'id' => 'tue', 'value' => '火', 'checked' => '', 'div' => false, 'label' => false, 'hiddenField' => false));?></td>
												<td><?php echo $this->LegacyCrmForm->checkbox('day_service_days.', array('type' => 'select', 'id' => 'wed', 'value' => '水', 'checked' => '', 'div' => false, 'label' => false, 'hiddenField' => false));?></td>
												<td><?php echo $this->LegacyCrmForm->checkbox('day_service_days.', array('type' => 'select', 'id' => 'thu', 'value' => '木', 'checked' => '', 'div' => false, 'label' => false, 'hiddenField' => false));?></td>
												<td><?php echo $this->LegacyCrmForm->checkbox('day_service_days.', array('type' => 'select', 'id' => 'fri', 'value' => '金', 'checked' => '', 'div' => false, 'label' => false, 'hiddenField' => false));?></td>
												<td><?php echo $this->LegacyCrmForm->checkbox('day_service_days.', array('type' => 'select', 'id' => 'sat', 'value' => '土', 'checked' => '', 'div' => false, 'label' => false, 'hiddenField' => false));?></td>
												<td><?php echo $this->LegacyCrmForm->checkbox('day_service_days.', array('type' => 'select', 'id' => 'sun', 'value' => '日', 'checked' => '', 'div' => false, 'label' => false, 'hiddenField' => false));?></td>
											</tr>
										</table>
									</div>
								</td>
								<td colspan="2"><?php echo $this->LegacyCrmForm->input('kazoku_kankyou', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control if100 if100', 'options' => $famOpt, 'empty'=>'-----')); ?></td>
								<td colspan="2"><?php echo $this->LegacyCrmForm->input('fukuyaku', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100 if100' )); ?></td>
								<td><?php echo $this->LegacyCrmForm->input('supply', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100 if100' )); ?></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">備考</td>
					<td colspan="3"><?php echo $this->LegacyCrmForm->textarea("memo", array("cols"=>20, "rows"=>5, "class"=>"form-control if100", "style"=>"width:100%")); ?></td>
				</tr>
			</tbody>
		</table>
<?php } ?>

<?php if( $legacyAuth <> 9 ){ ?>



	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">対応履歴新規登録</h3>
		</div>
		<?php echo $this->LegacyCrmForm->create('Contact', array('controller' => 'contacts','action' => 'add')); ?>
		<?php echo $this->LegacyCrmForm->input('customer_id',array('type'=>'hidden', 'value'=>$customer['Customer']['id'] )); ?>
		<?php echo $this->LegacyCrmForm->input('lead_name',array('type'=>'hidden', 'value'=>$lead_name )); ?>
		<table class="table">
			<tbody>
				<tr>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">結果</td>
					<td width="18%"><?php echo $this->LegacyCrmForm->input('kekka', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control if100', 'options' => $OPdispOpt)); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">リスト名</td>
					<?php if(empty($lead_name)){$lead_name = "";}?>
					<td width="27%"><?php echo $this->LegacyCrmForm->input('list_name', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100 if100','value'=>$lead_name, 'readonly'=>'readonly' )); ?></td>
					<?php //echo $this->LegacyCrmForm->input('list_name', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100 if100' )); ?>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">先送り日時</td>
					<td width="15%"><?php echo $this->LegacyCrmForm->input('sakiokuri_datetime', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100 if100 datetimepicker' ));;?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">AP担当者ID</td>
					<td><?php echo h($auth_loginid);?>
					<?php echo $this->LegacyCrmForm->input('ap_id',array('type'=>'hidden','value'=>$auth_loginid)); ?>
					</td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">AP担当者名</td>
					<td><?php echo h($auth_user_name);?>
					<?php echo $this->LegacyCrmForm->input('ap_name',array('type'=>'hidden','value'=>$auth_user_name)); ?>
					</td>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">AP受注日時</td>
					<td width="18%"><?php echo substr(date("Y/m/d H:i:s"),0,16) . "\n";?></td>
				</tr>
				<tr>
					<td colspan="7">
						<table class="tbl" border="1" width="100%">
							<tr>
								<td colspan="6" bgcolor="#98fb98">受注票：</td>
							</tr>
							<tr>
								<td width="30%" align="center" bgcolor="#FFD700">商品名</td>
								<td width="10%" align="center" bgcolor="#FFD700">数量</td>
								<td width="15%" align="center" bgcolor="#FFD700">単価</td>
								<td width="15%" align="center" bgcolor="#FFD700">金額</td>
								<td width="15%"></td>
								<td width="15%"></td>
							</tr>
							<tr height="40px">
								<td><?php echo $this->LegacyCrmForm->input('product_name1', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control product_name1', 'options' => $productOpt, 'empty'=>'--商品を選択してください--'   )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('quantity1', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'4','onkeyDown' =>'return numOnly(event)', 'class'=>'form-control numericStyle quantity1' )); ?>個</td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('unit_price1', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15', 'class'=>'form-control numericStyle unit_price1', 'readonly'=>'readonly' )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('price1', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15','onkeyDown' =>'return numOnly(event)' , 'class'=>'form-control numericStyle price1' )); ?></td>
							</tr>
							<tr height="40px">
								<td><?php echo $this->LegacyCrmForm->input('product_name2', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control product_name2', 'options' => $productOpt, 'empty'=>'--商品を選択してください--'   )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('quantity2', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'4','onkeyDown' =>'return numOnly(event)', 'class'=>'form-control numericStyle quantity2' )); ?>個</td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('unit_price2', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15', 'class'=>'form-control numericStyle unit_price2', 'readonly'=>'readonly' )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('price2', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15','onkeyDown' =>'return numOnly(event)' , 'class'=>'form-control numericStyle price2' )); ?></td>
							</tr>
							<tr height="40px">
								<td><?php echo $this->LegacyCrmForm->input('product_name3', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control product_name3', 'options' => $productOpt, 'empty'=>'--商品を選択してください--'   )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('quantity3', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'4','onkeyDown' =>'return numOnly(event)', 'class'=>'form-control numericStyle quantity3' )); ?>個</td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('unit_price3', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15', 'class'=>'form-control numericStyle unit_price3', 'readonly'=>'readonly' )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('price3', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15','onkeyDown' =>'return numOnly(event)' , 'class'=>'form-control numericStyle price3' )); ?></td>
							</tr>
							<tr height="40px">
								<td><?php echo $this->LegacyCrmForm->input('product_name4', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control product_name4', 'options' => $productOpt, 'empty'=>'--商品を選択してください--'   )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('quantity4', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'4','onkeyDown' =>'return numOnly(event)', 'class'=>'form-control numericStyle quantity4' )); ?>個</td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('unit_price4', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15', 'class'=>'form-control numericStyle unit_price4', 'readonly'=>'readonly' )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('price4', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15','onkeyDown' =>'return numOnly(event)' , 'class'=>'form-control numericStyle price4' )); ?></td>
							</tr>
							<tr height="40px">
								<td><?php echo $this->LegacyCrmForm->input('product_name5', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control product_name5', 'options' => $productOpt, 'empty'=>'--商品を選択してください--'   )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('quantity5', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'4','onkeyDown' =>'return numOnly(event)', 'class'=>'form-control numericStyle quantity5' )); ?>個</td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('unit_price5', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15', 'class'=>'form-control numericStyle unit_price5', 'readonly'=>'readonly' )); ?></td>
								<td align="center"><?php echo $this->LegacyCrmForm->input('price5', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15','onkeyDown' =>'return numOnly(event)' , 'class'=>'form-control numericStyle price5' )); ?></td>
							</tr>
							<tr>
								<td colspan="3" align="center" bgcolor="#FFD700">購入金額合計</td>
								<td align="center">
								<?php echo $this->LegacyCrmForm->input('total_price', array('label'=>false, 'div'=>false, 'type' => 'text', 'size' =>'15', 'class'=>'form-control numericStyle total_price', 'readonly'=>'readonly' )); ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
<?php } ?>

		<div style="width:95%;margin-bottom:5px;margin-top:5px;" align="center" class="btn_margin">
			<?php echo $this->LegacyCrmForm->submit('登　録', array('div'=>false, 'name' => 'add', 'type'=>'submit', 'class'=>'btn-info btn-xs', 'style'=>'width:150px;height:30px;cursor:pointer')); ?>
			<?php //echo $this->LegacyCrmForm->submit('登　録', array('div'=>false, 'name' => 'add', 'type'=>'submit', 'class'=>'btn-info btn-xs', 'style'=>'width:150px;height:30px;cursor:pointer','onClick'=>'return confirm("対応履歴を登録します。よろしいですか？")')); ?>
		</div>
	</div>
	<?php echo $this->LegacyCrmForm->end();?>
</div>
