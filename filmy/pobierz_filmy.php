<?php


	$db = new PDO('sqlite:tmdbXXX.sqlite');

for ( $i = 1; $i <=5	; $i++) {

	$html=file_get_contents("http://www.themoviedb.org/movie/top-rated?page=".$i);

	$DOM = new DOMDocument();
	$DOM->loadHTML($html);
	$xpath = new DOMXpath($DOM);


	$elements = $xpath->query('/html/body/div/div[4]/div/ul/li/child::*/h4/a/.');
	
	
	if (!is_null($elements)) {
	
		foreach ($elements as $element) {
		
			preg_match('/([0-9]+).*/', $element->getAttribute('href'),$match);
			
			$id=$match[1];
			$name=$element->childNodes->item(0)->nodeValue;
			$rel_url=$element->getAttribute('href');

			print "[". $id ."][". $element->nodeName ."=". $rel_url ."] == [". $name ."]\n";
						
			$query='INSERT INTO Movie VALUES('.$id.',"'.$name.'","'.$rel_url.'");';
			
			$sth=$db->prepare($query);
			$sth->execute();

	
		}
	}
	


}

	

?>
