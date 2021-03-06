<?php
namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HomeController extends Controller {

  public function home (RequestInterface $request, ResponseInterface $response) {
    $fb = new \Facebook\Facebook([
      'app_id' => '2157232504601426',
      'app_secret' => 'a41e09cd3d8ec4e3adc4154658b8cee6',
      'default_graph_version' => 'v3.0',
    ]);
    try {
      $resNews = $fb->get('1618411431756379?fields=feed.limit(4){message,created_time,full_picture,link}', 'EAAepZCdUie1IBAJuhZAOnhScQ0KmqMZBPHOBoPuWyoPulh9vIexhXEDS71UwmCvxk6UDKMMmZBMbFZAYs5MOcbAtb4hHpUKYgsRt2pezWiaoaZBcsgfMfMLC5kTlFn2KSaJNeHIsDLGc2ozfDy9cvuGt6lmHFxFpgZD');
      $resEventTemp = $fb->get('1618411431756379?fields=events', 'EAAepZCdUie1IBAJuhZAOnhScQ0KmqMZBPHOBoPuWyoPulh9vIexhXEDS71UwmCvxk6UDKMMmZBMbFZAYs5MOcbAtb4hHpUKYgsRt2pezWiaoaZBcsgfMfMLC5kTlFn2KSaJNeHIsDLGc2ozfDy9cvuGt6lmHFxFpgZD');
    } catch(\Facebook\Exceptions\FacebookResponseException $e) {
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(\Facebook\Exceptions\FacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }
    $resNews = $resNews->getGraphNode();
    $resEventTemp = $resEventTemp->getGraphNode();
    $resNews = json_decode($resNews['feed'], true);
    $resEventTemp = json_decode($resEventTemp['events'], true);
    $resEvent = array();
    foreach ($resEventTemp as $resTemp) {
      date_default_timezone_set('Asia/Bangkok');
      $dateNow = date('Y-m-d H:i:s');
      $dateTemp = substr($resTemp['start_time']['date'], 0, 19);
      if ($dateTemp > $dateNow) {
        array_push($resEvent, $resTemp);
      }
    }
    $query =  $this->db->table('cisw_profile_tbl')
                        ->join('cisw_comment_tbl', 'cisw_comment_tbl.user_id', '=', 'cisw_profile_tbl.google_user_id')
                        ->where('cisw_comment_tbl.comment_text', '!=', '')
                        ->where('cisw_profile_tbl.work_position', '!=', '')
                        ->where('cisw_profile_tbl.public_profile', '=', '1')
                        ->orderByRaw("RAND()")
                        ->limit(5)
                        ->get();
    $comment = json_decode($query, true);
    if (isset($_SESSION['uid'])) {
      $teacher =  $this->db->table('cisw_profile_tbl')->where('google_user_id', '=', $_SESSION['uid'])->pluck('email');
      if (strpos($teacher[0], '@kku.ac.th')) {
        $this->view->render($response, 'pages/Home.twig', [
          'resNews' => $resNews,
          'resEvent' => $resEvent,
          'resComment' => $comment,
          'teacher' => $teacher,
          'current' => 'home',
          'signin' => (isset($_SESSION["uid"]) ? $_SESSION["uid"] : false)
        ]);
      } else {
        $this->view->render($response, 'pages/Home.twig', [
          'resNews' => $resNews,
          'resEvent' => $resEvent,
          'resComment' => $comment,
          'current' => 'home',
          'signin' => (isset($_SESSION["uid"]) ? $_SESSION["uid"] : false)
        ]);
      }
    } else {
      $this->view->render($response, 'pages/Home.twig', [
        'resNews' => $resNews,
        'resEvent' => $resEvent,
        'resComment' => $comment,
        'current' => 'home',
        'signin' => (isset($_SESSION["uid"]) ? $_SESSION["uid"] : false)
      ]);
    }
  }

  public function news (RequestInterface $request, ResponseInterface $response) {
    $fb = new \Facebook\Facebook([
      'app_id' => '2157232504601426',
      'app_secret' => 'a41e09cd3d8ec4e3adc4154658b8cee6',
      'default_graph_version' => 'v3.0',
    ]);
    try {
      $resNews = $fb->get('1618411431756379?fields=feed{message,created_time,full_picture,link}', 'EAAepZCdUie1IBAJuhZAOnhScQ0KmqMZBPHOBoPuWyoPulh9vIexhXEDS71UwmCvxk6UDKMMmZBMbFZAYs5MOcbAtb4hHpUKYgsRt2pezWiaoaZBcsgfMfMLC5kTlFn2KSaJNeHIsDLGc2ozfDy9cvuGt6lmHFxFpgZD');
    } catch(\Facebook\Exceptions\FacebookResponseException $e) {
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(\Facebook\Exceptions\FacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }
    $resNews = $resNews->getGraphNode();
    $resNews = json_decode($resNews['feed'], true);
    if (isset($_SESSION['uid'])) {
      $teacher =  $this->db->table('cisw_profile_tbl')->where('google_user_id', '=', $_SESSION['uid'])->pluck('email');
      if (strpos($teacher[0], '@kku.ac.th')) {
        $this->view->render($response, 'pages/News.twig', [
          'resNews' => $resNews,
          'current' => 'news',
          'teacher' => $teacher,
          'signin' => (isset($_SESSION["uid"]) ? $_SESSION["uid"] : false)
        ]);
      } else {
        $this->view->render($response, 'pages/News.twig', [
          'resNews' => $resNews,
          'current' => 'news',
          'signin' => (isset($_SESSION["uid"]) ? $_SESSION["uid"] : false)
        ]);
      }
    } else {
      $this->view->render($response, 'pages/News.twig', [
        'resNews' => $resNews,
        'current' => 'news',
        'signin' => (isset($_SESSION["uid"]) ? $_SESSION["uid"] : false)
      ]);
    }
  }

  public function event (RequestInterface $request, ResponseInterface $response) {
    $fb = new \Facebook\Facebook([
      'app_id' => '2157232504601426',
      'app_secret' => 'a41e09cd3d8ec4e3adc4154658b8cee6',
      'default_graph_version' => 'v3.0',
    ]);
    try {
      $resEvent = $fb->get('1618411431756379?fields=events', 'EAAepZCdUie1IBAJuhZAOnhScQ0KmqMZBPHOBoPuWyoPulh9vIexhXEDS71UwmCvxk6UDKMMmZBMbFZAYs5MOcbAtb4hHpUKYgsRt2pezWiaoaZBcsgfMfMLC5kTlFn2KSaJNeHIsDLGc2ozfDy9cvuGt6lmHFxFpgZD');
    } catch(\Facebook\Exceptions\FacebookResponseException $e) {
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(\Facebook\Exceptions\FacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }
    $resEvent = $resEvent->getGraphNode();
    $resEvent = json_decode($resEvent['events'], true);
    if (isset($_SESSION['uid'])) {
      $teacher =  $this->db->table('cisw_profile_tbl')->where('google_user_id', '=', $_SESSION['uid'])->pluck('email');
      if (strpos($teacher[0], '@kku.ac.th')) {
        $this->view->render($response, 'pages/Event.twig', [
          'resEvent' => $resEvent,
          'current' => 'event',
          'teacher' => $teacher,
          'signin' => (isset($_SESSION["uid"]) ? $_SESSION["uid"] : false)
        ]);
      } else {
        $this->view->render($response, 'pages/Event.twig', [
          'resEvent' => $resEvent,
          'current' => 'event',
          'signin' => (isset($_SESSION["uid"]) ? $_SESSION["uid"] : false)
        ]);
      }
    } else {
      $this->view->render($response, 'pages/Event.twig', [
        'resEvent' => $resEvent,
        'current' => 'event',
        'signin' => (isset($_SESSION["uid"]) ? $_SESSION["uid"] : false)
      ]);
    }
  }
}