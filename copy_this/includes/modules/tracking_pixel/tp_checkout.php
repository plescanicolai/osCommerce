<?php
    require_once (DIR_FS_CATALOG . "datafeed/datafeed.php");
    require_once (DIR_FS_CATALOG . "includes/classes/order.php");

    $config = new DataFeed();
    if ($config->getTrackpixel() == null)
    {
        exit;
    }

    $orderId = $orders['orders_id'];
    $order = new order($orderId);
    $pluginConfig = new DataFeed();
    $order_string = '';
    $lang = $_SESSION['languages_id'];
    foreach ($order as $key => $row){
        switch ($key) {
            case 'info':
                $orderSum = substr($row['total'], 1);
                $orderCurrency = $row['currency'];
                break;
            case 'products':
                foreach ($row as $index => $rowProduct){
                    $modelOwn = $rowProduct['id'];
                    $arrayAttr = $pluginConfig->getAttributesOrder($orderId, $rowProduct['id']);
                    foreach ($arrayAttr as $value){
                        $modelOwn .= "_" . $value;
                    }
                    $products_id = $pluginConfig->getTrackableID($modelOwn, $lang, $orderCurrency);
                    $order_string .= $products_id . '=' . $rowProduct['final_price'] . '=' . $rowProduct['qty'] . ';';
                }
                break;
        }
}
?>

<script type="text/javascript">
	var _feeparams = _feeparams || new Object();
	//Required clientId
	_feeparams.client = '<?php echo $config->getClientId(); ?>';
	//Required tracking type
	_feeparams.event = 'sale';
	_feeparams.orderid = '<?php echo $orderId ?>';
	//Required for tracking the sales (order sum)
	_feeparams.ordersum = '<?php echo $orderSum ?>';
	//Required for tracking the sales (order currency)
	_feeparams.ordercur = '<?php echo $orderCurrency ?>';
	//Optional you can add product information for better statistics product_code_1=sum_1=qty_1;product_code_2=sum_2=qty_2
	//Product code is the code you put to import feed for datafeed (unique product identifier)
	//the sum for particular product decimal seperator "."
	//Quantity the amount of particular products in order integer values default is 1
	_feeparams.products = '<?php echo $order_string ?>';
	//Additional parameters
	_feeparams.sparam = '';
	(function () {
		var head = document.getElementsByTagName('head')[0];
		var script = document.createElement('script');
		script.type = 'text/javascript';
		script.src = (location.protocol == "https:" ? "https:" : "http:") + '//daily-feed.com/bundles/managementtracking/js/pixel.js';
		// fire the loading
		head.appendChild(script);
	})();
</script>

