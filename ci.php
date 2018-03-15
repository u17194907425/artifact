<?php

global $exit_code;
$exit_code = 0;


$test = function($f_name) {
  var_dump($f_name);
};

$test_card_format = function($f_name) {
  global $exit_code;

  $content = file_get_contents($f_name);
  static $pattern = "#{{ARTIFACT CARD}}\n(?P<img>.*?)\n---+\n(?P<descr>.*?)\n{{/ARTIFACT CARD}}\n#is";

  $f_name = str_replace(__DIR__, '', $f_name);

  if (!preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
    $exit_code = 1;
    print "[ERROR]: card template validation failed in ($f_name)\n";
    return;
  }

  if (count($matches) > 1) {
    $exit_code = 1;
    print "[ERROR]: more than a single card template found in ($f_name)\n";
    return;
  }


  $card_img_md = $matches[0]['img'];
  //----------------------------------------------------------------------------
  // Card Image
  // https://regex101.com/r/CM2QKy/2
  static $hero_img_pattern = "~.*\!\[[^\]]+\]\(https://(?P<url>.*)\).*~is";

  if (!preg_match_all($hero_img_pattern, $card_img_md, $matches, PREG_SET_ORDER)) {
    $exit_code = 1;
    print "[ERROR]: Card image is not properly formatted in ($f_name)\n";
    return;
  }

};




apply_check_on_dir('/cards', $test_card_format);
exit($exit_code);






function apply_check_on_dir($path = "", $fun) {
  $dir = new DirectoryIterator(__DIR__ . $path);
  $dir_file = explode('/', $path);
  $dir_file = end($dir_file) . '.md';


  foreach ($dir as $fileinfo) {
    if ($fileinfo->isDot()) {
      continue;
    }

    $f_name = $fileinfo->getFilename();

    if ($f_name == $dir_file) {
      continue;
    }

    $fun($fileinfo->getRealPath());
  }
}

