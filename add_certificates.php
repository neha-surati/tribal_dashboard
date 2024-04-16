<?php
//Created by Dev Jariwala
include "header.php";
if (isset($_COOKIE['edit_id'])) {
	$mode = 'edit';
	$editId = $_COOKIE['edit_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `certificates` where id=?");
	$stmt->bind_param('i', $editId);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}

if (isset($_COOKIE['view_id'])) {
	$mode = 'view';
	$viewId = $_COOKIE['view_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `certificates` where id=?");
	$stmt->bind_param('i', $viewId);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}
if (isset($_REQUEST["btnsubmit"])) {
	$title = $_REQUEST["title"];
	// $event_date = $_REQUEST["event_date"];
	$certi_img = $_FILES['certi_img']['name'];
	$certi_img = str_replace(' ', '_', $certi_img);
	$certi_img_path = $_FILES['certi_img']['tmp_name'];
	$status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? 'enable' : 'disable';

	if ($certi_img != "") {
		if (file_exists("images/certificates_images/" . $certi_img)) {
			$i = 0;
			$PicFileName = $certi_img;
			$Arr1 = explode('.', $PicFileName);

			$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			while (file_exists("images/certificates_images/" . $PicFileName)) {
				$i++;
				$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			}
		} else {
			$PicFileName = $certi_img;
		}
	}

	try {
		echo "INSERT INTO `certificates`(`title`, `image`, `status`) VALUES ('".$title."','".$PicFileName."','".$status."')";
		$stmt = $obj->con1->prepare("INSERT INTO `certificates`(`title`, `image`, `status`) VALUES (?,?,?)");
		$stmt->bind_param("ssss", $title, $PicFileName, $status);
		$Resp = $stmt->execute();
		if (!$Resp) {
			throw new Exception(
				"Problem in adding! " . strtok($obj->con1->error, "(")
			);
		}
		$stmt->close();
	} catch (\Exception $e) {
		setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
	}

	if ($Resp) {
		move_uploaded_file($certi_img_path, "images/certificates_images/" . $PicFileName);
		setcookie("msg", "data", time() + 3600, "/");
		header("location:certificates.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:certificates.php");
	}
}
if (isset($_REQUEST["btn_update"])) {
	$e_id = $_COOKIE['edit_id'];
	$title = $_REQUEST["title"];
	// $certi_date = $_REQUEST["certi_date"];
	$certi_img = $_FILES['certi_img']['name'];
	$certi_img = str_replace(' ', '_', $certi_img);
	$certi_img_path = $_FILES['certi_img']['tmp_name'];
	$status = (isset($_REQUEST["status"]) && $_REQUEST["status"] == 'on') ? 'enable' : 'disable';
	$old_img = $_REQUEST['old_img'];

	if ($event_img != "") {
		if (file_exists("images/certificates_images/" . $certi_img)) {
			$i = 0;
			$PicFileName = $event_img;
			$Arr1 = explode('.', $PicFileName);

			$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			while (file_exists("images/certificates_images/" . $PicFileName)) {
				$i++;
				$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			}
		} else {
			$PicFileName = $certi_img;
		}
		unlink("images/certificates_images/" . $old_img);
		move_uploaded_file($certi_img_path, "images/certificates_images/" . $PicFileName);
	} else {
		$PicFileName = $old_img;
	}

	try {
		$stmt = $obj->con1->prepare("UPDATE `certificates` SET `title`=?,`image`=? `status`=? WHERE `event_id`=?");
		$stmt->bind_param("ssssi", $event_name, $PicFileName, $status, $e_id);
		$Resp = $stmt->execute();
		if (!$Resp) {
			throw new Exception(
				"Problem in updating! " . strtok($obj->con1->error, "(")
			);
		}
		$stmt->close();
	} catch (\Exception $e) {
		setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
	}

	if ($Resp) {
		setcookie("edit_id", "", time() - 3600, "/");
		setcookie("msg", "update", time() + 3600, "/");
		header("location:certificates.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:certificates.php");
	}
}

if (isset($_REQUEST["flg"]) && $_REQUEST["flg"] == "del") {
	$event_subimg = $_REQUEST["event_subimg"];
	try {
		$stmt_del = $obj->con1->prepare("DELETE FROM `certificates` WHERE id='" . $_REQUEST["id"] . "'");
		$Resp = $stmt_del->execute();
		if (!$Resp) {
			if (
				strtok($obj->con1->error, ":") == "Cannot delete or update a parent row"
			) {
				throw new Exception("Image is already in use!");
			}
		}
		$stmt_del->close();
	} catch (\Exception $e) {
		setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
	}

	if ($Resp) {
		if (file_exists("images/certificates_images/" . $event_subimg)) {
			unlink("images/certificates_images/" . $event_subimg);
		}
		setcookie("msg", "data_del", time() + 3600, "/");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
	}
	header("location:certificates.php");
}
?>
<div class='p-6'>
	<div class="flex gap-6 items-center pb-8">
		<span class="cursor-pointer">
			<a href="javascript:go_back()" class="text-3xl text-black dark:text-white">
				<i class="ri-arrow-left-line"></i>
			</a>
		</span>
		<h1 class="dark:text-white-dar text-2xl font-bold">Certificates -
			<?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
		</h1>
	</div>
	<div class="panel mt-6">
		<div class="mb-5">
			<form class="space-y-5" method="post" enctype="multipart/form-data">
				<div>
					<label for="title">Certificate Title</label>
					<input id="title" name="title" type="text" class="form-input" required
						value="<?php echo (isset($mode)) ? $data['title'] : '' ?>" placeholder="Enter Title" <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
				</div>

                <div class="mb-4">
					<label for="custom_switch_checkbox1">Status</label>
					<label class="w-12 h-6 relative">
						<input type="checkbox"
							class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status"
							name="status" <?php echo (isset($mode) && $data['b_status'] == 'enable') ? 'checked' : '' ?>
							<?php echo (isset($mode) && $mode == 'view') ? 'disabled' : '' ?>><span
							class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
					</label>
				</div>
				<div <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>>
					<label for="image">Image</label>
					<input id="event_img" name="event_img" class="demo1" type="file" data_btn_text="Browse"
						onchange="readURL(this,'PreviewImage')" onchange="readURL(this,'PreviewImage')"
						placeholder="drag and drop file here" />
				</div>
				<div>
					<h4 class="font-bold text-primary mt-2  mb-3"
						style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">Preview</h4>
					<img src="<?php echo (isset($mode)) ? 'images/certificates_images/' . $data["main_img"] : '' ?>" name="PreviewImage"
						id="PreviewImage" width="400" height="400"
						style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>" class="object-cover shadow rounded">
					<div id="imgdiv" style="color:red"></div>
					<input type="hidden" name="old_img" id="old_img"
						value="<?php echo (isset($mode) && $mode == 'edit') ? $data["main_img"] : '' ?>" />
				</div>

				<div class="relative inline-flex align-middle gap-3 mt-4 ">
					<button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'btn_update' : 'btnsubmit' ?>"
						id="save" class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>"
						onclick="return setQuillInput()">
						<?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
					</button>
					<button type="button" class="btn btn-danger"
						onclick="<?php echo (isset($mode)) ? 'javascript:go_back()' : 'window.location.reload()' ?>">Close</button>
				</div>
		</div>
		</form>
	</div>
</div>

<script type="text/javascript">
	function go_back() {
		eraseCookie("edit_id");
		eraseCookie("view_id");
		window.location = "certificates.php";
	}

	var quill1 = new Quill('#editor1', {
		theme: 'snow',
	});
	var toolbar1 = quill1.container.previousSibling;
	toolbar1.querySelector('.ql-picker').setAttribute('title', 'Font Size');
	toolbar1.querySelector('button.ql-bold').setAttribute('title', 'Bold');
	toolbar1.querySelector('button.ql-italic').setAttribute('title', 'Italic');
	toolbar1.querySelector('button.ql-link').setAttribute('title', 'Link');
	toolbar1.querySelector('button.ql-underline').setAttribute('title', 'Underline');
	toolbar1.querySelector('button.ql-clean').setAttribute('title', 'Clear Formatting');
	toolbar1.querySelector('[value=ordered]').setAttribute('title', 'Ordered List');
	toolbar1.querySelector('[value=bullet]').setAttribute('title', 'Bullet List');

	function setQuillInput() {
		let quillInput1 = document.getElementById("quill-input1");
		quillInput1.value = quill1.root.innerHTML;

		let val1 = quillInput1.value.replace(/<[^>]*>/g, '');
		
		if (val1.trim() == '') {
			coloredToast("danger", 'Please add something in Description.');
			return false;
		}
		<?php if(!isset($mode)){ ?>
         else if (<?php echo (!isset($mode))?true:false ?>) {
            return checkImage();
        } 
        <?php } ?> 
		// else{
		// 	return true;
		// }
	}

	function readURL(input, preview) {
		if (input.files && input.files[0]) {
			var filename = input.files.item(0).name;

			var reader = new FileReader();
			var extn = filename.split(".");

			if (extn[1].toLowerCase() == "jpg" || extn[1].toLowerCase() == "jpeg" || extn[1].toLowerCase() == "png" || extn[1].toLowerCase() == "bmp") {
				reader.onload = function (e) {
					$('#' + preview).attr('src', e.target.result);
					document.getElementById(preview).style.display = "block";
				};

				reader.readAsDataURL(input.files[0]);
				$('#imgdiv').html("");
				document.getElementById('save').disabled = false;
			}
			else {
				$('#imgdiv').html("Please Select Image Only");
				document.getElementById('save').disabled = true;
			}
		}
	}
</script>
<?php
include "footer.php";
?>