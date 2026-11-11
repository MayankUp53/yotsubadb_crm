<?php $this->Form->setTemplates(['submitContainer' => '{{content}}']); ?>
<?= $this->Html->css('legacy-settings', ['block' => true]) ?>
<div class="settings-page bs-docs-section clearfix">



	<div class="panel panel-primary filterable">
		<div class="panel-heading">
			<h3 class="panel-title">データ取込</h3>
		</div>
		<?php echo $this->Form->create(null, ['url' => ['controller' => 'Imports', 'action' => 'import'], 'type' => 'file', 'class' => 'legacy-settings-upload', 'data-mode' => 'import']); ?>
<table class="table table-hover ">
			<tbody>
				<tr>
					<th width="30%">顧客データ　xlsx</th>
					<td width="40%">
							<?php echo $this->Form->file('result',array('class' => 'importresult', 'id' => 'settings-import-result')); ?>
					</td>
					<td width="30%">
						<div id="indicator-import">
							<?php echo $this->Form->submit('取　込', array('div'=>false, 'name' => 'searching', 'type'=>'submit','class'=>'btn-success btn-xs indicator-import', 'style'=>'width:150px;height:30px;cursor:pointer' )); ?>
						</div>
					</td>
				</tr>
				</tbody>
		</table>
<?php echo $this->Form->end();?>
	</div>

	<div class="panel panel-primary filterable">
		<div class="panel-heading">
			<h3 class="panel-title">データ出力</h3>
		</div>
		<?php echo $this->Form->create(null, ['url' => ['controller' => 'Imports', 'action' => 'getphonedata'], 'type' => 'file', 'class' => 'legacy-settings-upload', 'data-mode' => 'getphonedata']); ?>
<table class="table table-hover ">
			<tbody>
				<tr>
					<th width="30%">電話番号リスト　xlsx</th>
					<td width="40%">
							<?php echo $this->Form->file('result',array('class' => 'exportresult', 'id' => 'settings-export-result')); ?>
					</td>
					<td width="30%">
						<div id="indicator-export">
								<?php echo $this->Form->submit('出　力', array('div'=>false, 'name' => 'searching', 'type'=>'submit','class'=>'btn-success btn-xs indicator-export', 'style'=>'width:150px;height:30px;cursor:pointer')); ?>
						</div>
					</td>
				</tr>
				</tbody>
		</table>
<?php echo $this->Form->end();?>
	</div>

	<div class="panel panel-primary filterable">
		<div class="panel-heading">
			<h3 class="panel-title">設定項目一覧</h3>
		</div>
		<table class="table table-hover ">
			<tbody>
				<tr>
					<th>商品一覧</th>
					<td width="30%"><button onclick="location.href='<?php echo $this->Url->build('/mt_products/index/');?>';" class="btn-info btn-xs" style='width:150px;height:30px;cursor:pointer'>一覧</button></td>
				</tr>
				<tr>
					<th>結果一覧</th>
					<td width="30%"><button onclick="location.href='<?php echo $this->Url->build('/mt_dispositions/index/');?>';" class="btn-info btn-xs" style='width:150px;height:30px;cursor:pointer'>一覧</button></td>
				</tr>
				<tr>
					<th>症状一覧</th>
					<td width="30%"><button onclick="location.href='<?php echo $this->Url->build('/mt_categories/index/');?>';" class="btn-info btn-xs" style='width:150px;height:30px;cursor:pointer'>一覧</button></td>
				</tr>
				<tr>
					<th>状況一覧</th>
					<td width="30%"><button onclick="location.href='<?php echo $this->Url->build('/mt_subcategories/index/');?>';" class="btn-info btn-xs" style='width:150px;height:30px;cursor:pointer'>一覧</button></td>
				</tr>
				<tr>
					<th>購入動機一覧</th>
					<td width="30%"><button onclick="location.href='<?php echo $this->Url->build('/mt_statuses/index/');?>';" class="btn-info btn-xs" style='width:150px;height:30px;cursor:pointer'>一覧</button></td>
				</tr>

			</tbody>
		</table>

	</div>

</div>
<script>
document.querySelectorAll('.legacy-settings-upload').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        const importing = form.dataset.mode === 'import';
        if (!form.querySelector('input[type="file"]').files.length) {
            window.alert(importing ? '顧客データを選択してください' : '電話番号リストを選択してください');
            event.preventDefault();
            return;
        }
        if (!window.confirm(importing ? '取込を開始します。よろしいですか？' : '出力を開始します。よろしいですか？')) event.preventDefault();
    });
});
</script>
