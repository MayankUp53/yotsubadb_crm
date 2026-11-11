<div class="panel panel-primary">
		<div class="panel-heading">
			<h3 class="panel-title">顧客詳細情報</h3><span style="float:right;margin-top:-20px"></span>
		</div>
		<table class="table">
			<tbody>
				<tr>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">電話番号</td>
					<td width="10%"><?php echo h($customer['Customer']['tel']); ?></td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">物件名</td>
					<td width="25%"><?php echo h($customer['Customer']['article_name']); ?></td>
					<td width="10%" style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">物件住所</td>
					<td width="25%"><?php echo h($customer['Customer']['article_addr']); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#149c82">製品詳細</td>
					<td colspan="5" align="right" rowspan="2">
						<table width="100%" border="1">
							<tr>
								<td width="8%" align="center" bgcolor="#FFD700">種別</td>
								<td width="8%" align="center" bgcolor="#FFD700">リモニ</td>
								<td width="8%" align="center" bgcolor="#98fb98">SFV Ⅱ</td>
								<td width="8%" align="center" bgcolor="#98fb98">SFV Ⅰ</td>
								<td width="8%" align="center" bgcolor="#98fb98">CW</td>
								<td width="8%" align="center" bgcolor="#98fb98">CPL</td>
								<td width="8%" align="center" bgcolor="#98fb98">密結ﾀﾝｸ</td>
								<td width="8%" align="center" bgcolor="#87cefa">盤形式</td>
								<td width="8%" align="center" bgcolor="#87cefa">タンク</td>
								<td width="8%" align="center" bgcolor="#87cefa">SFV Ⅰ</td>
								<td width="8%" align="center" bgcolor="#87cefa">SFV Ⅱ</td>
								<td width="8%" align="center" bgcolor="#87cefa">製品</td>
							</tr>
							<tr>
								<td align="center"><?php echo h($customer['Customer']['p_detail1']); ?>&nbsp;</td>
								<td align="center"><?php echo h($customer['Customer']['p_detail2']); ?></td>
								<td align="center"><?php echo h($customer['Customer']['p_detail3']); ?></td>
								<td align="center"><?php echo h($customer['Customer']['p_detail4']); ?></td>
								<td align="center"><?php echo h($customer['Customer']['p_detail5']); ?></td>
								<td align="center"><?php echo h($customer['Customer']['p_detail6']); ?></td>
								<td align="center"><?php echo h($customer['Customer']['p_detail7']); ?></td>
								<td align="center"><?php echo h($customer['Customer']['p_detail8']); ?></td>
								<td align="center"><?php echo h($customer['Customer']['p_detail9']); ?></td>
								<td align="center"><?php echo h($customer['Customer']['p_detail10']); ?></td>
								<td align="center"><?php echo h($customer['Customer']['p_detail11']); ?></td>
								<td align="center"><?php echo h($customer['Customer']['p_detail12']); ?></td>
							</tr>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="panel panel-warning">
		<div class="panel-heading">
			<h3 class="panel-title">対応履歴更新</h3>
		</div>
	<?php echo $this->LegacyCrmForm->create('Contact',array('controller' => 'contact', 'action' => 'edit'.'/'.$contact['Contact']['id'])); ?>
	<?php echo $this->LegacyCrmForm->input('id',array('type'=>'hidden','value'=>$contact['Contact']['id']));?>
	<?php echo $this->LegacyCrmForm->input('customer_id',array('type'=>'hidden','value'=>$contact['Contact']['customer_id']));?>
		<table class="table">
			<tbody>
				<tr>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">受付番号</td>
					<td width="18%"><?php echo $this->LegacyCrmForm->input('receipt_number', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100','value'=>$contact['Contact']['receipt_number'])); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">履歴番号</td>
					<td><?php echo sprintf('%05d', $contact['Contact']['id']); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">問合せ社名</td>
					<td colspan="3"><?php echo $this->LegacyCrmForm->input('contact_company', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100','value'=>$contact['Contact']['contact_company'] )); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">担当者</td>
					<td><?php echo $this->LegacyCrmForm->input('contact_tantou', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100', 'value'=>$contact['Contact']['contact_tantou'] )); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">担当部署名</td>
					<td colspan="3"><?php echo $this->LegacyCrmForm->input('contact_dep', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100', 'value'=>$contact['Contact']['contact_dep'] )); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">担当連絡先</td>
					<td><?php echo $this->LegacyCrmForm->input('contact_tel', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100', 'value'=>$contact['Contact']['contact_tel'])); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">現場社名</td>
					<td colspan="3"><?php echo $this->LegacyCrmForm->input('site_company', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100', 'value'=>$contact['Contact']['site_company'] )); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">現場担当者</td>
					<td><?php echo $this->LegacyCrmForm->input('site_tantou', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100', 'value'=>$contact['Contact']['site_tantou'] )); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">現場部署名</td>
					<td colspan="3"><?php echo $this->LegacyCrmForm->input('site_dep', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100', 'value'=>$contact['Contact']['site_dep'] )); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">現場連絡先</td>
					<td><?php echo $this->LegacyCrmForm->input('site_tel', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100', 'value'=>$contact['Contact']['site_tel'])); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">設置箇所</td>
					<td colspan="3"><?php echo $this->LegacyCrmForm->input('location', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100', 'value'=>$contact['Contact']['location'] )); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">製品名</td>
					<td colspan="3"><?php echo $this->LegacyCrmForm->input('product_cd', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control if100', 'value'=>$contact['Contact']['product_cd'], 'options' => $productOpt, 'empty'=>'-----' )); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">チケットNo</td>
					<td><?php echo $this->LegacyCrmForm->input('ticket_no', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100', 'value'=>$contact['Contact']['ticket_no'])); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">手配先</td>
					<td><?php echo $this->LegacyCrmForm->input('destination', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100', 'value'=>$contact['Contact']['destination'])); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">希望日付</td>
					<td><?php echo $this->LegacyCrmForm->input('date_want', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100 datepicker', 'value'=>$contact['Contact']['date_want'])); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">希望日時</td>
					<td colspan="3"><?php echo $this->LegacyCrmForm->input('time_from', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control timepicker_from', 'value'=>$contact['Contact']['time_from'])); ?>～<?php echo $this->LegacyCrmForm->input('time_to', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control timepicker_to', 'value'=>$contact['Contact']['time_to'])); ?></td>
				</tr>
				<tr>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">処理区分</td>
					<td width="18%"><?php echo $this->LegacyCrmForm->input('category_cd', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control if100', 'value'=>$contact['Contact']['category_cd'], 'options' => $categoryOpt, 'empty'=>'-----')); ?></td>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">完了区分</td>
					<td width="18%"><?php echo $this->LegacyCrmForm->input('status_cd', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control if100','value'=>$contact['Contact']['status_cd'], 'options' => $statusOpt, 'empty'=>'-----')); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">対応区分</td>
					<td colspan="3"><?php echo $this->LegacyCrmForm->input('correspond_cd', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control if100', 'value'=>$contact['Contact']['correspond_cd'], 'options' => $correspondOpt, 'empty'=>'-----')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">取次先</td>
					<td><?php echo $this->LegacyCrmForm->input('agent_cd', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control if100', 'value'=>$contact['Contact']['agent_cd'], 'options' => $agentOpt, 'empty'=>'-----')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">HV</td>
					<td><?php echo $this->LegacyCrmForm->input('hv_div', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control if100', 'value'=>$contact['Contact']['hv_div'], 'options' => $hvOpt, 'empty'=>'-----')); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">問合せ内容</td>
					<td colspan="3"><?php echo $this->LegacyCrmForm->textarea("contact_detail", array("cols"=>20, "rows"=>5, "class"=>"form-control if100", 'value'=>$contact['Contact']['contact_detail'], 'style'=>'width:100%')); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">備考</td>
					<td colspan="3"><?php echo $this->LegacyCrmForm->textarea("remark", array("cols"=>20, "rows"=>5, "class"=>"form-control if100", 'value'=>$contact['Contact']['remark'], "style"=>"width:100%")); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">回答内容</td>
					<td colspan="3"><?php echo $this->LegacyCrmForm->textarea("answer_detail", array("cols"=>20, "rows"=>5, "class"=>"form-control if100", 'value'=>$contact['Contact']['answer_detail'], "style"=>"width:100%")); ?></td>
				</tr>
				<tr>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">契約区分</td>
					<td width="18%"><?php echo $this->LegacyCrmForm->input('contract_cd', array('label'=>false, 'div'=>false, 'type' => 'select', 'class'=>'form-control if100', 'value'=>$contact['Contact']['contract_cd'], 'options' => $contractOpt, 'empty'=>'-----')); ?></td>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">契約番号</td>
					<td width="18%"><?php echo $this->LegacyCrmForm->input('contract_no', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100', 'value'=>$contact['Contact']['contract_no'])); ?></td>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">複数契約</td>
					<td width="18%"><?php echo $this->LegacyCrmForm->input('contract_fukusu', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100', 'value'=>$contact['Contact']['contract_fukusu'])); ?></td>
					<td width="7%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">契約期間</td>
					<td width="18%"><?php echo $this->LegacyCrmForm->input('contract_priod', array('label'=>false, 'div'=>false, 'type' => 'text', 'class'=>'form-control if100', 'value'=>$contact['Contact']['contract_priod'])); ?></td>
				</tr>
				<tr>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">登録者</td>
					<td><?php if(!empty($row['Contact']['created_user_id']) ) echo h($opeOpt[$row['Contact']['created_user_id']]); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">登録日時</td>
					<td><?php echo h($contact['Contact']['created']); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">更新者</td>
					<td><?php if(!empty($row['Contact']['updated_user_id']) ) echo h($opeOpt[$row['Contact']['updated_user_id']]); ?></td>
					<td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">更新日時</td>
					<td><?php echo h($contact['Contact']['updated']); ?></td>
				</tr>
			</tbody>
		</table>

		<div style="width:95%;margin-bottom:5px;margin-top:5px;" align="center" class="btn_margin">
			<?php echo $this->LegacyCrmForm->submit('更　新', array('div'=>false, 'name' => 'edit', 'type'=>'submit', 'style'=>'width:150px;cursor:pointer', 'class'=>'btn btn-warning btn-xs')); ?>
		</div>
	<?php echo $this->LegacyCrmForm->end(); ?>
	</div>
