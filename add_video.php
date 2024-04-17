<?php
//  BY ARYA 
include "header.php";
if (isset($_COOKIE['edit_id'])) {
    $mode = 'edit';
    $editId = $_COOKIE['edit_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `videos` WHERE id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE['view_id'])) {
    $mode = 'view';
    $viewId = $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `videos` WHERE id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST['update'])) {
    $e_id = $_COOKIE['edit_id'];
    $video = $_FILES['video']['name'];
    $video = str_replace(' ', '_', $video);
    $video_path = $_FILES['video']['tmp_name'];
    $old_vid = $_REQUEST['old_vid'];


    if ($video != "") {
        if (file_exists("videos/" . $video)) {
            $i = 0;
            $VidFileName = $video;
            $Arr1 = explode('.', $VidFileName);

            $VidFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("videos/" . $VidFileName)) {
                $i++;
                $VidFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $VidFileName = $video;
        }
        unlink("videos/" . $old_vid);
        move_uploaded_file($video_path, "videos/" . $VidFileName);
    } else {
        $VidFileName = $old_vid;
    }
    try {
        // echo ("UPDATE `videos` SET `description`= $desc , `video`= $VidFileName WHERE `id`= $e_id");
        $stmt = $obj->con1->prepare("UPDATE `videos` SET `video`=? WHERE `id`=?");
        $stmt->bind_param("si", $VidFileName, $e_id);
        $Resp = $stmt->execute();
        $stmt->close();
        if (!$Resp) {
            throw new Exception(
                "Problem in updating! " . strtok($obj->con1->error, "(")
            );
        }
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }
    if ($Resp) {
        setcookie("edit_id", "", time() - 3600, "/");
        setcookie("msg", "update", time() + 3600, "/");
        header("location:videos.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:videos.php");
    }
}
if (isset($_REQUEST["save"])) {
    $video = $_FILES['video']['name'];
    $video = str_replace(' ', '_', $video);
    $video_path = $_FILES['video']['tmp_name'];

    if ($video != "") {
        if (file_exists("videos/" . $video)) {
            $i = 0;
            $VidFileName = $video;
            $Arr1 = explode('.', $VidFileName);

            $VidFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("videos/" . $VidFileName)) {
                $i++;
                $VidFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $VidFileName = $video;
        }
    }

    try {
        // echo ("INSERT INTO `videos`(`description`, `video`) VALUES ( $desc, $VidFileName)");
        $stmt = $obj->con1->prepare("INSERT INTO `videos`(`video`) VALUES (?)");
        $stmt->bind_param("s", $VidFileName);
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
        move_uploaded_file($video_path, "videos/" . $VidFileName);
        setcookie("msg", "data", time() + 3600, "/");
        header("location:videos.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:videos.php");
    }
}
function is_image($filename)
{
	$allowed_extensions = array('jpg', 'jpeg', 'png', 'bmp');
	$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	return in_array($extension, $allowed_extensions);
}
?>

<div class='p-6'>
    <div class="flex gap-6 items-center pb-8">
        <span class="cursor-pointer">
            <a href="javascript:go_back()" class="text-3xl text-black dark:text-white">
                <i class="ri-arrow-left-line"></i>
            </a>
        </span>
        <h1 class="dark:text-white-dar text-2xl font-bold">Videos -
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post" enctype="multipart/form-data">
                <div <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>>
                    <label for="video">Video</label>
                    <input id="video" name="video" class="demo1" type="file" data_btn_text="Browse"
                        onchange="readURL(this,'PreviewVideo')" onchange="readURL(this,'PreviewVideo')"
                        placeholder="drag and drop file here" />
                </div>


                <div id="mediaPreviewContainer" style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">


                    <!-- <img src="<?php echo (isset($mode) && is_image($data["video"])) ? 'videos/' . $data["video"] : '' ?>"
                        name="PreviewMedia" id="PreviewMedia" width="400" height="400"
                        style="display:<?php echo (isset($mode) && is_image($data["video"])) ? 'block' : 'none' ?>"
                        class="object-cover shadow rounded"> -->


                    <video src = "<?php echo (isset($mode) && !is_image($data["video"])) ? 'videos/' . $data["video"] : '' ?>" name="PreviewVideo" id="PreviewVideo" width="400" height="400" style="display:<?php echo (isset($mode) && !is_image($data["video"])) ? 'block' : 'none' ?>" class="object-cover shadow rounded" controls></video>


                    <div id="imgdiv" style="color:red"></div>


                    <input type="hidden" name="old_vid" id="old_vid"
                        value="<?php echo (isset($mode) && $mode == 'edit') ? $data["video"] : '' ?>" />


                </div>

        </div>
        <div class="relative inline-flex align-middle gap-3 mt-4 ">
            <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>" id="save"
                class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>">
                <?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
            </button>
            <button type="button" class="btn btn-danger" onclick="javascript:go_back()">Close</button>
        </div>
        </form>
    </div>
</div>
<script>
    function go_back() {
        eraseCookie("view_id");
        eraseCookie("edit_id");
        var loc = "videos.php";
        window.location = loc;
    }

    function readURL(input, preview) {
			if (input.files && input.files[0]) {
				var filename = input.files.item(0).name;
				var extn = filename.split(".").pop().toLowerCase();

				if (["jpg", "jpeg", "png", "bmp"].includes(extn)) {
					// Handle image preview
					console.log("image");
					displayImagePreview(input, preview);
				} else if (["mp4", "webm", "ogg"].includes(extn)) {
					// Handle video preview
					console.log("video");
					displayVideoPreview(input, preview);
				} else {
					// Display error message for unsupported file types
					$('#imgdiv').html("Unsupported file type. Please select an image or video.");
					document.getElementById('mediaPreviewContainer').style.display = "none";
				}
			}
		}
		function displayImagePreview(input, preview) {
			var reader = new FileReader();
			reader.onload = function(e) {
				document.getElementById('mediaPreviewContainer').style.display = "block";
				$('#PreviewMedia').attr('src', e.target.result);
				document.getElementById('PreviewMedia').style.display = "block";
				document.getElementById('preview_lable').style.display = "block";
				document.getElementById('PreviewVideo').style.display = "none";
			};
			reader.readAsDataURL(input.files[0]);
			$('#imgdiv').html("");
			document.getElementById('save').disabled = false;
		}
		function displayVideoPreview(input, preview) {
			var reader = new FileReader();
			reader.onload = function(e) {
				let file = input.files.item(0);
				let blobURL = URL.createObjectURL(file);
				document.getElementById('mediaPreviewContainer').style.display = "block";
				$('#PreviewVideo').attr('src', blobURL);
				document.getElementById('PreviewVideo').style.display = "block";

				document.getElementById('preview_lable').style.display = "block";
				document.getElementById('PreviewMedia').style.display = "none";
			};
			reader.readAsDataURL(input.files[0]);
			$('#imgdiv').html("");
			document.getElementById('save').disabled = false;
		}
</script>
<?php
include "footer.php";
?>