<?php

$name = $_GET['name'];
$code = $_GET['code'];

//echo 'Got '.$name.', '.$code."\n";

$db = new SQLite3('./robotDB');


$results = $db->query('SELECT user, julianday("now") - julianday(date) AS days FROM marks WHERE code == "'.$code.'" ORDER BY date DESC LIMIT 1;');

if ($row = $results->fetchArray()) { // This will execute at most once.
    if ($row[0] == $name) {
        $points = min(10, $row[1]);
        echo "ALREADY_MARKED ".$points."\n";
    } else {
        $points = min(15, $row[1] * 5);
        echo "REGAINED ".$points."\n";
    }
    
    //var_dump($row);
} else {
    echo "MARKED 1\n";
}

$db->exec('DELETE FROM marks WHERE code == "'.$code.'";');
$db->exec('INSERT INTO marks VALUES ("'.$name.'", "'.$code.'", datetime("now"));');

$db->close();

?>
