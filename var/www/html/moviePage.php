<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<link rel="stylesheet" href="https://code.getmdl.io/1.2.1/material.indigo-pink.min.css">
<script defer src="https://code.getmdl.io/1.2.1/material.min.js"></script>
<link rel="stylesheet" href="http:/fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">

<?php
$db = mysqli_connect('127.0.0.1','root','cs411fa2016','moovies') or die('Error connecting to MySQL server!!');
$imdb = mysqli_connect('127.0.0.1','root','cs411fa2016','imdb') or die('Error connecting to MySQL server!!');
?>

<html>
<link rel="stylesheet" type="text/css" href="moviePage.css">
<div id = "data">
<?php
	//including authorization info to use twitter API
	include 'twitter.php';

	//making a map/array to find the appropriate locations and fips format for d3 geomap
	$fips_lookup = array("ALABAMA" => "US01", "AL" => "US01", "NEBRASKA" => "US31", "NE" => "US31", "ALASKA" => "US02", "AK" => "US02", "NEVADA" => "US32", "NV" => "US32", "ARIZONA" => "US04", "AZ" => "US04", "NEW HAMPSHIRE" => "US33", "NH" => "US33", "ARKANSAS" => "US05", "AR" => "US05", "NEW JERSEY" => "US34", "NJ" => "US34", "CALIFORNIA" => "US06", "CA" => "US06", "NEW MEXICO" => "US35", "NM" => "US35", "COLORADO" => "US08", "CO" => "US08", "NEW YORK" => "US36", "NY" => "US36", "CONNECTICUT" => "US09", "CT" => "US09", "NORTH CAROLINA" => "US37", "NC" => "US37", "DELAWARE" => "US10", "DE" => "US10", "NORTH DAKOTA" => "US38", "ND" => "US38", "DISTRICT OF COLUMBIA" => "US11", "DC" => "US11", "OHIO" => "US39", "OH" => "US39", "FLORIDA" => "US12", "FL" => "US12", "OKLAHOMA" => "US40", "OK" => "US40", "GEORGIA" => "US13", "GA" => "US13", "OREGON" => "US41", "OR" => "US41", "HAWAII" => "US15", "HI" => "US15", "PENNSYLVANIA" => "US42", "PA" => "US42", "IDAHO" => "US16", "ID" => "US16", "PUERTO RICO" => "US72", "PR" => "US72", "ILLINOIS" => "US17", "IL" => "US17", "RHODE ISLAND" => "US44", "RI" => "US44", "INDIANA" => "US18", "IN" => "US18", "SOUTH CAROLINA" => "US45", "SC" => "US45", "IOWA" => "US19", "IA" => "US19", "SOUTH DAKOTA" => "US46", "SD" => "US46", "KANSAS" => "US20", "KS" => "US20", "TENNESSEE" => "US47", "TN" => "US47", "KENTUCKY" => "US21", "KY" => "US21", "TEXAS" => "US48", "TX" => "US48", "LOUISIANA" => "US22", "LA" => "US22", "UTAH" => "US49", "UT" => "US49", "MAINE" => "US23", "ME" => "US23", "VERMONT" => "US50", "VT" => "US50", "MARYLAND" => "US24", "MD" => "US24", "VIRGINIA" => "US51", "VA" => "US51", "MASSACHUSETTS" => "US25", "MA" => "US25", "VIRGIN ISLANDS" => "US78", "VI" => "US78", "MICHIGAN" => "US26", "MI" => "US26", "WASHINGTON" => "US53", "WA" => "US53", "MINNESOTA" => "US27", "MN" => "US27", "WEST VIRGINIA" => "US54", "WV" => "US54", "MISSISSIPPI" => "US28", "MS" => "US28", "WISCONSIN" => "US55", "WI" => "US55", "MISSOURI" => "US29", "MO" => "US29", "WYOMING" => "US56", "WY" => "US56", "MONTANA" => "US30", "MT" => "US30");

	//searching database for information about the movie
        $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $id = $_GET['cid'];
        if(empty($id) != 1){ 	//movie exists in our verified database
                $sql =  "SELECT title, plot, genres, release_date, predicted_rating, imdb_rating FROM movie2 WHERE id = '$id'";
                $result = $db->query($sql);
                if($result->num_rows > 0){
                        while($row = $result->fetch_assoc()){
                                echo "<h1>" . $row['title'] . "</h1>";
				$title = $row['title'];
				if(!$row['plot'])
					echo "<h5>Plot unavailable</h5>";
				
				else if(strlen($row['plot']) < 1024) {
					?><h5>Plot: </h5><div id="plot"><?php
					echo $row['plot'];
					?> </div> <?php
				}
				else { 
					?><h5>Plot: </h5><div id="plot"><?php
					echo $row['plot'] . "...";
					?> </div> <?php
				}
                        	?><h5>Genres: </h5><div id="plot"><?php	
				echo $row['genres'];
				?> </div> <?php
				if(stripos($row['release_date'], "USA") !== false){
					$release_date = $row['release_date'];
					$arr = explode('USA:', $release_date);
					$important = $arr[1];
					$arr2 = explode('-', $important);
					$important2 = $arr2[0];
					?><h5>USA Release Date: </h5><div id="plot"><?php
					echo $important2;
					?> </div> <?php
				}
				if(!$row['predicted_rating'])
					echo "<h5>Predicted Rating: </h5><div id=\"plot\">6.5</div>" . "<br>";
				else {			
					?><h5>Predicted Rating: </h5><div id="plot"><?php
					echo $row['predicted_rating'];
					?> </div> <?php
				}
				if(!$row['imdb_rating'])
					echo "<h5>IMDb rating unavailable</h5>";
				else {
					?><h5>IMDb Rating: </h5><div id="plot"><?php
					echo $row['imdb_rating'];
					?> </div> <?php
				}
			}
                }
		echo "<h5> Director: </h5>";
		$sql = "SELECT person2.first_name, person2.last_name, person2.id  
			FROM movie2, person2, worked_on 
			WHERE ((movie2.id = $id)) 
			AND movie2.id = worked_on.movie_id 
			AND person2.id = worked_on.person_id 
			AND worked_on.role_id = '8' ORDER BY last_name, first_name";
		?>
		<div id = director_list>
		<?php
		$result = $db->query($sql);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				echo "<a href='personPage.php?id=" . $row['id'] . "'style='text-decoration:none; color:green;'>" . $row['first_name'] . " " . $row['last_name'] . "</a>" . "<br>";
			}
		}
		?>
		</div>
		<?php	
               	echo "<h5> Cast: </h5>";
		$sql = "SELECT person2.first_name, person2.last_name, person2.id 
			FROM movie2, person2, worked_on 
			WHERE ((movie2.id = $id)) 
			AND movie2.id = worked_on.movie_id 
			AND person2.id = worked_on.person_id 
			AND (worked_on.role_id = '1' OR worked_on.role_id = '2')
			GROUP BY person2.id 
			ORDER BY last_name, first_name";
		?>
		<div id = cast_list>
		<?php
		$result = $db->query($sql);
               	if($result->num_rows > 0){
                       	while($row = $result->fetch_assoc()){
                               	echo "<a href='personPage.php?id=" . $row['id'] . "'style='text-decoration:none; color:green;'>" . $row['first_name'] . " " . $row['last_name'] . "</a>" . "<br>";
			}
                }
		?>
		</div>	
	<?php
	}	
        else{	//movie title comes from user added movie
                $id = $_GET['uid'];
		$sql =  "SELECT title, plot, genres, predicted_rating, imdb_rating FROM upcoming_movies where id = '$id'";
		$result = $db->query($sql);
                if($result->num_rows > 0){
                        while($row = $result->fetch_assoc()){
                                echo $row['title'] . "<br>";
				echo "Plot: " . $row['plot'] . "<br>";
				echo "Genres: " . $row['genres'] . "<br>";
                                echo "Predicted Rating: " . $row['predicted_rating'] . "<br>";
				if(!$row['imdb_rating'])
					echo "No IMDb Rating";
				else
					echo "IMDb Rating: " . $row['imdb_rating'];
				echo "<br>";
                        }
                }
                $sql = "SELECT person2.first_name, person2.last_name FROM movie2, person2, worked_on WHERE ((person2.id = $id)) and movie2.id = worked_on.movie_id and person2.id = worked_on.person_id";
                $result = $db->query($sql);
                if($result->num_rows > 0){
   	        	while($row = $result->fetch_assoc()){
                     		echo "<a href='personPage.php?id=" . $row['id'] . "'style='text-decoration:none; color:green;'>" . $row['first_name'] . " " . $row['last_name'] . "</a>";
                                echo "<br>";
                        }
                }
        }

	//saving tweets to our database
	//twitter only gives a max of 100 tweets per request
        $title = str_replace(' ', '%20', $title);
        $query = "1.1/search/tweets.json?q=" .$title. "&result_type=mixed&count=100&lang=en";
        $json = file_get_contents($api_base.$query,false,$context);
        $tweets = json_decode($json,true);
        $count = count($tweets["statuses"]);
        $tweet_urls = [];
        for($i = 0; $i < $count; $i++){
		$text = $tweets["statuses"][$i]['text'];
		if(!empty($tweets["statuses"][$i]['retweeted_status'])) {
			$tweet_id = $tweets["statuses"][$i]['retweeted_status']['id_str'];
			$location = $tweets["statuses"][$i]['retweeted_status']['user']['location'];
			if(empty($location) == 1) {
				$location = NULL;
			}
			$timestamp = $tweets["statuses"][$i]['retweeted_status']['created_at'];
			$user = $tweets["statuses"][$i]['retweeted_status']['user']['name'];
			$screen_name = $tweets["statuses"][$i]['retweeted_status']['user']['screen_name'];
		}
		else if(!empty($tweets["statuses"][$i]['quoted_status'])) {
			$tweet_id = $tweets["statuses"][$i]['quoted_status']['id_str'];
			$location = $tweets["statuses"][$i]['quoted_status']['user']['location'];
                        if(empty($location) == 1) {
                                $location = NULL;
                        }
			$timestamp = $tweets["statuses"][$i]['quoted_status']['created_at'];
			$user = $tweets["statuses"][$i]['quoted_status']['user']['name'];
			$screen_name = $tweets["statuses"][$i]['quoted_status']['user']['screen_name'];
		}
		else {
			$tweet_id = $tweets["statuses"][$i]['id_str'];
			$location = $tweets["statuses"][$i]['user']['location'];
			if(empty($location) == 1) {
				$location = NULL;
 			}
			$timestamp = $tweets["statuses"][$i]['created_at'];
			$user =  $tweets["statuses"][$i]['user']['name'];
			$screen_name = $tweets["statuses"][$i]['user']['screen_name'];
 		}
		$text = str_replace("'", "''", "$text");
		$location = str_replace("'", "''", "$location");
		$loc = explode(', ', $location);
		$fips = NULL;
		foreach($loc as $l) {
			if(array_key_exists(strtoupper($l), $fips_lookup)) {
				$fips = $fips_lookup[strtoupper($l)];
				break;
			}
		}
		$user = str_replace("'", "''", "$user");
		$screen_name = str_replace("'", "''", "$screen_name");
		$retweet_count = $tweets["statuses"][$i]['retweet_count'];
                $update_tweet_sql = "UPDATE tweets SET retweet_count = '$retweet_count', WHERE tweet_id = '$tweet_id' AND retweet_count <> '$retweet_count'";

		$result = $db->query($update_tweet_sql);
                if($result->num_rows == 0) {
			$add_tweet_sql = "INSERT INTO tweets (movie_id, tweet_id, text, location, fips, timestamp, user, screen_name, retweet_count) VALUES ('$id', '$tweet_id', '$text', '$location',  '$fips', '$timestamp', '$user', '$screen_name', '$retweet_count')";
			$db->query($add_tweet_sql);
		}
        }

	//get tweet_id of top 10 tweets in our database (by retweet_count) and save them to display
	$find_top_tweets = "SELECT tweet_id FROM tweets WHERE movie_id = '$id' ORDER BY retweet_count DESC";
	$result = $db->query($find_top_tweets);
	$count = $result->num_rows < 10 ? $result->num_rows : 10;
	for($i = 0; $i < $count; $i++) {
		$row = $result->fetch_assoc();
 		$tweet_urls[$i] = "https://twitter.com/Interior/status/".$row['tweet_id'];
	}

	//get the number of states for this tweet;
	$get_fips_count_sql = "SELECT fips, COUNT(fips) FROM tweets WHERE movie_id = $id AND fips IS NOT NULL GROUP BY fips";
	$result = $db->query($get_fips_count_sql);
	$data_array = [];
	$header = "Tweets,fips";
	$data_array[] = $header;
	if($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$c = $row['COUNT(fips)'];
			$f = $row['fips'];
			if(!empty($f)) {
				$info = "$c,$f";
				$data_array[] = $info;
			}
		}
	}
      
	$file = fopen('/var/www/html/csv/tweet_data.csv', 'w') or die(print_r(error_get_last(), true));
	foreach($data_array as $line) {
		fputcsv($file, explode(',', $line));
	}
	fclose($file);
	$db->close();
	$imdb->close();
