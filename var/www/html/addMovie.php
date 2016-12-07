<?php
$db = mysqli_connect('127.0.0.1','root','cs411fa2016','moovies') or die('Error connecting to MySQL server!!');
$imdb = mysqli_connect('127.0.0.1','root','cs411fa2016','imdb') or die('Error connecting to MySQL server!!');
?>


<html>
<body>
<h1> Add a movie to the database. </h1>
<form action="" method="post">
	Title: <input type="text" name="title"><br>
	Release Date: <input type="text" name="releaseDate"><br><br>
	Plot: <textarea cols="40" rows="4" name="plot"></textarea><br>
	<input type="submit" name="button">
</form>

<?php
if (isset($_POST["button"])){
	$title = $_POST["title"];
	$release_date = $_POST["releaseDate"];
	$plot = $_POST["plot"];
	//$id_result = $db->query("SELECT movie2.id FROM movie2 ORDER BY movie2.id DESC LIMIT 1");
	//$id_row = $id_result->fetch_assoc();
	//$id = $id_row["id"] + 1;
	//$imdb_rating_result = $imdb->query("SELECT rating FROM 'rating' WHERE title = '$title'");
	//$imdb_rating_row = $imdb_rating_result->fetch_assoc();
	//$imdb_rating = 0; $imdb_rating_row["rating"];
	$genres = "idk";
	$image = NULL;
	
	$sql = "INSERT INTO upcoming_movies (title, plot, release_date, genres, image) VALUES ('$title', '$plot', '$release_date', '$genres', '$image')";

	$db->query($sql);
	
	echo "<br>";
	echo "Pending..." . "<br>";
	//echo $id . "<br>";
	echo "Title: " . $title . "<br>";
	echo "Plot: " . $plot . "<br>";
	echo "Release date: " . $release_date . "<br>";
	echo "Genres: " . $genres . "<br>";
	//echo $predicted_rating ."<br>";
	//echo $imdb_rating . "<br>";
	echo "Image: " . $image . "<br>";
}

$db->close();
$imdb->close();
?>

</body>
</html>

