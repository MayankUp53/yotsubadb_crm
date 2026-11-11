<div class="panel panel-primary filterable">
		<div class="panel-heading">
			<h3 class="panel-title">購入動機一覧</h3>
		</div>
		<br />
		<?= $this->Form->create(null, ['url' => ['action' => 'sortupdate'], 'id' => 'master-sort-form']) ?>
<?= $this->Form->hidden('ids', ['id' => 'master-sort-ids']) ?>
<ul id="jquery-ui-sortable">
			<?php foreach ($mtStatuses as $row): ?>

				<?php if( $row['MtStatus']['active_flg'] == 1 ){ ?>
					<li class="ui-state-default">
					<span><?php echo h($row['MtStatus']['id']); ?></span>&nbsp;&nbsp;&nbsp;<?php echo h($row['MtStatus']['item_name']); ?>&nbsp;&nbsp;&nbsp;(<?php echo h($activeOpt[$row['MtStatus']['active_flg']]); ?>)
					<?php if ($canWrite): ?><button type="button" onclick="location.href='<?php echo $this->Url->build('/mt_statuses/edit/'. $row['MtStatus']['id'] );?>';" class="btn-success btn-xs" style="float:right;margin-top:-2px;">編集</button><?php endif ?>
					</li>
				<?php }else{ ?>
					<li style="background-color:#dcdcdc;" >
					<span><?php echo h($row['MtStatus']['id']); ?></span>&nbsp;&nbsp;&nbsp;<?php echo h($row['MtStatus']['item_name']); ?>&nbsp;&nbsp;&nbsp;(<?php echo h($activeOpt[$row['MtStatus']['active_flg']]); ?>)
					<?php if ($canWrite): ?><button type="button" onclick="location.href='<?php echo $this->Url->build('/mt_statuses/edit/'. $row['MtStatus']['id'] );?>';" class="btn-success btn-xs" style="float:right;margin-top:-2px;">編集</button><?php endif ?>
					</li>
				<?php } ?>
			<?php endforeach; ?>
		</ul>
		<?php if ($canWrite): ?><div style="width:50%;" align="center" class="btn_margin">
			<button type="button" onclick="location.href='<?php echo $this->Url->build('/mt_statuses/add/');?>';" class="btn-info btn-xs" style="width:150px;height:30px;cursor:pointer">新規登録</button>
			<input type="submit" id="submitSortable" value="表示順序を変更" class="btn-default btn-xs" style="width:150px;height:30px;">
		</div>
	<?php endif ?><?= $this->Form->end() ?>
</div>
<?php if ($canWrite): ?>
<script>
(function () {
    const list = document.getElementById('jquery-ui-sortable');
    let dragging = null;
    list.querySelectorAll('li').forEach(function (row) { row.draggable = true; });
    list.addEventListener('dragstart', function (event) {
        dragging = event.target.closest('li');
        if (dragging) event.dataTransfer.setData('text/plain', dragging.querySelector('span').textContent.trim());
    });
    list.addEventListener('dragover', function (event) {
        const row = event.target.closest('li');
        if (!dragging || !row || row === dragging) return;
        event.preventDefault();
        const middle = row.getBoundingClientRect().top + row.offsetHeight / 2;
        list.insertBefore(dragging, event.clientY < middle ? row : row.nextSibling);
    });
    list.addEventListener('drop', function (event) { event.preventDefault(); dragging = null; });
    list.addEventListener('dragend', function () { dragging = null; });
    document.getElementById('master-sort-form').addEventListener('submit', function (event) {
        if (!window.confirm('この順番で更新します。よろしいですか？')) { event.preventDefault(); return; }
        document.getElementById('master-sort-ids').value = Array.from(list.children).map(row => row.querySelector('span').textContent.trim()).join('-');
    });
})();
</script>
<?php endif ?>
