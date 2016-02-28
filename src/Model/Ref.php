<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 2/28/16
 * Time: 8:32 AM
 */

namespace PhpGitServer\Model;

/**
 * Class Ref
 * @package PhpGitServer\Model
 */
class Ref {
  protected $sha;
  protected $ref;

  /**
   * Creates an instance from a single packed ref line.
   *
   * @param $refLine
   * @return \PhpGitServer\Model\Ref
   */
  public static function createFromPackedRef($refLine) {
    list($sha, $ref) = explode(' ', $refLine);
    return new self($sha, $ref);
  }

  public static function createFromLooseRef($gitDir, $refPath) {
    $depth = 5;

    while ($depth > 0) {
      $path = $gitDir . '/' . $refPath;
      if (!@lstat($path)) {
        return self::createEmptyRef();
      }
      if (is_link($path)) {
        $dest = readlink($path);
        if (strlen($dest) >= 5 && !strcmp('refs/', substr($dest, 0, 5))) {
          $refPath = $dest;
          continue;
        }
      }
      if (is_dir($path)) {
        return self::createEmptyRef();
      }

      $buffer = file_get_contents($path);
      if (!preg_match('~ref:\s*(.*)~', $buffer, $matches)) {
        if (strlen($buffer) < 40) {
          return self::createEmptyRef();
        }

        return new self(substr($buffer, 0, 40), $refPath);
      }

      $refPath = $matches[1];

      $depth -= 1;
    }

    return self::createEmptyRef();
  }

  public static function createEmptyRef() {
    return new self(NULL, '0000000000000000000000000000000000000000');
  }

  /**
   * Ref constructor.
   *
   * @param $sha
   * @param $ref
   */
  public function __construct($sha, $ref) {
    $this->sha = $sha;
    $this->ref = $ref;
  }

  /**
   * @return mixed
   */
  public function getSha() {
    return $this->sha;
  }

  /**
   * @return mixed
   */
  public function getRef() {
    return $this->ref;
  }

  /**
   * Returns ref for info/refs
   * @return string
   */
  public function __toString() {
    return trim("{$this->sha}\t{$this->ref}");
  }


}