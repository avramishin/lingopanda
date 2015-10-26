<?php

require_once sprintf("%s/../../../bootstrap.php", __DIR__);

$extJsModelsDir = sprintf('%s/desktop/app/models', ROOT);

$dbConfigName = 'default';
$classPrefix = '';

if (@$argv[1]) {
    $dbConfigName = strtolower($argv[1]);
    $classPrefix = str_replace(' ', '', ucwords(str_replace('_', ' ', $dbConfigName)));
    echo "Using database config $dbConfigName, class prefix $classPrefix\n";
}

if (empty(cfg()->db->$dbConfigName)) {
    exit("Config $dbConfigName not found\n");
}

$dbConfig = cfg()->db->$dbConfigName;

$tables = db($dbConfigName)->query("SHOW TABLES FROM `{$dbConfig->name}`")->fetchAllArray();
foreach($tables as $tc) {
    $tableName = $tc[0];
    echo "Working on {$tableName}\n";
    $tableInfo = db($dbConfigName)->query("SHOW FULL COLUMNS FROM {$tableName}")->fetchAllAssoc();
    $tableClassName = getClassByTable($tableName);


    $pk = array();
    $methods = array();
    $indexes = array();
    foreach($tableInfo as $k => $tableFields){
        if($tableFields['Key'] == 'PRI'){
            $pk[] = sprintf("'%s'", $tableFields['Field']);
        }

        if(!empty($tableFields['Default']) && $tableFields['Default'] == "''"){
            $tableInfo[$k]['Default'] = "";
        }

        $comment = trim($tableFields['Comment']);
        if(preg_match('#^(.+)\((.+)\.(.+)\)$#', $comment, $m)){
            print_r($m);
            ob_start();
            $method = array(
                'foreignClass' => getClassByTable($m[2]),
                'foreignTable' => $m[2],
                'foreignField' => $m[3],
                'name' => $m[1],
                'localTable' => $tableName,
                'localField' => $tableFields['Field']
            );

            require sprintf("%s/templates/table-method-get-one.tpl", __DIR__);
            $methods[] = ob_get_contents();
            ob_end_clean();
        }
    }


    ob_start();
    require sprintf("%s/templates/table-class.tpl", __DIR__);
    $classContent = sprintf("<?php \n\n%s", ob_get_contents());
    file_put_contents(sprintf("%s/classes/tables/%sTable.php", INCLUDE_ROOT, getClassByTable($tableName)), $classContent);
    ob_end_clean();
    $className = getClassByTable($tableName);
    if (preg_match('/^([A-Z][a-z]*)\w+/', $className, $m)) {
        $prefix = strtolower($m[1]);
        $entityClass = sprintf("%s/classes/%s/%s.php", INCLUDE_ROOT, $prefix, $className);
        $dirname = dirname($entityClass);
        if (!file_exists($dirname)) {
            mkdir($dirname, 0755, true);
        }
    } else {
        $entityClass = sprintf("%s/classes/%s.php", INCLUDE_ROOT, $className);
    }
    if(!file_exists($entityClass)){
        ob_start();
        require sprintf("%s/templates/entity-class.tpl", __DIR__);
        $entityClassContent = sprintf("<?php \n\n%s", ob_get_contents());
        file_put_contents($entityClass, $entityClassContent);
        ob_end_clean();
    }

    /*ob_start();
    require sprintf("%s/templates/extjs-model.tpl", __DIR__);
    $modelContent = ob_get_contents();

    file_put_contents(sprintf('%s/%s.js', $extJsModelsDir, getClassByTable($tableName)), $modelContent);
    ob_end_clean();*/

}

function getJsType($type){

    $types = array(
        'bigint' => 'string',
        'int' => 'int',
        'float' => 'float',
        'datetime' => 'date',
        'date' => 'date',
        'timestamp' => 'date'
    );

    foreach($types as $dbType => $jsType){
        if(strpos($type, $dbType) !== false){
            return $jsType;
        }
    }

    return 'string';
}

function getClassByTable($table){
    global $classPrefix;
    $parts = explode('_', $table);
    foreach($parts as $key => $value){
        $parts[$key] = ucfirst($value);
    }
    return $classPrefix . join('', $parts);
}