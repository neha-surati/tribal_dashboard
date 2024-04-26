<?php
	include "db_connect.php";
	$obj = new DB_Connect();
	$s = $_REQUEST["cntrid"];
    // $cid = $_REQUEST['ctid'];
	$stmt = $obj->con1->prepare("select * from states WHERE country_id=?");
	$stmt->bind_param("i", $s);
	$stmt->execute();
	$result = $stmt->get_result();
?>
<option value="">Choose State</option>

<?php 
while ($row = mysqli_fetch_assoc($result)) { 
?>
	<option value="<?php echo $row["id"]; ?>">
		<?php echo $row["name"]; ?>
	</option>
<?php
}
?>