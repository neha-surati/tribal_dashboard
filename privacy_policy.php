<?php
//  BY ARYA 
include "header.php";

$stmt = $obj->con1->prepare("SELECT * FROM `privacy_policy`");
$stmt->execute();
$Resp = $stmt->get_result();
$stmt->close();
if ($Resp->num_rows > 0) {
    $data = $Resp->fetch_assoc();
    $mode = 'edit';
}

if (isset($_REQUEST['update'])) {
    $desc = $_REQUEST["description"];
    $status = (isset($_REQUEST["status"])) ? "enable" : "disable";
    try {
        $stmt = $obj->con1->prepare("UPDATE `privacy_policy` SET `description`=?,`status`=?");
        $stmt->bind_param("ss", $desc, $status);
        $Res = $stmt->execute();
        $stmt->close();
        if (!$Resp) {
            throw new Exception(
                "Problem in updating! " . strtok($obj->con1->error, "(")
            );
        }
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }
    if ($Res) {
        setcookie("msg", "update", time() + 3600, "/");
        setcookie("updateId", "", time() - 100, "/");
        header("location:privacy_policy.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:privacy_policy.php");
    }
}
if (isset($_REQUEST["save"])) {
    $desc = $_REQUEST["description"];
    $status = (isset($_REQUEST["status"])) ? "enable" : "disable";
    try {
        $stmt = $obj->con1->prepare("INSERT INTO `privacy_policy`(`description`, `status`) VALUES (?,?)");
        $stmt->bind_param("ss", $desc, $status);
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
        setcookie("msg", "data", time() + 3600, "/");
        header("location:privacy_policy.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:privacy_policy.php");
    }
}

?>

<div class='p-6'>
    <div class="flex gap-6 items-center pb-8">
        <!-- <span class="cursor-pointer">
            <a href="javascript:go_back()" class="text-3xl text-black dark:text-white">
                <i class="ri-arrow-left-line"></i>
            </a>
        </span> -->

        <h1 class="dark:text-white-dar text-3xl font-bold">Privacy Policy</h1>
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

                <div class="mb-4">
                    <label for="custom_switch_checkbox1">Status</label>
                    <label class="w-12 h-6 relative">
                        <input type="checkbox" class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer" id="status" name="status" <?php echo isset($mode) && $data['status'] == 'enable' ? 'checked' : '' ?>><span class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
                    </label>
                </div>
        </div>
        <div class="relative inline-flex align-middle gap-3 mt-4 ">
            <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>" id="save" class="btn btn-success" onclick="return setQuillInput()">Save</button>
            <button type="button" class="btn btn-danger" onclick="javascript:go_back()">Close</button>
        </div>
        </form>
    </div>
</div>
<script>
    checkCookies();

    function go_back() {
        eraseCookie("viewId");
        eraseCookie("updateId");
        var loc = "privacy_policy.php";
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
</script>
<?php
include "footer.php";
?>