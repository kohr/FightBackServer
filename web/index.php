<?php

date_default_timezone_set('europe/paris');

use Silex\Application;
use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\JsonResponse;

//use Neoxygen\NeoClient\ClientBuilder;

use FightBack\Sqlite\DbHelper;

require __DIR__.'/../vendor/autoload.php';
$app = new Application();

$app->get('/', function (Request $request) {

  if ($request->get('url')) {
    /*
    $url = utf8_decode(preg_replace('/_(\w+)_(\w+).html$/', '_*_*.html', $request->get('url')));
    if (strstr($url, '.html')) {
      list($url, $args) = explode('.html', $url);
      $url .= '*';
    }
    //$url = 'www.lemonde.fr/societe/article/2015/01/18/michel-rocard-il-n-y-a-pas-lieu-de-remettre-en-cause-la-politique-d-immigration_*_*.html';
    //echo '$url:'.$url;
    $tweets = FightBack\Twitter\Api::searchUrl($url);
    $packager = new FightBack\Response\Formater($tweets);
    $data = $packager->run();
    */

    $sql = 'SELECT * FROM hit WHERE url LIKE "%'.$request->get('url').'%"';
    $db = new DbHelper(__DIR__.'/../');
    $data = $db->get($sql);
    
    //foreach ($data as $hit) {
    //  $data['tweet_oembed'] = FightBack\Twitter\Api::getOembed($hit->tweet_id);
    //}
    

    $response = new JsonResponse();
    $response->setData($data);
    return $response;
  }
});

$app->error(function (\Exception $e, $code) {
    return new Response('error: ', $e->getMessage());
});

$app->run();
