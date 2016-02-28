<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 2/28/16
 * Time: 12:28 AM
 */

namespace PhpGitServer;


use Slim\Http\Request;
use Slim\Http\Response;

class RouteController {
  static $repositories = [];

  /**
   * @param $args
   * @return \PhpGitServer\Repository
   */
  public static function getRepoFromArgs($args) {
    /** @var \PhpGitServer\Service\Config $config */
    $config = \PhpGitServer::service('config');
    $repo_dir = $config->getRepositoryDir();
    $repository = $config->getRepository($args['owner'], $args['repo']);
    return new Repository($args['owner'], $args['repo'], $repo_dir . '/' . $repository['dir']);
  }

  /**
   * @param \Slim\Http\Response $response
   * @return \Slim\Http\Response
   */
  public static function noCache(Response $response) {
    return $response
      ->withAddedHeader('Expires', 'Fri, 01 Jan 1980 00:00:00 GMT')
      ->withAddedHeader('Pragma', 'no-cache')
      ->withAddedHeader('Cache-Control', 'no-cache, max-age=0, must-revalidate');
  }

  /**
   * @param \Slim\Http\Response $response
   * @return \Slim\Http\Response
   */
  public static function cacheForever(Response $response) {
    return $response
      ->withAddedHeader('Expires', date('r', time() + 31536000))
      ->withAddedHeader('Cache-Control', 'public, max-age=31536000');
  }

  public static function finalizeResponse(Response $response, $content) {
    if ($content !== NULL) {
      $response->write($content);
    } else {
      $response->write('');
    }

    return $response;
  }

  public static function getRepoHead(Request $request, Response $response, array $args) {
    $actualResponse = self::noCache($response)->withAddedHeader('Content-Type', 'text/plain');
    $repository = self::getRepoFromArgs($args);

    return self::finalizeResponse($actualResponse, $repository->readFile('/HEAD'));
  }

  public static function getInfoRefs(Request $request, Response $response, array $args) {
    $actualResponse = self::noCache($response)->withAddedHeader('Content-Type', 'text/plain');
    $repository = self::getRepoFromArgs($args);
    return self::finalizeResponse($actualResponse, $repository->getInfoRefs());
  }

  public static function getObjectInfoAlternates(Request $request, Response $response, array $args) {
    $actualResponse = self::noCache($response)->withAddedHeader('Content-Type', 'text/plain');
    $repository = self::getRepoFromArgs($args);
    return self::finalizeResponse($actualResponse, $repository->readFile('/objects/info/alternates'));
  }

  public static function getObjectInfoHttpAlternates(Request $request, Response $response, array $args) {
    $actualResponse = self::noCache($response)->withAddedHeader('Content-Type', 'text/plain');
    $repository = self::getRepoFromArgs($args);
    return self::finalizeResponse($actualResponse, $repository->readFile('/objects/info/http-alternates'));
  }

  public static function getInfoPacks(Request $request, Response $response, array $args) {
    $actualResponse = self::noCache($response)->withAddedHeader('Content-Type', 'text/plain; charset=utf-8');
    $repository = self::getRepoFromArgs($args);
    return self::finalizeResponse($actualResponse, $repository->getInfoPacks());
  }

  public static function getLooseObject(Request $request, Response $response, array $args) {
    $actualResponse = self::cacheForever($response)->withAddedHeader('Content-Type', 'application/x-git-loose-object');
    $repository = self::getRepoFromArgs($args);
    return self::finalizeResponse($actualResponse, $repository->readFile('/objects/' . $args['base'] . '/' . $args['hash']));
  }

  public static function getPackFile(Request $request, Response $response, array $args) {
    $actualResponse = self::cacheForever($response)->withAddedHeader('Content-Type', 'application/x-git-packed-objects');
    $repository = self::getRepoFromArgs($args);
    return self::finalizeResponse($actualResponse, $repository->readFile('/objects/pack/' . $args['pack'] . '.pack'));
  }

  public static function getIdxFile(Request $request, Response $response, array $args) {
    $actualResponse = self::cacheForever($response)->withAddedHeader('Content-Type', 'application/x-git-packed-objects-toc');
    $repository = self::getRepoFromArgs($args);
    return self::finalizeResponse($actualResponse, $repository->readFile('/objects/pack/' . $args['pack'] . '.idx'));
  }
}