<?php
require('includes/application_top.php');
require('datafeed/datafeed.php');
require(DIR_WS_CLASSES . 'shipping.php');
$sFramework = dirname(__FILE__) . DIRECTORY_SEPARATOR . "datafeed" . DIRECTORY_SEPARATOR . "sdk" . DIRECTORY_SEPARATOR . "feed.php";

if(file_exists($sFramework)) {
    if (isset($_REQUEST['dataFeed']) && $request = $_REQUEST['dataFeed']) {
        include($sFramework);
        $sPluginName = "osdatafeed";
        $sPluginPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "datafeed" . DIRECTORY_SEPARATOR . "plugin" . DIRECTORY_SEPARATOR . $sPluginName . ".php";

        $plugin = new DataFeed();
        /**
         * @var $dataFeed Feed
         */
        $dataFeed = Feed::getInstance($sPluginPath, $plugin);
        $request = $_REQUEST['dataFeed'];
        $response = $dataFeed->dispatch($request);

        if ($request["fnc"] != "getFeed") {
            $response = (is_null(json_decode($response))) ? $response : json_decode($response);
            print_r($response);
        }
    } else {
        if (isset($_REQUEST['dataExport']) && $exportParam = $_REQUEST['dataExport']) {
            header('Location: http://daily-feed.com/export/' . $exportParam);
        } else {
            header('HTTP/1.0 404 Not Found');
        }
    }
}
exit();
