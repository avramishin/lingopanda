    /**
    * Get linked record from table "<?=sprintf("%s", $method['foreignTable'])?>"
    * where <?=$method['foreignTable']?>.<?=$method['foreignField']?> = <?=$method['localTable']?>.<?=$method['localField']."\n"?>
    * @return <?=sprintf("%s\n", $method['foreignClass'])?>
    */
    public function <?=$method['name']?>(){
        return db('<?=$dbConfigName?>')->getObject('<?=$method['foreignTable']?>', '<?=$method['foreignField']?>', $this-><?=$method['localField']?>, '<?=$method['foreignClass']?>');
    }

