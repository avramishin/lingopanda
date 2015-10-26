Ext.define('SS.models.<?php echo getClassByTable($tableName)?>', {
    extend: 'Ext.data.Model',
    idProperty : <?php echo ($pk ? (count($pk) > 1 ? '[' . implode(', ', $pk) . ']' : $pk[0]) : 'null');?>,
    fields: [
<?php foreach($tableInfo as $i => $f):?>
    {
        name: '<?php echo $f['Field']?>',
        type: '<?php echo getJsType($f['Type'])?>',
<?php if(strpos($f['Type'], 'datetime') !== false || strpos($f['Type'], 'timestamp') !== false):?>
        dateFormat: 'Y-m-d H:i:s',
<?elseif(strpos($f['Type'], 'date') !== false):?>
        dateFormat: 'Y-m-d',
<?php endif;?>
<?php if($f['Null'] == 'YES'): ?>
        useNull: true,
<?php endif; ?>
        defaultValue: <?php echo $f["Default"] === null ? "null" : "'{$f['Default']}'"?>

    }<?php echo $i == count($tableInfo) - 1 ? '' : ','?>

<?endforeach;?>
    ],

    proxy: {
        type: 'rest',
        format: '',
        appendId : false,
        url : '../rest?class=<?php echo getClassByTable($tableName)?>',
        reader: {
            type: 'json',
            root: 'data',
            messageProperty: 'message'
        }
    }
});