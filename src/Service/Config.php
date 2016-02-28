<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 2/28/16
 * Time: 10:29 AM
 */

namespace PhpGitServer\Service;


use Symfony\Component\Yaml\Yaml;

class Config {
  const FILE_NAME = 'config.yml';

  protected $config = [];

  public function __construct($rootDir) {
    $this->config = Yaml::parse(file_get_contents($rootDir . '/' . self::FILE_NAME));
  }

  public function getRepositoryDir() {
    return $this->config['repository_directory'];
  }

  public function getRespositories() {
    return $this->config['repositories'];
  }

  public function getRepository($owner, $name) {
    return $this->config['repositories']["$owner/$name"];
  }

}