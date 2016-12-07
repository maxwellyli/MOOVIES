<?php
$db = mysqli_connect('127.0.0.1','root','cs411fa2016','moovies') or die('Error connecting to MySQL server!!');
?>


<!DOCTYPE html>
<html>
<head>
  	<title>MOOVIES</title>
  	<meta charset="utf-8">
  	<link rel="stylesheet" type="text/css" href="index.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.js"></script>

	<!-- Material Design -->
	<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/material-design-lite/1.1.0/material.indigo-red.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<script defer src="https://code.getmdl.io/1.2.1/material.min.js"></script>
	
	<script>
	//	$(document).ready( function() {
	//		$('#search_results').DataTable();
	//		$('#search_results_up').DataTable();
	//	});
	</script>


</head>

<body>
	<!-- MAIN SEARCH BAR -->
	<div class="jumbotron text-center">
		<center><h2><a href='index.php'>MOOVIES</a></h2></center>
	

		<center>
		<!-- Search Bar -->
		<form action="" method="post">
                	<div class="mdl-textfield mdl-js-textfield">
                     		<input type="text" class="mdl-textfield__input" name="searchbar" id="inputfield">
				<label class="mdl-textfield__label" for="inputfield">Search</label>
			</div>
			<button class="mdl-button mdl-js-button mdl-button--icon" type="submit" name="submit">
				<i class="material-icons">search</i>
			</button>
		<br>
		<!-- Search Options -->
		<div class="button">
  		<label class="mdl-radio mdl-js-radio" for="option1">
  			<input type="radio" name="options" id="option1" value="title" autocomplete="off" class="mdl-radio__button" checked>
			<span class="mdl-radio__label">Movies &nbsp;&nbsp;&nbsp; </span>
 		</label>
 		<label class="mdl-radio mdl-js-radio" for="option2">
    			<input type="radio" name="options" id="option2" value="name"  autocomplete="off" class="mdl-radio__button">
			<span class="mdl-radio__label">People</span>
  		</label>
		</div>
		
		</form>
		</center>

	</div>


	<!-- SEARCH RESULTS -->
	<div class="container" id="search-results">
	<?php
	if (isset($_POST["submit"])){
        	$search_term=$_POST["searchbar"];
		$search_category=$_POST["options"];
		if ($search_category == "title") {
	        	$sql =  "SELECT title, genres, id FROM movie2 where $search_category like '%$search_term%'";
	        	$sql_up = "SELECT title, id FROM upcoming_movies where $search_category like '%$search_term%'";
	        	$result = $db->query($sql);
	        	$result_up = $db->query($sql_up);
		}

		else {
			$name_split = explode(" ", $search_term);
			/*$sql = "SELECT dob, first_name, last_name, id FROM person2 
			WHERE ((person2.first_name like '$name_split[0]'
			 or person2.last_name like '$name_split[1]')
			 or (person2.first_name like '$name_split[1]'
			 or person2.last_name like '$name_split[0]'))
			 ORDER BY num_movie_associations DESC";
			*/
			  $sql = "SELECT first_name, last_name, id, dob FROM person2 
                WHERE ((person2.first_name like '%$name_split[0]%' 
                and person2.last_name like '%$name_split[1]%') 
                or (person2.first_name like '%$name_split[1]%' 
                and person2.last_name like '%$name_split[0]%'))
		order by num_movie_associations DESC
		";
			$result_person = $db->query($sql);
		}
	?>


		<?php
	        if($result->num_rows > 0 || $result_up->num_rows > 0) {
		?>
	
	<div id="current">
	<h3>Movies</h3>
			
	<table id="search_results" class="mdl-data-table mdl-js-data-table mdl-shadow--2dp">
		<thead>
			<tr>
				<th class="mdl-data-table__cell--non-numeric">Title</th>
				<th class="mdl-data-table__cell--non-numeric">Genre</th>
			</tr>
		</thead>
		<tbody>
			<?php
	                while($row = $result->fetch_assoc()) {
			?>
			<tr>
	             		<td class="mdl-data-table__cell--non-numeric"><?php echo "<a href='moviePage.php?cid=" . $row['id'] . "'style='text-decoration:none; color:indigo;'>" . $row['title'] . "</a>"; ?></td>
	                        <td class="mdl-data-table__cell--non-numeric"><?php echo $row['genres'] ?></td>
			</tr>
			<?php	
	                }
			?>
		</tbody>
	</table>
	</div>
	
	
	<?php	
		
		}

		else if($result_person->num_rows > 0) {
	?>
	
	<div id="people">
	<h3>People</h3>

	<table id="search_results_people" class="mdl-data-table mdl-js-data-table mdl-shadow--2dp">
		<thead>
			<tr>
				<th class="mdl-data-table__cell--non-numeric">Name</th>
				<th class="mdl-data-table__cell--non-numeric">Date of Birth</th>	
			</tr>
		</thead>
		<tbody>
			<?php
			while($row = $result_person->fetch_assoc()) {
			?>
			<tr>
				<td class="mdl-data-table__cell--non-numeric"><?php echo "<a href='personPage.php?id=" . $row['id'] . "'style='text-decoration:none; color:indigo;'>" . $row['first_name'] . " " . $row['last_name'] . "</a>"; ?></td>
				<td class="mdl-data-table__cell--non-numeric"><?php echo $row['dob'] ?></td>
			</tr>
			<?php
			}
			?>
		</tbody>
	</table>		
	</div>
		
	<?php
		}

		else {
			?><h4>0 Results</h4><?php	
		}
	}

	$db->close();
	?>

  	</div>
</body>
<footer>
	<iframe scrolling="no" src="./footer.html"></iframe>
</footer>
</html>
