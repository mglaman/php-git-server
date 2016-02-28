<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 2/27/16
 * Time: 11:33 PM
 */

namespace PhpGitServer;


use PhpGitServer\Model\Ref;

class Repository {
  protected $dir;
  protected $owner;
  protected $name;

  public function __construct($owner, $name, $base_dir) {
    $this->setOwner($owner);
    $this->setName($name);
    $this->setDir($base_dir);
  }

  /**
   * Get the repository's .git directory path.
   * @return string
   */
  public function getGitDir() {
    return $this->getDir() . '/.git';
  }

  /**
   * Get the full repository name.
   * @return string
   */
  public function getRepositoryName() {
    return $this->getOwner() . '/' . $this->getName();
  }

  /**
   * Read a file from the repository's .git directory.
   * @param $filename
   * @return null|string
   */
  public function readFile($filename) {
    $handle = fopen($this->getGitDir() . $filename, 'rb');

    if (!$handle) {
      return NULL;
    }

    $content = fread($handle, filesize($this->getGitDir() . $filename));
    fclose($handle);

    return $content;
  }


  public function getInfoRefs() {
    /** @var \PhpGitServer\Model\Ref[] $list */
    $list = array_merge($this->getLooseRefs(), $this->getPackedRefs());

    $output = [];
    foreach ($list as $ref) {
      $output[] = $ref->__toString();
    }

    return implode(PHP_EOL, $output);
  }

  public function getInfoPacks() {
    $packDir = $this->getGitDir() . '/objects/pack';
    $dir = dir($packDir);

    $output = [];

    while ($entry = $dir->read() !== false) {
      if (strpos($entry, '.idx') !== FALSE) {
        $name = substr($entry, 0, -4);
        if (is_file($packDir . '/' . $name . '.pack')) {
          $output[] = "P $name.pack";
        }
      }
    }

    return implode(PHP_EOL, $output);
  }

  /**
   * @return array|\PhpGitServer\Model\Ref[]
   */
  public function getLooseRefs() {
    return $this->getRefDir('refs');
  }

  /**
   * @return array|\PhpGitServer\Model\Ref[]
   */
  public function getPackedRefs() {
    $content = file($this->getGitDir() . '/packed-refs');
    $packedRefs = [];

    foreach ($content as $refLine) {
      if (strpos($refLine, '#') !== FALSE) continue;
      $packedRefs[] = Ref::createFromPackedRef($refLine);
    }

    return $packedRefs;
  }

  /**
   * @param $type
   * @param array $list
   * @return array|\PhpGitServer\Model\Ref[]
   */
  public function getRefDir($type, $list = []) {
    $dir = dir($this->getGitDir() . '/' . $type);

    while (($entry = $dir->read()) !== FALSE) {
      if ($entry[0] == '.' || strlen($entry) > 255 || strpos($entry, '.lock') !== FALSE || $entry == 'remotes')
        continue;

      $entry_path = $this->getGitDir() . '/' . $type . '/' .$entry;
      if (is_dir($entry_path)) {
        $list = $this->getRefDir($type . '/' . $entry, $list);
      } else {
        $list[] = Ref::createFromLooseRef($this->getGitDir(), $type . '/' .$entry);
      }
    }

    return $list;
  }

  /**
   * @return mixed
   */
  public function getDir() {
    return $this->dir;
  }

  /**
   * @param mixed $dir
   * @return Repository
   */
  public function setDir($dir) {
    $this->dir = $dir;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getOwner() {
    return $this->owner;
  }

  /**
   * @param mixed $owner
   * @return Repository
   */
  public function setOwner($owner) {
    $this->owner = $owner;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @param mixed $name
   * @return Repository
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }


}