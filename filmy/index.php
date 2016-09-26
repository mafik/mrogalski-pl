<!doctype html>
<meta charset="utf-8">
<script src="star-rating/jquery.js"></script>
<script src="star-rating/jquery.form.js"></script>
<script src="star-rating/jquery.rating.js"></script>
<link href='http://fonts.googleapis.com/css?family=Mouse+Memoirs&subset=latin-ext' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" href="star-rating/jquery.rating.css">
<style>
html {
	font-family: "Open Sans", sans-serif;
	text-align: center;
	background: url('pattern.png');
	color: #eee;
}
h1 {
	font-family: "Mouse Memoirs", sans-serif;
	font-weight: normal;
	font-size: 3em;
	text-shadow: 1px 1px 0 #999, 2px 2px 0 #999, 3px 3px 0 #999, 1px 1px 10px black;
}
#log_info {
	display: none;
}
.thankyou {
	max-height: 100%;
	max-width: 100%;
}
.ratebox {
	display: inline-block;
	background: #fff;
	padding: 1em;
	border-radius: .5em;
	box-shadow:  1px 1px 0 #999, 2px 2px 0 #999, 3px 3px 0 #999, 1px 1px 10px black;
}
#rate {
	display: inline-block;
}
footer {
	margin-top: 1em;
}
</style>
<title>Oskary</title>
<?php
session_start();

$pdo = new PDO('sqlite:tmdbXXX.sqlite');

if(!isset($_SESSION['user']) && isset($_GET['name'])) {
	$query = "INSERT INTO User VALUES (NULL, ? )";
	$stmt = $pdo->prepare($query);
	$stmt->execute(array($_GET['name']));

	$_SESSION['user'] = $pdo->lastInsertId();
	$_SESSION['username'] = $_GET['name'];
}

if(!isset($_SESSION['user'])) { ?>

	<?php
	$cnt = $pdo->query("select count(*) as cnt from user;")->fetchObject()->cnt;
	?>

	<h1>Gratulacje, jesteś <?php echo $cnt; ?> gościem!</h1>
	<p>Zostałeś wybrany do wybrania filmów, które nagrodzimy Oskarami.
	<p>Kategoria: <em>Ulubione przez ciebie</em>.
	<p>Zacznij od przedstawienia się:
	<form method="GET">
		<input name="name">
	</form>
	<hr>

<?php } else {
	$user = $_SESSION['user'];
	$username = $_SESSION['username'];
	echo '<div id="log_info">Zalogowany jako <em>'.$username.'</em></div>';

	if(isset($_GET['movie_id'])) {
		$movie_id = $_GET['movie_id'];
		if(isset($_GET['rating'])) {
			$rating = $_GET['rating'];
			$query = "INSERT INTO Score VALUES (?, ?, ?)";
			$stmt = $pdo->prepare($query);
			$stmt->execute(array($movie_id, $user, $rating));
		} else {
			$query = "INSERT INTO Score VALUES (?, ?, NULL)";
			$stmt = $pdo->prepare($query);
			$stmt->execute(array($movie_id, $user));
		}
	}

	// Wybranie jeszcze nie ocenionego filmu
	$query = "select * from Movie
		where id not in (select movie_id from Score where user_id == ?)
		order by random() limit 1;";
	$stmt = $pdo->prepare($query);
	$stmt->execute(array($user));
	$result = $stmt->fetchObject();

	// TODO: sprawdzić czy user nie ocenił już wszystkich filmów
	if($result) {
		// Wyświetlanie formularza z filmem
		echo "<h1>".$result->name ."</h1>";
		$movie_url = "http://www.themoviedb.org".$result->relative_url;
		$html=file_get_contents($movie_url);
		$DOM = new DOMDocument();
		$DOM->loadHTML($html);
		$xpath = new DOMXpath($DOM);
		$elements = $xpath->query('//*[@id="leftCol"]/a/img');
		echo '<div class="ratebox">';
		foreach ($elements as $element) {
			$src = $element->getAttribute('src');
			echo '<a href="'.$movie_url.'" target="_blank">';
			echo '<img class="poster" alt="Okładka '.$result->name.'" src="'.$src.'"><br>';
			echo '</a>';
		}
		echo '<form id="rate" method="GET">';
		echo '<input name="movie_id" type="hidden" value="'.$result->id.'">';
		for ($i = 0; $i <= 5; $i++) {
			echo '<input name="rating" type="radio" class="auto-submit-star" value="'.$i.'"/>';
		}
		echo '</form>';
		echo '</div>';
		
		
		$query = "select count(*) as cnt from Movie
			where id in (select movie_id from Score where user_id == ?);";
		$stmt = $pdo->prepare($query);
		$stmt->execute(array($user));
		$result = $stmt->fetchObject();
		$done = $result->cnt;
		
		$query = "select count(*) as cnt from Movie;";
		$stmt = $pdo->query($query);
		$result = $stmt->fetchObject();
		$total = $result->cnt;
		echo '<footer>';
		echo "<progress value=\"$done\" max=\"$total\"></progress><br>";
		echo "$done / $total";
		echo '</footer>';
	} else {
		echo '<h1>Dziękujemy!</h1>';
		echo '<img class="thankyou" src="free.jpg" alt="Skończyłeś">';
	}
} 

?>
<script>
$(function() {
	$('.auto-submit-star').rating({
		callback: function(value, link) {
			if(this == window) {
				$('#rate').submit();
			} else {
				this.form.submit();
			}
		}
	});
});
</script>
