<?php

class Utils {

  static $emojiRegex = '([*#0-9](?>\\xEF\\xB8\\x8F)?\\xE2\\x83\\xA3|\\xC2[\\xA9\\xAE]|\\xE2..(\\xF0\\x9F\\x8F[\\xBB-\\xBF])?(?>\\xEF\\xB8\\x8F)?|\\xE3(?>\\x80[\\xB0\\xBD]|\\x8A[\\x97\\x99])(?>\\xEF\\xB8\\x8F)?|\\xF0\\x9F(?>[\\x80-\\x86].(?>\\xEF\\xB8\\x8F)?|\\x87.\\xF0\\x9F\\x87.|..(\\xF0\\x9F\\x8F[\\xBB-\\xBF])?|(((?<zwj>\\xE2\\x80\\x8D)\\xE2\\x9D\\xA4\\xEF\\xB8\\x8F\k<zwj>\\xF0\\x9F..(\k<zwj>\\xF0\\x9F\\x91.)?|(\\xE2\\x80\\x8D\\xF0\\x9F\\x91.){2,3}))?))';

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
    if (date("N", $ts) == 1) {
      return strtotime("last monday", strtotime("sunday this week", $ts));
    }
    return strtotime("last monday", $ts);
  }

  /**
   * @param string $str
   * @return int
   */
  static function nrEmojis($str) {
    return preg_match_all(Utils::$emojiRegex, $str);
  }
}
