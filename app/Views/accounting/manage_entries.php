<?= view('partial/header') ?>

<div id="table_holder">
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr>
                <th>Entry ID</th>
                <th>Date</th>
                <th>Journal</th>
                <th>Reference</th>
                <th>Description</th>
                <th>Created By</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($entries as $entry): ?>
            <tr>
                <td><?= esc($entry->entry_id) ?></td>
                <td><?= esc($entry->date) ?></td>
                <td><?= esc($entry->journal_name) ?></td>
                <td><?= esc($entry->ref) ?></td>
                <td><?= esc($entry->description) ?></td>
                <td><?= esc($entry->employee_name) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= view('partial/footer') ?>
