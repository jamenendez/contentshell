<?php
require_once("common.php");
if (!authorized()) { exit(); }
$page_title = $lang['version'];
$page_script = "";
$page_nav = "version";
include "head.php";
?>

<script>
function selfUpdate() {

    var button = $("#updatebut");

    button.prop("disabled", true);
    $("#spinner").show();

    if (button.html() == "Check") {

        $.ajax({
            url: "background.php?selfUpdate=1&check=1",
            success: function(results) {
                $("#avail_contentshell").html(results.contentshell);
                // check we've got a decent looking version number
                if (results.contentshell.match(/^v\d+\.\d+\.\d+$/)) {
                    if (results.contentshell == $("#cur_contentshell").html()) {
                        button.css("color", "green");
                        button.html("&#10004; Success");
                        button.html("&#10004; Up to date");
                    } else {
                        button.html("Update");
                        button.prop("disabled", false);
                    }
                } else {
                    button.css("color", "#c00");
                    button.html("X Internal Error (1)");
                }
            },
            error: function() {
                button.css("color", "#c00");
                button.html("X Can't Connect");
            },
            complete: function() {
                $("#spinner").hide();
            }
        })

    } else if (button.html() == "Update") {

        $.ajax({
            url: "background.php?selfUpdate=1",
            success: function(results) {
                button.css("color", "green");
                button.html("&#10004; Up to date");
                $("#cur_contentshell").html(results.version);
            },
            error: function() {
                button.css("color", "#c00");
                button.html("X Internal Error (2)");
            },
            complete: function() {
                $("#spinner").hide();
            }
        });

    } else {
        // invalid
        button.prop("disabled", true);
        $("#spinner").hide();
    }

}
</script>

<?php

# this should work on debian variants
foreach (glob("/etc/*-release") as $filename) {
    $filecont = file_get_contents($filename);
    if (preg_match("/PRETTY_NAME=\"(.+?)\"/", $filecont, $matches)) {
        $os = $matches[1];
        break;
    }
}

# this should work on redhat variants
if (!isset($os)) {
    foreach (glob("/etc/*-release") as $filename) {
        $os = file_get_contents($filename);
        break;
    }
}

# this works on remaining unix systems (i.e. Mac OS)
if (!isset($os)) { $os = exec("uname -srmp"); }

# this gets the hardware version on rpi systems
$hardware = "";
unset($output, $matches);
exec("dmesg 2>&1 | grep 'Machine model'", $output);
if (isset($output[0]) && preg_match("/Machine model: (.+)/", $output[0], $matches)) {
    $hardware = $matches[1];
} else {
    exec("arch", $output);
    if ($output) {
        $hardware = $output[0];
    }
}

$rachel_installer_version = "?";
if (file_exists("/etc/rachelinstaller-version")) {
    $rachel_installer_version = file_get_contents("/etc/rachelinstaller-version");
}

$kalite_version = "?";
if (file_exists("/etc/kalite-version")) {
    $kalite_version = file_get_contents("/etc/kalite-version");
}

$kiwix_version = "?";
if (file_exists("/etc/kiwix-version")) {
    $kiwix_version = file_get_contents("/kiwix-version");
}

?>

<h2>RACHEL Version Info</h2>
<table class="version">
<tr><td>Hardware</td><td><?php echo $hardware ?></td></tr>
<tr><td>OS</td><td><?php echo $os ?></td></tr>
<tr><td>RACHEL Installer</td><td><?php echo $rachel_installer_version ?>*</td></tr>
<tr><td>KA Lite</td><td><?php echo $kalite_version ?>*</tr>
<tr><td>Kiwix</td><td><?php echo $kiwix_version ?>*</td></tr>
<tr><td>Content Shell</td><td>

    <div style="float: right; margin-left: 20px;">
        <div style="float: left; width: 24px; height: 24px; margin-top: 2px;">
            <img src="../art/spinner.gif" id="spinner" style="display: none;">
        </div>
        <button id="updatebut" onclick="selfUpdate();" style="margin-left: 5px;">Check</button>
    </div>

    Current: <span id="cur_contentshell">v2.1.0</span><br>
    Available: <span id="avail_contentshell"></span>
</td></tr>

<?php
    # get module info
    foreach (getmods_fs() as $mod) {
        echo "<tr><td>$mod[moddir]</td><td>$mod[version]</td></tr>\n";
    }
?>

</table>

<ul style="margin-top: 40px;">
<li>blank indicates the item predates versioning.</li>
<li>? indicates the version could not be determined, and perhaps the item is not actually installed</li>
<li>* indicates the version number was recorded at installation; if you have modified your installation this info may be out of date</li>
</ul>

</div>
</body>
</html>
