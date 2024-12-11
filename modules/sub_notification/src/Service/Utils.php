<?php

namespace Drupal\sub_notification\Service;

/**
 * Class Utils
 * @package Drupal\sub_notification\Service
 */
class Utils {

  public function cloneEntity(
    $src, $dst, 
    $src_module, $des_module,
    $src_url, $des_url,
    $src_class, $des_class,
    $src_lower_text, $des_lower_text,
    $src_upper_text, $des_upper_text,
    $src_package, $des_package,
    $ignore_files
  ) {
    $dir = opendir($src);
    @mkdir($dst);

    // print_r($dst);
    // print_r(111111);
    // exit;
    while ($file = readdir($dir)) {
      if (($file != '.') && ($file != '..')) {
        if (is_dir($src . '/' . $file)) {
          $this->cloneEntity($src . '/' . $file, $dst . '/' . $file,
            $src_module, $des_module,
            $src_url, $des_url,
            $src_class, $des_class,
            $src_lower_text, $des_lower_text,
            $src_upper_text, $des_upper_text,
            $src_package, $des_package,
            $ignore_files);
        }
        else {
          if(!in_array($file, $ignore_files)) {
            $str = $file;
            $str = str_replace($src_module, $des_module, $str);
            $str = str_replace($src_url, $des_url, $str);
            $str = str_replace($src_class, $des_class, $str);
            copy($src . '/' . $file, $dst . '/' . $str);
            $this->replaceInFile($dst . '/' . $str, $src_module, $des_module);
            $this->replaceInFile($dst . '/' . $str, $src_url, $des_url);
            $this->replaceInFile($dst . '/' . $str, $src_class, $des_class);
            $this->replaceInFile($dst . '/' . $str, $src_lower_text, $des_lower_text);
            $this->replaceInFile($dst . '/' . $str, $src_upper_text, $des_upper_text);
            $this->replaceInFile($dst . '/' . $str, $src_package, $des_package);
          }
        }
      }
    }
    closedir($dir);
  }

  /**
   * @param $path
   * @param $src_str
   * @param $des_str
   */
  public function replaceInFile($path, $src_str, $des_str) {
    $content = file_get_contents($path);
    $content = str_replace($src_str, $des_str, $content);
    file_put_contents($path, $content);
  }
}
