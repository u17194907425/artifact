<?php

global $exit_code;
$exit_code = 0;







$test_card_format = function($f_name) {
  global $exit_code;

  $content = file_get_contents($f_name);
  $f_name = str_replace(__DIR__, '', $f_name);



  //----------------------------------------------------------------------------
  // Card Template
  // https://regex101.com/r/Bv7OqT/1
  static $pattern = "~^{{ARTIFACT CARD}}\n(?P<img>.*?)\n---+\n(?P<descr>.*?)\n{{/ARTIFACT CARD}}$~sm";
  validate_using_regexp('Card template', $content, $pattern, $f_name, $matches, $exit_code);
  $card_img_md = $matches[0]['img'];
  $card_descr_md = $matches[0]['descr'];



  //----------------------------------------------------------------------------
  // Card Image
  // https://regex101.com/r/CM2QKy/4
  static $card_img_pattern = "~^\!\[[^\]]+\]\(https://(?P<url>[^\)]+)\).*~m";
  validate_using_regexp('Card image', $card_img_md, $card_img_pattern, $f_name, $matches, $exit_code);



  //----------------------------------------------------------------------------
  // Card Type
  // https://regex101.com/r/teJzXr/1
  static $card_type_pattern = "~^\*\s+Type: (?P<type>Hero|Creature|Item|Spell)$~m";
  validate_using_regexp('Card type', $card_descr_md, $card_type_pattern, $f_name, $matches, $exit_code);
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

    if ($f_name == $dir_file || $f_name == 'README.md') {
      continue;
    }

    $fun($fileinfo->getRealPath());
  }
}



function validate_using_regexp($descr = '', $str = '', $pattern = '~.*~i', $f_name, &$matches, &$exit_code) {
  if (!preg_match_all($pattern, $str, $matches, PREG_SET_ORDER)) {
    $exit_code = 1;
    print "[ERROR]: $descr is not properly formatted in ($f_name)\n";
    return;
  }

  if (count($matches) > 1) {
    $exit_code = 1;
    print "[ERROR]: more than a single $descr found in ($f_name)\n";
    return;
  }
}
