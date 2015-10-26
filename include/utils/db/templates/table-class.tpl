/**
 * Class: <?=$tableClassName?>Table to work with table "<?=$tableName?>".
 * THIS CLASS WAS AUTOMATICALLY GENERATED. ALL MANUAL CHANGES WILL BE LOST!
 * PUT YOUR CODE TO CLASS "<?=$tableClassName?>" INSTEAD.
*/
class <?=$tableClassName?>Table extends AbstractTable {

    static $fields;
    static $tablename = '<?=$tableName?>';
    static $dbconfig = '<?=$dbConfigName?>';
    static $pk = array(<?=implode(', ', $pk)?>);
    static $generated;
    <?foreach($tableInfo as $field):?>

    /**
    * Field: <?=$tableName?>.<?=$field['Field']."\n"?>
    * @var <?=$field['Type'] . (is_null($field['Default']) ? '' : " (Default: '".addslashes($field['Default'])."')") . "\n"?>
    */
    public $<?=$field['Field'] . (is_null($field['Default']) ? '' : " = '".addslashes($field['Default'])."'")?>;
    <?endforeach; echo "\n"?>

<?foreach($methods as $method):?>
<?=$method?>
<?endforeach?>

}

<?=$tableClassName?>Table::$generated = array(
<?foreach($tableInfo as $field): if($field['Comment'] == 'uniqid'):?>
    '<?=$field['Field']?>' => 'uniqid',
<?endif;endforeach;?>
);
