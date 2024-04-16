<?php
//  BY ARYA 
include "header.php";
if (isset($_COOKIE['edit_id'])) {
    $mode = 'edit';
    $editId = $_COOKIE['edit_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `about_us` WHERE id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE['view_id'])) {
    $mode = 'view';
    $viewId = $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `about_us` WHERE id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST['update'])) {
    $e_id = $_COOKIE['edit_id'];
    $desc = $_REQUEST['description'];
    $a_image = $_FILES['a_image']['name'];
    $a_image = str_replace(' ', '_', $a_image);
    $a_image_path = $_FILES['a_image']['tmp_name'];
    $old_img = $_REQUEST['old_img'];


    if ($a_image != "") {
		if (file_exists("images/aboutus_image/" . $a_image)) {
			$i = 0;
			$PicFileName = $a_image;
			$Arr1 = explode('.', $PicFileName);

			$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			while (file_exists("images/aboutus_image/" . $PicFileName)) {
				$i++;
				$PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
			}
		} else {
			$PicFileName = $a_image;
		}
		unlink("images/aboutus_image/" . $old_img);
		move_uploaded_file($a_image_path, "images/aboutus_image/" . $PicFileName);
	} else {
		$PicFileName = $old_img;
	}
    try {
        // echo ("UPDATE `about_us` SET `description`= $desc , `image`= $PicFileName WHERE `id`= $e_id");
        $stmt = $obj->con1->prepare("UPDATE `about_us` SET `description`=?,`image`=? WHERE `id`=?");
        $stmt->bind_param("ssi", $desc, $PicFileName, $e_id);
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
        header("location:about_us_details.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:about_us_details.php");
    }
}
if (isset($_REQUEST["save"])) {
    $desc = $_REQUEST['description'];
    $a_image = $_FILES['a_image']['name'];
    $a_image = str_replace(' ', '_', $a_image);
    $a_image_path = $_FILES['a_image']['tmp_name'];

    if ($a_image != "") {
        if (file_exists("images/aboutus_image/" . $a_image)) {
            $i = 0;
            $PicFileName = $a_image;
            $Arr1 = explode('.', $PicFileName);

            $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("images/aboutus_image/" . $PicFileName)) {
                $i++;
                $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $PicFileName = $a_image;
        }
    }

    try {
        // echo ("INSERT INTO `about_us`(`description`, `image`) VALUES ( $desc, $PicFileName)");
        $stmt = $obj->con1->prepare("INSERT INTO `about_us`(`description`, `image`) VALUES (?,?)");
        $stmt->bind_param("ss", $desc, $PicFileName);
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
        move_uploaded_file($a_image_path, "images/aboutus_image/" . $PicFileName);
        setcookie("msg", "data", time() + 3600, "/");
        header("location:about_us_details.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:about_us_details.php");
    }
}

?>

<div class='p-6'>
    <div class="flex gap-6 items-center pb-8">
        <span class="cursor-pointer">
            <a href="javascript:go_back()" class="text-3xl text-black dark:text-white">
                <i class="ri-arrow-left-line"></i>
            </a>
        </span>
        <h1 class="dark:text-white-dar text-2xl font-bold">About Us -
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post" enctype="multipart/form-data">

                <div class="mb-4">
                    <label for="quill">Description</label>
                    <div id="editor1">
                        <?php echo (isset($mode)) ? $data['description'] : '' ?>
                    </div>
                </div>
                <input type="hidden" id="description" name="description">

                <div <?php echo (isset($mode) && $mode == 'view') ? 'hidden' : '' ?>>
                    <label for="a_image">Image</label>
                    <input id="a_image" name="a_image" class="demo1" type="file" data_btn_text="Browse"
                        onchange="readURL(this,'PreviewImage')" onchange="readURL(this,'PreviewImage')"
                        placeholder="drag and drop file here" />
                </div>
                <div>
                    <h4 class="font-bold text-primary mt-2  mb-3"
                        style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">Preview</h4>
                    <img src="<?php echo (isset($mode)) ? 'images/aboutus_image/' . $data["image"] : '' ?>"
                        name="PreviewImage" id="PreviewImage" width="400" height="400"
                        style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>"
                        class="object-cover shadow rounded">
                    <div id="imgdiv" style="color:red"></div>
                    <input type="hidden" name="old_img" id="old_img"
                        value="<?php echo (isset($mode) && $mode == 'edit') ? $data["image"] : '' ?>" />
                </div>

        </div>
        <div class="relative inline-flex align-middle gap-3 mt-4 ">
            <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>"
                onclick="return setQuillInput()" id="save"
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
        var loc = "about_us_details.php";
        window.location = loc;
    }



    var quill = new Quill('#editor1', {
        theme: 'snow',
    });
    var toolbar = quill.container.previousSibling;
    toolbar.querySelector('.ql-picker').setAttribute('title', 'Font Size');
    toolbar.querySelector('button.ql-bold').setAttribute('title', 'Bold');
    toolbar.querySelector('button.ql-italic').setAttribute('title', 'Italic');
    toolbar.querySelector('button.ql-link').setAttribute('title', 'Link');
    toolbar.querySelector('button.ql-underline').setAttribute('title', 'Underline');
    toolbar.querySelector('button.ql-clean').setAttribute('title', 'Clear Formatting');
    toolbar.querySelector('[value=ordered]').setAttribute('title', 'Ordered List');
    toolbar.querySelector('[value=bullet]').setAttribute('title', 'Bullet List');


    function setQuillInput() {
        let quillInput = document.getElementById("description");
        quillInput.value = quill.root.innerHTML;
        let val1 = quillInput.value.replace(/<[^>]*>/g, '');

        if (val1.trim() == '') {
            coloredToast("danger", 'Please add something in Description.');
            return false;
        } else {
            return true;
        }
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
            } else {
                $('#imgdiv').html("Please Select Image Only");
                document.getElementById('save').disabled = true;
            }
        }
    }
</script>
<?php
include "footer.php";
?>