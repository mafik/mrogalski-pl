<!doctype html>
<meta charset="utf-8">
<head>
<title>Marek Rogalski - strona domowa</title>
<style><?php readfile("style.css"); ?></style>
<link rel="shortcut icon" href="favicon.ico">
</head>
<body>
<header>
  <hgroup>
    <h1><a href="http://mrogalski.pl/">Marek
    Rogalski</a>&nbsp;</h1><h2>(stara strona - sprawdź <a href="https://mrogalski.eu" style="font-weight: 800">nową</a>)</h2>
  </hgroup>
</header>

<?php

setlocale(LC_ALL, 'pl_PL.utf8');

function path_clean($path) {
  $path = str_replace("pages/", "", $path);
  $path = str_replace(".html", "", $path);
  return $path;
}

function path_unclean($path) {
  return "pages/".$path.".html";
}

function path_to_title($path) {
  return path_clean(basename($path));
}

function startsWith($haystack, $needle) {
  $length = strlen($needle);
  return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle) {
  $length = strlen($needle);
  if ($length == 0) {
    return true;
  }

  return (substr($haystack, -$length) === $needle);
}

function rscandir($base='./', &$data=array()) {

  $array = scandir($base);
   
  foreach($array as $value) {

    if (startsWith($value, '.')) continue;
  
    if (is_dir($base.$value)) {
      $data = rscandir($base.$value.'/', $data);
      
    } else if (is_file($base.$value)) {
      $data[] = $base.$value;
      
    }
    
  }
  
  return $data;
  
}

function is_bad_page($path) {
  return !endsWith($path, ".html");
}

?>

<div id="container">
  <script><?php readfile("jquery.min.js"); ?></script>
  
<div id=empty>

  <?php

     include 'simple_html_dom.php';

       function browse_dir($path) {
         if ($handle = opendir( $path )) {
           while (false !== ($entry = readdir($handle))) {
             if ($entry != "." && $entry != "..") {
               visit($path . "/" . $entry);
             }
           }
           closedir($handle);
         }
       }

       $pl = array();
       $en = array();

       function visit($path)
       {
         global $pl, $en;

         $name = basename( $path );

         if (is_dir($path)) {
           browse_dir( $path );
         } else if (is_file($path)) {

           if(is_bad_page($path)) return;

           $dom = file_get_html($path);

           foreach($dom->find('[lang=pl]') as $element) {
             foreach($element->find('.tags a') as $link) {
               $pl[$link->href] = $link->plaintext;
             }
           }

           foreach($dom->find('[lang=en]') as $element) {
             foreach($element->find('.tags a') as $link) {
               $en[$link->href] = $link->plaintext;
             }
           }

         }

       }

       browse_dir('pages');
       
       ?></div>
<article>

<?php

$clean_path = isset($_GET["q"]) ? $_GET["q"] : '';
if(strpos($clean_path,'..') !== false) $clean_path = "";
$path = path_unclean($clean_path);

if( file_exists($path) ) {
  readfile($path);
} else if("" == $clean_path ) {
  ?>

  <h1 lang=pl>Ostatnie zmiany</h1>
  <h1 lang=en>Recent changes</h1>

  <ul id="last_changes">

  <?php
    $files = rscandir('pages/');
    usort($files, function($a, $b) {
      return filemtime($a) < filemtime($b);
    });

    $i = 0;
    foreach($files as $file) {
      // if($i++ >= 20) break;
      if(is_bad_page($file)) continue;
      $clean_path = path_clean($file);

      $html = file_get_contents($file);

      $start = strpos($html, "<h1>"); // check for multilingual title
      if($start !== false) {
        $end = strpos($html, "</h1>");
        $title = substr($html, $start + 4, $end - $start - 4);
      } else {
        $title = path_to_title($file);
      }
      echo '<li><a href="'.urlencode($clean_path).'">'.$title.'</a> <time>'.strftime ("%e %B %Y", filemtime($file)).'</time></li>';      
    }
  ?>

  </ul>

  <?php
} else {
  readfile("404.html");
}

?>
</article>
<nav></nav>
</div>

<script>
$(function() {
  $('nav .expand').click(function() {
    $(this).next().slideToggle();
  });
  $('nav li li .expand').next().hide();
});

var lang = navigator.language;

$('[lang]').filter(function(i, el) { return (el.lang == 'pl') != (lang == 'pl');}).hide();
</script>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-460633-3']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</body>

