<?php

namespace FightBack\Response;

/**
* Format data for browsers' addons
*/
class Formater {

  private $tweets;

  public function __construct($tweets){
    $this->tweets = $tweets;
  }

  /**
  *
  */
  public function run(){
    return array(
      'timestamp' => time(),
      'twitter_response' => $this->tweets
    );
  }
}

?>