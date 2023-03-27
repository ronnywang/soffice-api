<?php

$types = array(
    'document' => 'Document',
    'spreadsheet' => 'Spreadsheet',
);
$output_types = array(
    'html' => 'HTML',
    'csv' => 'CSV',
    'xlsx' => 'xlsx',
    'txt' => 'txt',
);

if ($_FILES['file']) {
    if (!array_key_exists($_REQUEST['output_type'], $output_types)) {
        echo "wrong output type";
        exit;
    }

    putenv('HOME=/tmp/soffice');
    @mkdir('/tmp/soffice');
    $tmp_name = tempnam('/tmp/soffice', '');
    $output = tempnam('/tmp/soffice', '');
    $error = tempnam('/tmp/soffice', '');
    if (preg_match('#\.[a-z0-9]+$#', strtolower($_FILES['file']['name']), $matches)) {
        unlink($tmp_name);
        $tmp_name .= $matches[0];
    }
    error_log(json_encode($_FILES['file']));
    move_uploaded_file($_FILES['file']['tmp_name'], $tmp_name);

    unlink($output);
    $cmd = ("unoconv -o " . escapeshellarg($output) . " -f " . escapeshellarg($_REQUEST['output_type']) . " " . escapeshellarg($tmp_name) . " 2>" . escapeshellarg($error));
    error_log($cmd);
    system($cmd, $ret);

    header('Content-Type: application/json');
    if ($_GET['demo']) {
	    echo file_get_contents($output . '.' . $_REQUEST['output_type']);
    } else {
	    $obj = new StdClass;
	    echo json_encode(['info', ['output' => $output, 'output_type' => $_REQUEST['output_type']]]) . "\n";
	    echo json_encode(['content', base64_encode(file_get_contents($output . '.' . $_REQUEST['output_type']))]) . "\n";
	    foreach (glob($output . '_' . $_REQUEST['output_type'] . '_*') as $f) {
		    echo json_encode(['attachments', [
			    'file_name' => basename($f),
			    'content' => base64_encode(file_get_contents($f)),
		    ]]) . "\n";
	    }
	    echo json_encode(['error_code', $ret]) . "\n";
	    echo json_encode(['error', file_get_contents($error)]) . "\n";
    }

    if (false and !$_GET['nodelete']) {
        unlink($tmp_name);
	unlink($output);
	unlink($output . '.' . $_REQUEST['output_type']);
        unlink($error);
        system("rm {$output}_*");
    }
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>soffice converter</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.css">
</head>
<body>
<form method="post" action="?demo=1" enctype="multipart/form-data">
    File: <input type="file" name="file"><br>
    Output Type:
    <?php foreach ($output_types as $k => $v) { ?>
    <label><input type="radio" name="output_type" value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($v) ?></label>
    <?php } ?>
    <br>
    <button type="submit">Convert</button>
</form>
</body>
</html>
