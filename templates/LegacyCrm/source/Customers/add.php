<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">顧客情報新規登録</h3>
		</div>
		<?php echo $this->LegacyCrmForm->create('Customer',array('action' => 'add','class' => 'h-adr')); ?>
		<table class="table">
			<tbody>
				<tr>
					<td width="13%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">電話番号（「-」有）</td>
					<td width="12%"><?php echo $this->LegacyCrmForm->input('tel', array('label'=>false, 'div'=>false, 'required' => false, 'type' => 'text', 'class'=>'form-control', 'style'=>'width:100%;')); ?></td>
					<td width="13%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">電話番号（「-」無）</td>
					<td width="12%"><?php echo $this->LegacyCrmForm->input('phone1', array('label'=>false, 'div'=>false, 'required' => false, 'type' => 'text', 'class'=>'form-control', 'style'=>'width:100%;')); ?></td>
					<td width="13%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">携帯電話</td>
					<td width="12%"><?php echo $this->LegacyCrmForm->input('mobile', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control', 'style'=>'width:100%;')); ?></td>
					<td width="13%"></td>
					<td width="12%"></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">名前</td>
					<td><?php echo $this->LegacyCrmForm->input('customer_name', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control', 'style'=>'width:100%;')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">カナ</td>
					<td><?php echo $this->LegacyCrmForm->input('name_kana', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control', 'style'=>'width:100%;')); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">性別</td>
					<td><?php echo $this->LegacyCrmForm->input('gender', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control', 'style'=>'width:100%;', 'options' => $genderOpt, 'empty'=>'-----'   )); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">年齢</td>
					<td><?php echo $this->LegacyCrmForm->input('age', array('label'=>false, 'div'=>false, 'type' => 'text','onkeyDown' =>'return numOnly(event)', 'class'=>'form-control numericStyle age', 'style'=>'width:100%;')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">生年</td>
					<td><?php echo $this->LegacyCrmForm->input('dob', array('label'=>false, 'div'=>false, 'type' => 'text','onkeyDown' =>'return numOnly(event)', 'class'=>'form-control numericStyle dob', 'style'=>'width:100%;')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">年代</td>
					<td><?php echo $this->LegacyCrmForm->input('nendai', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control', 'style'=>'width:100%;', 'options' => $nendaiOpt, 'empty'=>'-----'   )); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">郵便番号</td>
					<span class="p-country-name" style="display:none;">Japan</span>
					<td><?php echo $this->LegacyCrmForm->input('zip', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control zip p-postal-code', 'style'=>'width:100%;')); ?></td>
					<td colspan="2"><input type="text" size="40" readonly="readonly" class="p-region p-locality p-street-address p-extended-address" /></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">住所1（県）</td>
					<td><?php echo $this->LegacyCrmForm->input('address1', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control address1', 'style'=>'width:100%;')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">住所2（市区郡）</td>
					<td><?php echo $this->LegacyCrmForm->input('address2', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control address2', 'style'=>'width:100%;')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">住所3（町地番）</td>
					<td><?php echo $this->LegacyCrmForm->input('address3', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control address3', 'style'=>'width:100%;')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">住所4（建物名）</td>
					<td><?php echo $this->LegacyCrmForm->input('address4', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control', 'style'=>'width:100%;')); ?></td>
				</tr>
			</tbody>
		</table>
		<div style="width:95%;margin-bottom:5px;margin-top:5px;" align="center" class="btn_margin">
			<?php echo $this->LegacyCrmForm->submit('戻　る', array('div'=>false, 'name' => 'back', 'type'=>'submit', 'style'=>'width:150px;height:30px;cursor:pointer', 'class'=>'btn-default btn-xs')); ?>
			<?php echo $this->LegacyCrmForm->submit('登　録', array('div'=>false, 'name' => 'add', 'type'=>'submit', 'style'=>'width:150px;height:30px;cursor:pointer', 'class'=>'btn-info btn-xs')); ?>
		</div>
		<?php echo $this->LegacyCrmForm->end(); ?>
	</div>
