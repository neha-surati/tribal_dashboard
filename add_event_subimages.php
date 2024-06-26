<?php
// by nidhi
include "header.php";

$event_id = isset($_COOKIE['edit_id']) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];


$stmt_id = $obj->con1->prepare("SELECT event_name FROM event_category WHERE event_id = ?");
$stmt_id->bind_param('i', $event_id);
$stmt_id->execute();
$data_id = $stmt_id->get_result()->fetch_assoc();
$stmt_id->close();


if (isset($_COOKIE['edit_subimg_id'])) {
    $mode = 'edit';
    $editId = $_COOKIE['edit_subimg_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `event_images` WHERE img_id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE['view_subimg_id'])) {
    $mode = 'view';
    $viewId = $_COOKIE['view_subimg_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `event_images` WHERE img_id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["btn_submit"])) {
    try {
        // multiple event images 
        foreach ($_FILES["event_img"]['name'] as $key => $value) {
            // rename for event images       
            if ($_FILES["event_img"]['name'][$key] != "") {
                $PicSubImage = $_FILES["event_img"]["name"][$key];
                if (file_exists("images/event_image/" . $PicSubImage)) {
                    $i = 0;
                    $SubImageName = $PicSubImage;
                    $Arr = explode('.', $SubImageName);
                    $SubImageName = $Arr[0] . $i . "." . $Arr[1];
                    while (file_exists("images/event_image/" . $SubImageName)) {
                        $i++;
                        $SubImageName = $Arr[0] . $i . "." . $Arr[1];
                    }
                } else {
                    $SubImageName = $PicSubImage;
                }
                $SubImageTemp = $_FILES["event_img"]["tmp_name"][$key];
                $SubImageName = str_replace(' ', '_', $SubImageName);

                // sub images qry
                move_uploaded_file($SubImageTemp, "images/event_image/" . $SubImageName);

                // echo "INSERT INTO `event_images`(`event_id`, `img`) VALUES ('".$event_id."','".$SubImageName."')";
                $stmt_image = $obj->con1->prepare("INSERT INTO `event_images`(`event_id`, `img`) VALUES (?,?)");
                $stmt_image->bind_param("is", $event_id, $SubImageName);
                $Resp = $stmt_image->execute();
                $stmt_image->close();
            }
        }

        if (!$Resp) {
            throw new Exception(
                "Problem in adding! " . strtok($obj->con1->error, "(")
            );
        }
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        setcookie("msg", "data", time() + 3600, "/");
        header("location:add_event.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:add_event.php");
    }
}

if (isset($_REQUEST["btn_update"])) {
    $event_img_one = $_FILES['event_img_one']['name'];
    $event_img_one = str_replace(' ', '_', $event_img_one);
    $event_img_one_path = $_FILES['event_img_one']['tmp_name'];
    $old_img = $_REQUEST['old_img'];
    $id = $_COOKIE['edit_subimg_id'];

    //rename file for event image
    if ($event_img_one != "") {
        if (file_exists("images/event_image/" . $event_img_one)) {
            $i = 0;
            $PicFileName = $event_img_one;
            $Arr1 = explode('.', $PicFileName);

            $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            while (file_exists("images/event_image/" . $PicFileName)) {
                $i++;
                $PicFileName = $Arr1[0] . $i . "." . $Arr1[1];
            }
        } else {
            $PicFileName = $event_img_one;
        }
        unlink("images/event_image/" . $old_img);
        move_uploaded_file($event_img_one_path, "images/event_image/" . $PicFileName);
    } else {
        $PicFileName = $old_img;
    }

    try {
        $stmt = $obj->con1->prepare("UPDATE `event_images` SET `img`=? WHERE `img_id`=?");
        $stmt->bind_param("si", $PicFileName, $id);
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
        setcookie("edit_subimg_id", "", time() - 3600, "/");
        setcookie("msg", "update", time() + 3600, "/");
        header("location:add_event.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:add_event.php");
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
        <h1 class="dark:text-white-dar text-2xl font-bold">
            <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?> Event Images
        </h1>
    </div>

    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post" enctype="multipart/form-data">

                <div>
                    <label for="event_name">Event Name</label>
                    <input id="event_name" name="event_name" type="text" class="form-input" required value="<?= $data_id["event_name"] ?>" placeholder="Enter Event" readonly />
                </div>

                <div <?php echo (isset($mode)) ? 'hidden' : '' ?>>
                    <label for="image">Image</label>
                    <input id="event_img" class="demo1" type="file" name="event_img[]" multiple data_btn_text="Browse" onchange="readURL_multiple(this)" placeholder="drag and drop file here" multiple />
                    <div id="preview_image_div"></div>
                    <div id="imgdiv_multiple" style="color:red"></div>
                </div>

                <div <?php echo (isset($mode) && $mode == 'edit') ? '' : 'hidden' ?>>
                    <label for="image">Image</label>
                    <input id="event_img_one" class="demo1" type="file" name="event_img_one" data_btn_text="Browse" onchange="readURL(this,'PreviewImage')" placeholder="drag and drop file here" />
                </div>
                <div <?php echo (isset($mode)) ? '' : 'hidden' ?>>
                    <h4 class="font-bold text-primary mt-2  mb-3" style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>">Preview</h4>
                    <img src="<?php echo (isset($mode)) ? 'images/event_image/' . $data["img"] : '' ?>" name="PreviewImage" id="PreviewImage" width="400" height="400" style="display:<?php echo (isset($mode)) ? 'block' : 'none' ?>" class="object-cover shadow rounded">
                    <div id="imgdiv" style="color:red"></div>
                    <input type="hidden" name="old_img" id="old_img" value="<?php echo (isset($mode) && $mode == 'edit') ? $data["img"] : '' ?>" />
                </div>

                <div class="relative inline-flex align-middle gap-3 mt-4 ">
                    <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'btn_update' : 'btn_submit' ?>" id="save" class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>" <?php echo isset($mode) ? '' : 'onclick="return checkImage()"' ?>>
                        <?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
                    </button>
                    <button type="button" class="btn btn-danger" onclick="javascript:go_back()">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    checkCookies();

    function go_back() {
        eraseCookie("edit_subimg_id");
        eraseCookie("view_subimg_id");
        window.location = "add_event.php";
    }

    function readURL(input, preview) {
        if (input.files && input.files[0]) {
            var filename = input.files.item(0).name;

            var reader = new FileReader();
            var extn = filename.split(".");

            if (extn[1].toLowerCase() == "jpg" || extn[1].toLowerCase() == "jpeg" || extn[1].toLowerCase() == "png" || extn[1].toLowerCase() == "bmp") {
                reader.onload = function(e) {
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

    function readURL_multiple(input) {
        $('#preview_image_div').html("");
        var filesAmount = input.files.length;
        for (i = 0; i <= filesAmount; i++) {
            if (input.files && input.files[i]) {

                var filename = input.files.item(i).name;
                var reader = new FileReader();
                var extn = filename.split(".");
                if (extn[1].toLowerCase() == "jpg" || extn[1].toLowerCase() == "jpeg" || extn[1].toLowerCase() == "png" || extn[1].toLowerCase() == "bmp" || extn[1].toLowerCase() == "mp4" || extn[1].toLowerCase() == "webm" || extn[1].toLowerCase() == "ogg") {
                    if (extn[1].toLowerCase() == "jpg" || extn[1].toLowerCase() == "jpeg" || extn[1].toLowerCase() == "png" || extn[1].toLowerCase() == "bmp") {
                        reader.onload = function(e) {
                            $('#preview_image_div').append('<img src="' + e.target.result + '" name="PreviewImage' + i + '" id="PreviewImage' + i + '" width="400" height="400" class="object-cover shadow rounded" style="display:inline-block; margin:2%;">');
                        };
                    } else if (extn[1].toLowerCase() == "mp4" || extn[1].toLowerCase() == "webm" || extn[1].toLowerCase() == "ogg") {
                        reader.onload = function(e) {
                            $('#preview_image_div').append('<video src="' + e.target.result + '" name="PreviewVideo' + i + '" id="PreviewVideo' + i + '" width="400" height="400" style="display:inline-block" class="object-cover shadow rounded" controls></video>');
                        }
                    }

                    reader.readAsDataURL(input.files[i]);
                    $('#imgdiv_multiple').html("");
                    document.getElementById('save').disabled = false;
                } else {
                    $('#imgdiv_multiple').html("Please Select Image Or Video Only");
                    document.getElementById('save').disabled = true;
                }
            }
        }
    }
</script>

<?php
include "footer.php";
?>