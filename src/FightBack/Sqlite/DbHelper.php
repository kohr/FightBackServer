<?php

namespace FightBack\Sqlite;

class DbHelper {
  protected $db;
  protected $dbpath = 'src/Fightback/Sqlite/fightback.db';
  protected $tables = array(
    'ennemy' => array(
      'INTEGER PRIMARY KEY' => 'id',
      'CHAR(155) NOT NULL' => 'name',
      'CHAR(255)' => 'url'
    ),
    'ally' => array(
      'INTEGER PRIMARY KEY' => 'id',
      'CHAR(155) NOT NULL' => 'name',
      'CHAR(255)' => 'url',
      'CHAR(155)' => 'twitter_screen_name'
    ),
    'hit' => array(
      'INTEGER PRIMARY KEY' => 'id',
      'INTEGER' => 'ally_id',
      'INTEGER NOT NULL' => 'ennemy_id',
      'STRING NOT NULL' => 'url',
      'CHAR(155) NOT NULL' => 'tweet_id'
    ),
  );

  public function __construct($dir) {
    if (!file_exists($dir.$this->dbpath)) {
      $this->init($dir);
    } else {
      //echo __METHOD__.': exists '.$dir.$this->dbpath."\n";
      $this->db = new \PDO('sqlite:'.$dir.$this->dbpath);
    }
  }
  
  protected function init($dir) {
    echo __METHOD__.': '.$dir.$this->dbpath."\n";
    $this->db = new \PDO('sqlite:'.$dir.$this->dbpath);
    //chmod($this->dbpath, 0777);
    
    foreach ($this->tables as $table => $fields) {
      $query_fields = array();
      foreach($fields as $type => $name) {
        $query_fields[] = $name.' '.$type;
      }
      $this->query("CREATE TABLE " . $table . " ( " . implode(', ', $query_fields) . " );");
    }

    $this->query( "INSERT INTO ally VALUES ('1','Acrimed','http://www.acrimed.org', 'acrimed_info');" );
    $this->query( "INSERT INTO ennemy VALUES ('1','Le Figaro','lefigaro.fr'), ('2','Le Monde','lemonde.fr'), ('3','Libération','liberation.fr');" );
    

    echo __METHOD__.' DONE !'."\n";
  }

  public function query($sql) {
    return $this->db->query($sql);
  }

  public function prepare($sql) {
    return $this->db->prepare($sql);
  }

  public function execute($r) {
    return $this->db->execute($r);
  }

  public function get($sql) {
    //echo $sql;
    $r = $this->db->prepare($sql);
    $r->execute();

    return $r->fetchAll(\PDO::FETCH_OBJ);
  }
}

?>