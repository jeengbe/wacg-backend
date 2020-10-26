<?php

class Utils {

  /**
   * @param array $datasets
   * @param string $label
   * @param string $borderColor
   * @param string $backgroundColor
   * @param (int|float)[] $data
   * @param array $options
   * @return self
   */
  static function addDataset(&$datasets, $label, $borderColor, $backgroundColor, $data, $options =  []) {
    $datasets[] = array_merge(
      [
        "label"             => $label,
        "borderColor"       => $borderColor,
        "backgroundColor"   => $backgroundColor,
        "data"              => array_values($data),
        "fill"              => false,
        "pointRadius"       => 2,
        "pointHoverRadius"  => 2,
      ],
      $options
    );
  }

  /**
   * @param int $ts
   * @return int
   */
  static function getMonday($ts) {
    return strtotime("last monday", $ts);
  }
}
