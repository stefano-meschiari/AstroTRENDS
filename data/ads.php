<?php
set_time_limit (100000);
$link = @mysql_connect('localhost:8888', 'mathalic_mines', 'Pbqajm4XzVZ3uQ84');
if (!$link) {
  die(mysql_error());
}

function logic($str) {
  $str = trim($str);
  if (strpos($str, '"') === FALSE && strpos($str, ' OR ') === FALSE)
    return '"' . ucwords($str) . '"';
  else
    return $str;
}

function check($str) {
  return urlencode(preg_replace('/[^\s\d\w-,\(\)\"]/', "", $str));
}

$urls = array(
"http://adsabs.harvard.edu/cgi-bin/nph-abs_connect?",
"http://esoads.eso.org/cgi-bin/nph-abs_connect?",
"http://cdsads.u-strasbg.fr/cgi-bin/nph-abs_connect?",
"http://ukads.nottingham.ac.uk/cgi-bin/nph-abs_connect?"
);


function download($keywords, $year1, $year2) {
  $keywords = check($keywords);
  $year1 = check($year1);
  $year2 = check($year2);
  $month1 = 1;
  $month2 = 12;
  print($keywords . "<br>");
  global $urls;

  $adsUrl = $urls[array_rand($urls)];

  $adsParameters = "db_key=AST&db_key=AST&qform=AST&arxiv_sel=astro-ph&arxiv_sel=cond-mat&arxiv_sel=cs&arxiv_sel=gr-qc&arxiv_sel=hep-ex&arxiv_sel=hep-lat&arxiv_sel=hep-ph&arxiv_sel=hep-th&arxiv_sel=math&arxiv_sel=math-ph&arxiv_sel=nlin&arxiv_sel=nucl-ex&arxiv_sel=nucl-th&arxiv_sel=physics&arxiv_sel=quant-ph&arxiv_sel=q-bio&sim_query=YES&ned_query=YES&adsobj_query=YES&aut_logic=OR&obj_logic=OR&author=&object=&start_mon=$month1&start_year=$year1&end_mon=$month2&end_year=$year2&ttl_logic=OR&title=&txt_logic=OR&text=$keywords&nr_to_return=20&start_nr=1&jou_pick=NO&ref_stems=&data_and=ALL&group_and=ALL&start_entry_day=&start_entry_mon=&start_entry_year=&end_entry_day=&end_entry_mon=&end_entry_year=&min_score=&sort=SCORE&data_type=SHORT&aut_syn=YES&ttl_syn=YES&txt_syn=YES&aut_wt=1.0&obj_wt=1.0&ttl_wt=0.3&txt_wt=3.0&aut_wgt=YES&obj_wgt=YES&ttl_wgt=YES&txt_wgt=YES&ttl_sco=YES&txt_sco=YES&version=1";
 
  $url = $adsUrl . $adsParameters;
  print("<p>");
  print($url);
  print("</p>");
  $page = file_get_contents($url);

  $re1 = '/Total number selected: <strong>(\d+)<\/strong>/';
  $re2 = '/Selected and retrieved <strong>(\d+)<\/strong>/';

  $matches = array();
  
  usleep(1e6/5.);
  flush();
  ob_flush();
  if (preg_match($re1, $page, $matches) > 0) {
    return($matches[1]);
  } else if (preg_match($re2, $page, $matches) > 0) {
    return($matches[1]);
  } else return 0;
}

function loop() {
  $keywords = file('keywords.txt');
  $year1 = 1970;
  $year2 = 2013;
  if (file_exists("keywords.json")) 
    $bigarr = json_decode(file_get_contents("keywords.json"), true);
  else
    $bigarr = array();



  
  foreach ($keywords as $key) {
    $arr = array();
    
    $key = logic($key);
    
    if (array_key_exists($key, $bigarr)) {
      //print("Skipping " . $key);
      $b = $bigarr[$key];
      unset($bigarr[$key]);
      $bigarr[$key] = $b;
      continue;
    }
    for ($year = $year1; $year <= $year2; $year++) {
      $num = download($key, $year, $year);
      $arr[$year] = $num;
    }
    $bigarr[$key] = $arr;
    file_put_contents("keywords.json", json_encode($bigarr));
  }


  
}

loop();


?>
