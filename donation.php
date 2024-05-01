<?php
include "header.php";

if (isset($_COOKIE["view_id"])) {
    $mode = 'view';
    $viewId = $_COOKIE["view_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `donation` where id=?");
    $stmt->bind_param('i', $viewId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_COOKIE["edit_id"])) {
    $mode = 'edit';
    $editId = $_COOKIE["edit_id"];
    $stmt = $obj->con1->prepare("SELECT * FROM `donation` where id=?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $Resp = $stmt->get_result();
    $data = $Resp->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $firstname = $_REQUEST["firstname"];
    $lastname = $_REQUEST["lastname"];
    $phone_no = $_REQUEST["phone_no"];
    $email = $_REQUEST["email"];
    $country = $_REQUEST["country"];
    $state = $_REQUEST["state"];
    $city = $_REQUEST["city"];
    $reason = $_REQUEST["reason"];
    $currency = $_REQUEST["currency"];
    $amount = $_REQUEST["amount"];

    // echo "INSERT INTO `donation`(`firstname`, `lastname`,`phone_no`, `email`, `country_id`, `state_id`, `city_id`, `reason`, `currency`, `amount`) VALUES ($firstname, $lastname, $phone_no, $email, $country, $state, $city, $reason, $currency, $amount)";
    try {
        $stmt = $obj->con1->prepare(
            "INSERT INTO `donation`(`firstname`, `lastname`,`phone_no`, `email`, `country_id`, `state_id`, `city_id`, `reason`, `currency`, `amount`) VALUES (?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param("ssisiiisss", $firstname, $lastname, $phone_no, $email, $country, $state, $city, $reason, $currency, $amount);
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
        header("location:donation_details.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:donation_details.php");
    }
}

if (isset($_REQUEST["update"])) {
    $firstname = $_REQUEST["firstname"];
    $lastname = $_REQUEST["lastname"];
    $phone_no = $_REQUEST["phone_no"];
    $email = $_REQUEST["email"];
    $country = $_REQUEST["country"];
    $state = $_REQUEST["state"];
    $city = $_REQUEST["city"];
    $reason = $_REQUEST["reason"];
    $currency = $_REQUEST["currency"];
    $amount = $_REQUEST["amount"];
    $editId = $_COOKIE["edit_id"];

    try {
        $stmt = $obj->con1->prepare(
            "UPDATE `donation` SET `firstname`=?, `lastname`=?, `phone_no`=?, `email`=?, `country_id`=?,`state_id`=?,`city_id`=?, `reason`=?, `currency`=?, `amount`=? WHERE `id`=?"
        );
        // echo "UPDATE `donation` SET `firstname`='$firstname', `lastname`='$lastname', `phone_no`='$phone_no', `email`='$email', `country_id`='$country',`state_id`='$state',`city_id`=$city, `reason`='$reason', `currency`='$currency', `amount`='$amount' WHERE `id`='$editId'";
        $stmt->bind_param("ssisiiisssi", $firstname, $lastname, $phone_no, $email, $country, $state, $city, $reason, $currency, $amount, $editId);

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
        setcookie("edit_id", "", time() - 3600, "/");
        setcookie("msg", "data", time() + 3600, "/");
        header("location:donation_details.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:donation_details.php");
    }
}

?>

<div class='p-6'>
    <div class='flex items-center mb-3'>
        <span class="cursor-pointer">
            <a href="javascript:go_back()" class="text-3xl text-black dark:text-white">
                <i class="ri-arrow-left-line"></i>
            </a>
        </span>
        <h1 class="dark:text-white-dar text-2xl font-bold">Donation - <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>
        </h1>
    </div>
    <div class="panel mt-6">
        <div class="mb-5">
            <form class="space-y-5" method="post">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="firstname">First Name</label>
                        <input id="firstname" name="firstname" type="text" class="form-input" placeholder="Enter your first name" value="<?php echo (isset($mode)) ? $data['firstname'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="lastname">Last Name</label>
                        <input id="lastname" name="lastname" type="text" class="form-input" placeholder="Enter your last name" value="<?php echo (isset($mode)) ? $data['lastname'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="phone_no">Phone Number</label>
                        <div>
                            <div class="flex">
                                <input id="phone_no" name="phone_no" type="text" placeholder="Enter Phone Number" class="form-input" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="10" value="<?php echo (isset($mode)) ? $data['phone_no'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input id="email" name="email" type="text" class="form-input" placeholder="Enter your Email" value="<?php echo (isset($mode)) ? $data['email'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                    <div>
                        <label for="inputCountry">Country</label>
                        <select class="form-select" name="country" id="country" <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> required onchange="fillState(this.value)">
                            <option value=""> Choose Country </option>
                            <?php
                            $stmt = $obj->con1->prepare("SELECT * FROM countries");
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $stmt->close();

                            while ($result = mysqli_fetch_array($Resp)) {
                            ?>
                                <option value="<?php echo $result["id"]; ?>" <?php echo (isset($mode) && $data["country_id"] == $result["id"]) ? "selected" : ""; ?>>
                                    <?php echo $result["name"]; ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <div>
                            <label for="groupFname">State Name</label>
                            <select class="form-select text-black" name="state" id="state" <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> required onchange="fillCity(this.value)">
                            <?php
                            $stmt = $obj->con1->prepare("SELECT * FROM states where country_id = ? ");
                            $stmt->bind_param("i",$data["country_id"]);
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $stmt->close();

                            while ($result = mysqli_fetch_array($Resp)) {
                            ?>
                                <option value="<?php echo $result["id"]; ?>" <?php echo (isset($mode) && $data["state_id"] == $result["id"]) ? "selected" : ""; ?>>
                                    <?php echo $result["name"]; ?>
                                </option>
                            <?php
                            }
                            ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="city">City</label>
                        <select class="form-select text-black" name="city" id="city" <?php echo isset($mode) && $mode == 'view' ? 'disabled' : '' ?> required>
                        <?php
                            $stmt = $obj->con1->prepare("SELECT * FROM cities where state_id = ?");
                            $stmt->bind_param("i",$data["state_id"]);
                            $stmt->execute();
                            $Resp = $stmt->get_result();
                            $stmt->close();

                            while ($result = mysqli_fetch_array($Resp)) {
                            ?>
                                <option value="<?php echo $result["id"]; ?>" <?php echo (isset($mode) && $data["city_id"] == $result["id"]) ? "selected" : ""; ?>>
                                    <?php echo $result["name"]; ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>

                </div>
                <div>
                    <label for="exampleInputReason">Reason</label>
                    <textarea class="form-input" name="reason" id="message" cols="30" rows="9" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> placeholder="Enter Message"><?php echo (isset($mode)) ? $data['reason'] : '' ?></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10">
                    <div>
                        <label for="currency">Currency</label>
                        <input id="currency" name="currency" type="text" class="form-input" placeholder="Enter your currency" value="<?php echo (isset($mode)) ? $data['currency'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                    <div>
                        <label for="amount">Amount</label>
                        <input id="amount" name="amount" type="text" class="form-input" placeholder="Enter amount" value="<?php echo (isset($mode)) ? $data['amount'] : '' ?>" required <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?> />
                    </div>
                </div>
                <div class="relative inline-flex align-middle gap-3 mt-4 ">
                    <button type="submit" name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>" id="save" class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'hidden' : '' ?>">
                        <?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
                    </button>
                    <button type="button" class="btn btn-danger" onclick="javascript:go_back()">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    // $(document).ready(function() {
    //     eraseCookie("edit_id");
    //     eraseCookie("view_id");
    // });
    checkCookies();

    function fillCity(stid) {
        const xhttp = new XMLHttpRequest();
        xhttp.open("GET", "getcities.php?sid=" + stid);
        xhttp.send();
        xhttp.onload = function() {
            document.getElementById("city").innerHTML = xhttp.responseText;
        }
    }

    function fillState(cntrid) {
        const xhttp = new XMLHttpRequest();
        xhttp.open("GET", "getstate.php?cntrid=" + cntrid);
        xhttp.send();
        xhttp.onload = function() {
            document.getElementById("state").innerHTML = xhttp.responseText;
        }
    }

    function go_back() {
        eraseCookie("edit_id");
        eraseCookie("view_id");
        window.location = "donation_details.php";
    }
</script>

<?php
include "footer.php";
?>