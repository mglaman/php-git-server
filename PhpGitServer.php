<?php

use PhpGitServer\Service\Config;
use Slim\App;
use Slim\Container;

class PhpGitServer {
  protected static $app;

  public function __construct() {

    self::$app = new App($this->createContainer());
    $this->addRoutes();
  }

  public function run() {
    return self::$app->run();
  }

  public static function service($name) {
    return self::$app->getContainer()->get($name);
  }

  /**
   * @return \Slim\Container
   */
  protected function createContainer() {
    $container = new Container();
    $container['config'] = new Config(__DIR__);
    return $container;
  }

  protected function addRoutes() {
    self::$app->get('/', function(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args) {
      $response->write(':)');
      return $response;
    });
    self::$app
      ->get('/{owner}/{repo}/HEAD', '\PhpGitServer\RouteController::getRepoHead');
    self::$app->get('/{owner}/{repo}/info/refs', '\PhpGitServer\RouteController::getInfoRefs');
    self::$app->get('/{owner}/{repo}/objects/info/alternates', '\PhpGitServer\RouteController::getObjectInfoAlternates');
    self::$app->get('/{owner}/{repo}/objects/info/http-alternates', '\PhpGitServer\RouteController::getObjectInfoHttpAlternates');
    self::$app->get('/{owner}/{repo}/objects/info/packs', '\PhpGitServer\RouteController::getInfoPacks');
    self::$app->get('/{owner}/{repo}/objects/{base}/{hash}', '\PhpGitServer\RouteController::getLooseObject');
    self::$app->get('/{owner}/{repo}/objects/pack/{pack:[0-9a-f]}.pack', '\PhpGitServer\RouteController::getPackFile');
    self::$app->get('/{owner}/{repo}/pack/{pack:[0-9a-f]}.idx', '\PhpGitServer\RouteController::getIdxFile');
  }
}