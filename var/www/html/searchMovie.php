<?php
$db = mysqli_connect('127.0.0.1','root','cs411fa2016','moovies') or die('Error connecting to MySQL server!!');
?>

<html>
<body>
Search for a movie
<form action="" method="post">
<input type="search" name="search" autofocus>
<input type="submit" name="button">
</form>

<?php
if (isset($_POST["button"])){
	$search_term=$_POST["search"];
	$sql = 	"SELECT title, id  FROM movie2 where title like '%$search_term%'";
	$sql_up = "SELECT title, id FROM upcoming_movies where title like '%$search_term%'";	
	$result = $db->query($sql);
	$result_up = $db->query($sql_up);
?>

<h3>Current Movies</h3>

<?php
	if($result->num_rows > 0 || $result_up->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			echo "<a href='moviePage.php?cid=" . $row['id'] . "'>" . $row['title'] . "</a>";
			echo "<br>";
		}
?>

<h3>Upcoming Movies</h3>

<?php
		while($row = $result_up->fetch_assoc()) {
			echo "<a href='moviePage.php?uid=" . $row['id'] . "'>" . $row['title'] . "</a>";
			echo "<br>";	
		}	
	}

	else {
		echo "0 results" . "<br>";
	}
}

$db->close();
?>

</body>
</html>
