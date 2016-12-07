<?php
	$db = mysqli_connect('127.0.0.1','root','cs411fa2016','moovies') or die('Error connecting to MySQL server!!');
?>

<html>
<body>
Search for a person
<form action="" method="post">
<input type="search" name="search" autofocus>
<input type="submit" name="button">
</form>

<?php
	if(isset($_POST["button"])){
		$search_term = $_POST["search"];
		$name_split = explode(" ", $search_term);
		$sql = "SELECT first_name, last_name, id, dob FROM person2 
		WHERE ((person2.first_name like '%$name_split[0]%' 
		and person2.last_name like '%$name_split[1]%') 
		or (person2.first_name like '%$name_split[1]%' 
		and person2.last_name like '%$name_split[0]%'))";
		$result = $db->query($sql);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				echo "<a href='personPage.php?id=" . $row['id'] . "'>" . $row['first_name'] . " " . $row['last_name'] . "(" . $row['dob'] . ")" . "</a>";
				echo "<br>";
			}
		}
		else
			echo "0 results";
	}
	$db->close();
?>

</body>
</html>
