<table class="table settings-audit"><tbody>
    <tr><th scope="row">登録者</th><td><?= h($users[$record->created_user_id] ?? '—') ?></td><th scope="row">登録日時</th><td><?= $record->created ? h($record->created->format('Y/m/d H:i')) : '—' ?></td></tr>
    <tr><th scope="row">更新者</th><td><?= h($users[$record->updated_user_id] ?? '—') ?></td><th scope="row">更新日時</th><td><?= $record->updated ? h($record->updated->format('Y/m/d H:i')) : '—' ?></td></tr>
</tbody></table>
