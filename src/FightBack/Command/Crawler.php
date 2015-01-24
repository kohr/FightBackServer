<?php

namespace FightBack\Command;
use Knp\Command\Command as BaseCommand;

use Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\DomCrawler\Crawler as DOMCrawler;

use FightBack\Sqlite\DbHelper;

date_default_timezone_set('europe/paris');

class Crawler extends BaseCommand
{
  protected $db = null;
  protected $allies = null;
  protected $ennemies = null;

  protected function configure() {
    $this
      ->setName("fightback:crawler")
      ->setDescription("crawl!");
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $output->writeln('START');
    
    $this->db = new DbHelper($this->getProjectDirectory().'/');
    $this->allies = $this->db->get('SELECT * FROM ally');
    $this->ennemies = $this->db->get('SELECT * FROM ennemy');

    foreach ($this->allies as $ally) {
      if ($ally->twitter_screen_name) {
        $this->getTweets($ally);
      }
    }
  }

  protected function crawlSite($rss_url, $ally_id) {
    $rss = simplexml_load_file($rss_url);
    foreach ($rss->item as $item) {
      $this->crawlPage($item->link, $label);
    }
  }

  protected function crawlPage($page_url, $ally, $params = array('tweet_id' => null)) {
    echo __METHOD__.': '.$page_url.", ally_id:".$ally->id."\n";
    $page_url = strtok($page_url, '#');

    //if matches targets
    if (preg_match('/('.implode('|', $this->getEnnemiesDomains()).')/', $page_url)) {
      $ennemy_id = $this->getEnnemyIdByUrl($page_url);
      $exists = $this->db->get('SELECT * FROM hit WHERE ally_id = '.$ally->id.' AND url = "'.$page_url.'"');
      if (count($exists) == 0) {
        $sql = 'INSERT INTO hit (ally_id,ennemy_id,url,tweet_id) VALUES ("'.$ally->id.'", "'.$ennemy_id.'", "'.$page_url.'","'.$params['tweet_id'].'")';
        $this->db->query($sql);
        echo __METHOD__.': Hit direct '.$page_url."\n";
      } else {
        echo __METHOD__.': Hit direct exists '.$page_url."\n";
      }

    //if matches sites
    } else {

      $html = file_get_contents($page_url);
      $crawler = new DOMCrawler($html);

      $links = $crawler->filter('a')->each(function ($node, $i) use ($ally, $params) {
        $url = strtok($node->attr('href'), '#');
        $ennemy_id = $this->getEnnemyIdByUrl($url);
        if (preg_match('/('.implode('|', $this->getEnnemiesDomains()).')/', $url)) {
          $exists = $this->db->get('SELECT * FROM hit WHERE ally_id = '.$ally->id.' AND url = "'.$url.'"');
          if (count($exists) == 0) {
            $sql = 'INSERT INTO hit (ally_id,ennemy_id,url,tweet_id) VALUES ("'.$ally->id.'", "'.$ennemy_id.'", "'.$url.'","'.$params['tweet_id'].'")';
            $this->db->query($sql);
            echo __METHOD__.': Hit through ally '.$url."\n";
          } else {
            echo __METHOD__.': Hit through ally exists '.$url."\n";
          }
          $this->db->query($sql);
        }
      });
    }
  }
  
  protected function getEnnemyIdByUrl($url) {
    //echo __METHOD__.': $url -> '.$url."\n";
    foreach ($this->ennemies as $site) {
      if (preg_match('/'.$site->url.'/', $url)) {
        //echo __METHOD__.': FOUND -> '.$site->id."\n";
        return $site->id;
      }
    }
  }
  
  protected function getEnnemiesDomains() {
    $domains = array();
    foreach ($this->ennemies as $site) {
      $domains[] = $site->url;
    }
    return $domains;
  }
  

  protected function getTweets($site) {
    echo __METHOD__.': twitter_screen_name -> '.$site->twitter_screen_name."\n";
    $tweets = \FightBack\Twitter\Api::getTimeline($site->twitter_screen_name);
    foreach ($tweets as $tweet) {
      foreach ($tweet->entities->urls as $url) {
        $this->crawlPage($url->expanded_url, $site, array('tweet_id' => $tweet->id_str));
      }
    }
  }
}