?>
</div>
<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
<head>
  <title>MovieInfo</title>
</head>
<body>
<div id = "tweets">
<?php foreach($tweet_urls as $url) { ?>
<blockquote class="twitter-tweet">
<a href="<?php echo $url."<br>"?>">
</a></blockquote>
<?php } ?>

</div>

<div id="map">
<center><h6>Number of Tweets by State</h6></center>
</div>

<head>
<meta charset="utf-8">
<script src="http://d3js.org/d3.v3.min.js"></script>
<link href="https://d3-geomap.github.io/d3-geomap/css/d3.geomap.css" rel="stylesheet">
<script src="https://d3-geomap.github.io/d3-geomap/vendor/d3.geomap.dependencies.min.js"></script>
<script src="https://d3-geomap.github.io/d3-geomap/js/d3.geomap.min.js"></script>
</head>
<body>
<script type="text/javascript">
    //adding map
    /**var map = d3.geomap()
        .geofile('https://d3-geomap.github.io/d3-geomap/topojson/countries/USA.json')
        .projection(d3.geo.albersUsa);

    d3.select('#map').call(map.draw, map);**/

    var format = function(d) {
	if(!d) {
	   d = 0;
	   return '';
  	}
    	return d3.format('.2d')(d);
    }

    var map = d3.geomap.choropleth()
        .geofile('https://d3-geomap.github.io/d3-geomap/topojson/countries/USA.json').projection(d3.geo.albersUsa).colors(colorbrewer.YlGnBu[9])
	.column('Tweets').format(format).unitId('fips').scale(600).legend(true);

    d3.csv('/csv/tweet_data.csv', function(error, data) {
	//console.log(data);
	//console.log(error);
	d3.select('#map').datum(data).call(map.draw, map);
    });

</script>
</body>

<footer>
	<iframe scrolling="no" src="./footer.html"></iframe>
</footer>

</html>



