<?php
//Created by Dev Jariwala
include "header.php";
if (isset($_COOKIE['edit_id'])) {
	$mode = 'edit';
	$editId = $_COOKIE['edit_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `event` where event_id=?");
	$stmt->bind_param('i', $editId);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}

if (isset($_COOKIE['view_id'])) {
	$mode = 'view';
	$viewId = $_COOKIE['view_id'];
	$stmt = $obj->con1->prepare("SELECT * FROM `event` where event_id=?");
	$stmt->bind_param('i', $viewId);
	$stmt->execute();
	$data = $stmt->get_result()->fetch_assoc();
	$stmt->close();
}
if (isset($_REQUEST["btnsubmit"])) {
	$event_name = $_REQUEST["event_name"];
	$description = $_REQUEST["description"];
	$event_date = $_REQUEST["event_date"];
	$event_img = $_FILES['event_img']['name'];
	$event_img = str_replace(' ', '_', $event_img);
	$event_img_path = $_FILES['event_img']['tmp_name'];

	if ($event_img != "") {
		if (file_exists("images/event_image/" . $event_img)) {
			$i = 0;
			$PicFileName = $event_img;
			$Arr1 = explode('.', $PicFileName);

			$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			while (file_exists("images/event_image/" . $PicFileName)) {
				$i++;
				$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			}
		} else {
			$PicFileName = $event_img;
		}
	}

	try {
		echo "INSERT INTO `event`(`event_name`, `description`, `event_date`, `main_img`) VALUES ('".$event_name."','".$description."','".$event_date."','".$PicFileName."')";
		$stmt = $obj->con1->prepare("INSERT INTO `event`(`event_name`, `description`, `event_date`, `main_img`) VALUES (?,?,?,?)");
		$stmt->bind_param("ssss", $event_name, $description, $event_date, $PicFileName);
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
		move_uploaded_file($event_img_path, "images/event_image/" . $PicFileName);
		setcookie("msg", "data", time() + 3600, "/");
		header("location:event.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:event.php");
	}
}
if (isset($_REQUEST["btn_update"])) {
	$id = $_COOKIE['edit_id'];
	$event_name = $_REQUEST["event_name"];
	$description = $_REQUEST["description"];
	$event_date = $_REQUEST["event_date"];
	$event_img = $_FILES['event_img']['name'];
	$event_img = str_replace(' ', '_', $event_img);
	$event_img_path = $_FILES['event_img']['tmp_name'];
	$old_img = $_REQUEST['old_img'];

	if ($event_img != "") {
		if (file_exists("images/event_image/" . $event_img)) {
			$i = 0;
			$PicFileName = $event_img;
			$Arr1 = explode('.', $PicFileName);

			$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			while (file_exists("images/event_image/" . $PicFileName)) {
				$i++;
				$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			}
		} else {
			$PicFileName = $event_img;
		}
		unlink("images/event_image/" . $old_img);
		move_uploaded_file($event_img_path, "images/event_image/" . $PicFileName);
	} else {
		$PicFileName = $old_img;
	}

	try {
		$stmt = $obj->con1->prepare("UPDATE `event` SET `event_name`=?,`description`=?,`event_date`=?,`main_img`=? WHERE `event_id`=?");
		$stmt->bind_param("ssssi", $event_name, $description, $event_date, $PicFileName, $id);
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
		header("location:event.php");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
		header("location:event.php");
	}
}

if (isset($_REQUEST["flg"]) && $_REQUEST["flg"] == "del") {
	$event_subimg = $_REQUEST["event_subimg"];
	try {
		$stmt_del = $obj->con1->prepare("DELETE FROM `event_subimages` WHERE subimg_id='" . $_REQUEST["sub_img_id"] . "'");
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
		if (file_exists("images/event_image/" . $event_subimg)) {
			unlink("images/event_image/" . $event_subimg);
		}
		setcookie("msg", "data_del", time() + 3600, "/");
	} else {
		setcookie("msg", "fail", time() + 3600, "/");
	}
	header("location:event.php");
}
?>
<div class='p-6'>
	<div class="flex gap-6 items-center pb-8">
		<span class="cursor-pointer">
			<a href="javascript:go_back()" class="text-3xl text-black dark:text-white">
				<i class="ri-arrow-left-line"></i>
			</a>
		</span>
		<h1 class="dark:text-white-dar text-2xl font-bold">Event -
			<?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
		</h1>
	</div>
	<div class="panel mt-6">
		<div class="mb-5">
			<form class="space-y-5" method="post" enctype="multipart/form-data">
				<div>
					<label for="event_name">Event Name</label>
					<input id="event_name" name="event_name" type="text" class="form-input" required
						value="<?php echo (isset($mode)) ? $data['event_name'] : '' ?>" placeholder="Enter Event" <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
				</div>

				<div>
					<label for="event_date">Event Date</label>
					<input id="event_date" name="event_date" type="date" class="form-input" required
						value="<?php echo (isset($mode)) ? $data['event_date'] : '' ?>" <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
				</div>
				
				<div class="mb-4">
					<label for="quill1">Description</label>
					<div id="editor1">
						<?php echo (isset($mode)) ? $data['description'] : '' ?>
					</div>
				</div>
				<input type="hidden" id="quill-input1" name="description">

				<div <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>>
					<label for="image">Image</label>
					<input id="event_img" name="event_img" class="demo1" type="file" data_btn_text="Browse"
						onchange="readURL(this,'PreviewImage')" onchange="readURL(this,'PreviewImage')"
						placeholder="drag and drop file here" />
				</div>
				<div>
					<h4 class="font-bold text-primary mt-2  mb-3"
						style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">Preview</h4>
					<img src="<?php echo (isset($mode)) ? 'images/event_image/' . $data["main_img"] : '' ?>" name="PreviewImage"
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
<?php if (isset($mode)) { ?>
	function getActions(id, event_img) {
		checkCookies();
		return `<ul class="flex items-center gap-4">
		<li>
			<a href="javascript:viewdata(`+ id + `);" class='text-xl' x-tooltip="View">
			<i class="ri-eye-line text-primary"></i>
			</a>
		</li>
		<?php if(isset($mode) && $mode == 'edit') { ?>
		<li>
			<a href="javascript:editdata(`+ id + `);" class='text-xl' x-tooltip="Edit">
			<i class="ri-pencil-line text text-success"></i>
			</a>
		</li>
		<li>
			<a href="javascript:showAlert(`+ id + `,\'` + event_img + `\');" class='text-xl' x-tooltip="Delete">
			<i class="ri-delete-bin-line text-danger"></i>
			</a>
		</li>
		<?php } ?>
		</ul>`
	}
	document.addEventListener('alpine:init', () => {
		Alpine.data('pagination', () => ({
			datatable: null,
			init() {
				this.datatable = new simpleDatatables.DataTable('#myTable', {
					data: {
						headings: ['Sr.No.', 'Image', 'Action'],
						data: [
						<?php
							$id = ($mode=='edit')?$editId:$viewId;
							$stmt = $obj->con1->prepare("SELECT * FROM `event_subimages` WHERE event_id=? order by subimg_id desc");
							$stmt->bind_param("i",$id);
							$stmt->execute();
							$Resp = $stmt->get_result();
							$i = 1;
							while ($row = mysqli_fetch_array($Resp)) { ?>
								[
								<?php echo $i; ?>,
								'<img src="images/event_image/<?php echo addslashes($row["subimg"]); ?>" height="200" width="200" class="object-cover shadow rounded">',
								getActions(<?php echo $row["subimg_id"]; ?>, '<?php echo addslashes($row["subimg"]); ?>')
								],
								<?php $i++;
							}
						?>
						],
					},
					perPage: 10,
					perPageSelect: [10, 20, 30, 50, 100],
					columns: [{
						select: 0,
						sort: 'asc',
					},],
					firstLast: true,
					firstText: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M13 19L7 12L13 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path opacity="0.5" d="M16.9998 19L10.9998 12L16.9998 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
					lastText: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M11 19L17 12L11 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path opacity="0.5" d="M6.99976 19L12.9998 12L6.99976 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
					prevText: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M15 5L9 12L15 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
					nextText: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>',
					labels: {
						perPage: '{select}',
					},
					layout: {
						top: '{search}',
						bottom: "<div class='flex items-center gap-4'>{info}{select}</div>{pager}",
					},
				});
			},

			printTable() {
				this.datatable.print();
			},

			formatDate(date) {
				if (date) {
					const dt = new Date(date);
					const month = dt.getMonth() + 1 < 10 ? '0' + (dt.getMonth() + 1) : dt.getMonth() +
						1;
					const day = dt.getDate() < 10 ? '0' + dt.getDate() : dt.getDate();
					return day + '/' + month + '/' + dt.getFullYear();
				}
				return '';
			},
		}));
	})
<?php } ?>
	function go_back() {
		eraseCookie("edit_id");
		eraseCookie("view_id");
		window.location = "event.php";
	}

	function editdata(id) {
		createCookie("edit_subimg_id", id, 1);
		window.location = "add_event_subimages.php";
	}

	function viewdata(id) {
		createCookie("view_subimg_id", id, 1);
		window.location = "add_event_subimages.php";
	}

	async function showAlert(id, img) {
		new window.Swal({
			title: 'Are you sure?',
			text: "You won't be able to revert this!",
			showCancelButton: true,
			confirmButtonText: 'Delete',
			padding: '2em',
		}).then((result) => {
			if (result.isConfirmed) {
				var loc = "add_event.php?flg=del&sub_img_id=" + id + "&event_subimg=" + img;
				window.location = loc;
			}
		});
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