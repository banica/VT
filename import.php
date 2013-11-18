
require_once('include/utils/utils.php');
require_once('include/logging.php');
require_once("config.php");

ini_set('memory_limit', '2048M');

$current_user = Users::getActiveAdminUser();

$file = $argv[1];

if(!file_exists($file) || !is_readable($file)) {
    echo "No suitable file specified" . PHP_EOL;
    die;
}

function csv_to_array($file='', $length = 0, $delimiter=';') {

    $header = NULL;
    $data = array();
    if (($handle = fopen($file, 'r')) !== FALSE) {
        while (($row = fgetcsv($handle, $length, $delimiter)) !== FALSE) {
            if(!$header) {
                $header = $row;
            } else {
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    }
    return $data;
}

include_once 'include/Webservices/Create.php';

foreach (csv_to_array($file) as $row) {
        //print_r($row);
        try {
            $row = vtws_create('Users', $row, $current_user);
            echo "User: " . $row['id'] . PHP_EOL;
        } catch (WebServiceException $ex) {
            $msg = $ex->getMessage();
            $msg .= print_r($row,true) . "\n";
            error_log($msg, 3, $file . "-error.log");
            echo $msg;
        }
}
