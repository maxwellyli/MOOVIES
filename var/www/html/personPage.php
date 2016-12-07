<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<link rel="stylesheet" href="https://fonts.googleapis.comcss?family=Roboto:300,400,500,700" type="text/css">
<link rel="stylesheet" href="https://code.getmdl.io/1.2.1/material.indigo-pink.min.css">
<script defer src="https://code.getmdl.io/1.2.1/material.min.js"></script>
<link rel="stylesheet" type="text/css" href="personPage.css">
</head>

<body>
        
<div id = "data">
<?php
        include 'twitter.php';
	$db = mysqli_connect('127.0.0.1','root','cs411fa2016','moovies') or die('Error connecting to MySQL server!!');
        $id = $_GET['id'];
        if(empty($id) != 1){
                $sql = "SELECT first_name,last_name,gender,dob FROM person2 where id = '$id'";
                $result = $db->query($sql);
                if($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()){
	                        echo "<h1>" . $row['first_name'] . " " . $row['last_name'] . "</h1>";
				if($row['gender'])
					?> <h5> Gender: </h5> <div id="gender"> <?php
					if($row['gender'] == 'f') {
						echo "Female";
					}
					else if($row['gender'] == 'm'){
						echo "Male";
					}
					//echo strtoupper($row['gender']);
					?> </div> <?php
                                if($row['dob'])
					?> <h5> Date of Birth: </h5> <div id="dob"> <?php
					echo $row['dob'] . "<br>";
					?> </div> <?php
                        }
                }
        }
	echo "<h5> Filmography: </h5>";
	/*$sql = "SELECT movie2.title, movie2.id 
	FROM movie2, person2, worked_on 
	WHERE ((person2.id = $id)) 
	AND movie2.id = worked_on.movie_id 
	AND person2.id = worked_on.person_id
	ORDER BY movie2.title";*/
        $sql = "select movie2.title,movie2.id from movie2 inner join (select * from worked_on where person_id=$id group by movie_id) as A on movie2.id=A.movie_id ORDER BY movie2.predicted_rating DESC";
?>
<div id = "list">
<?php
	$result = $db->query($sql);
        if($result->num_rows > 0){
               	while($row = $result->fetch_assoc()){
                       	echo "<a href='moviePage.php?cid=" . $row['id'] . "'style='text-decoration:none; color:green;'>" . $row['title'] . "</a>";
                       	echo "<br>";
               	}
        }
        else
               	echo "0 results";
?>
</div>
</div>
</body
<footer>
	<iframe scrolling="no" src="./footer.html"></iframe>
</footer>
