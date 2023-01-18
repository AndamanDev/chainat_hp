<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class AutoNumber extends Component
{
  public $prefix = 1;

  public $number = 1;

  public $digit = 6;

  public function init()
  {
    parent::init();
  }

  public function generate()
  {
    if (empty($this->number) || $this->number === null) {
      return $this->prefix . sprintf("%'.0" . $this->digit . "d", 1);
    }
    if (is_numeric($this->number)) {
      $number = substr($this->number, strlen($this->prefix));
      return $this->prefix . sprintf("%'.0" . $this->digit . "d", $number + 1);
    } elseif (is_string($this->number)) {
      $prefix = substr($this->number, 0, strlen($this->prefix));
      $number = substr($this->number, strlen($this->prefix));
      $length = strlen($number);
      if (is_numeric($number)) {
        return $this->prefix . sprintf("%'.0" . ($length) . "d", ($number + 1));
      } else {
        return $this->prefix . sprintf("%'.0" . ($length) . "d", 1);
      }
    }
  }
}
