<?php 

/**
 * Class: LingopandaSubscribersTable to work with table "subscribers".
 * THIS CLASS WAS AUTOMATICALLY GENERATED. ALL MANUAL CHANGES WILL BE LOST!
 * PUT YOUR CODE TO CLASS "LingopandaSubscribers" INSTEAD.
*/
class LingopandaSubscribersTable extends AbstractTable {

    static $fields;
    static $tablename = 'subscribers';
    static $dbconfig = 'lingopanda';
    static $pk = array('id');
    static $generated;
    
    /**
    * Field: subscribers.id
    * @var int(10) unsigned
    */
    public $id;
    
    /**
    * Field: subscribers.email
    * @var varchar(50)
    */
    public $email;
    
    /**
    * Field: subscribers.firstname
    * @var varchar(50)
    */
    public $firstname;
    
    /**
    * Field: subscribers.lastname
    * @var varchar(50)
    */
    public $lastname;
    
    /**
    * Field: subscribers.created
    * @var datetime
    */
    public $created;
    


}

LingopandaSubscribersTable::$generated = array(
);
