<?php
	include "db_connect.php";
	$obj = new DB_Connect();
	$s = $_REQUEST["cntrid"];
    // $cid = $_REQUEST['ctid'];
	$stmt = $obj->con1->prepare("select * from states WHERE country_id=?");
	$stmt->bind_param("i", $s);
	$stmt->execute();
	$result = $stmt->get_result();


	$stmt_country = $obj->con1->prepare("select * from countries WHERE id=?");
	$stmt_country->bind_param("i", $s);
	$stmt_country->execute();
	$result_country = $stmt_country->get_result()->fetch_assoc();
?>
<option value="">Choose State</option>

<?php 
$html="";

while ($row = mysqli_fetch_assoc($result)) { 
?>
	<option value="<?php echo $row["id"]; ?>">
		<?php echo $row["name"]; ?>
	</option>
<?php
}
echo $html."@@@".$result_country["phonecode"];

?>