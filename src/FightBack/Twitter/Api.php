<?php

namespace FightBack\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;

/**
* handle Twitter API search request
*/
class API {

  /**
  * load twitter API
  */
  public static function load(){
		$token_credentials = array(
			'consumer_key' => 'JSuauKd6CCPrURhSXn3hWQ',
			'consumer_secret' => 'nfY0OE20cEbyx54e83e72dTh2zFqPd3sUaS0k00IP0',
			'oauth_token' => '944336262-Xvw2ooHGK6CqipizpH1tgW5xBX6TNqUDChRsRHby',
			'oauth_token_secret' => '3kPZjKJhcHUY3Dmb3qbTApXGgL9GTR6jqFnjdBvIRc'
		);
		try {
			$connection = new TwitterOAuth(
				$token_credentials['consumer_key'],
				$token_credentials['consumer_secret'],
				$token_credentials['oauth_token'],
				$token_credentials['oauth_token_secret']
      );
			$connection->host = 'https://api.twitter.com/1.1/';
			//print_r($content);
      return $connection;
		} catch (\Exception $ex) {
			throw new \Exception($ex);
		}
	}

  /**
  * search
  */
  public static function searchUrl($url){
    $q = self::getKeywords($url);
    $twitter = self::load();
    $tweets = $twitter->get('search/tweets', array(
  		'q' => $q,
  		'include_entities' => true,
  		'count' => 100
    ));

    $logger = new \Katzgrau\KLogger\Logger(__DIR__.'/../../../app/logs');
    $logger->info('searchUrl url:'.$url.' -> '.count($tweets->statuses).' tweets found');
    //$logger->error('Oh dear.');
    //$logger->debug('Got these users from the Database.', $users);
    
    return $tweets;
  }

  /**
  * search
  */
  public static function getOembed($tweet_id){
    $twitter = self::load();
    return $twitter->get('statuses/oembed', array(
  		'url' => 'https://twitter.com/acrimed_info/status/'.$tweet_id
    ));
  }

  /**
  * user's feed
  */
  public static function getTimeline($account){
    echo __METHOD__.': '.$account."\n";
    $twitter = self::load();
    $tweets = $twitter->get('statuses/user_timeline', array(
  		'screen_name' => $account,
  		'count' => 100
    ));

    $logger = new \Katzgrau\KLogger\Logger(__DIR__.'/../../../app/logs');
    $logger->info('getFeed @'.$account.' -> '.count($tweets->statuses).' tweets found');
    
    return $tweets;
  }

  /**
  *
  */
  public static function getKeywords($url){
    return urlencode($url);
    return '#fightback ' . urlencode($url);
  }
}

?>