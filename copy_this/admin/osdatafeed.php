<?php
    require("includes/configure.php");
    require(DIR_WS_INCLUDES . "application_top.php");
    require(DIR_WS_INCLUDES . "template_top.php");

    $dataFeedPath = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "datafeed" . DIRECTORY_SEPARATOR;
    require( $dataFeedPath . "datafeed.php");

    $dataFeedConfig = new DataFeed();

    $data = $dataFeedConfig->getData();
    $zones = $dataFeedConfig->getZones();
    $zoneId = $dataFeedConfig->getZoneId();

    $dataFeedConfig->getOptionsForForm($_SESSION['languages_id']);
    $dataFeedConfig->initCountries();
    $dataFeedConfig->initTablesColumns();
    $dataFeedConfig->initShipping();

    $dataFeedConfig->initZones();

    $dataFeedConfig->defaultValues['condition'] = array(
        CONDITION_NEW => CONDITION_NEW, 
        CONDITION_USED => CONDITION_USED
    );

    $dataFeedConfig->defaultValues['ModelOwn'] = $dataFeedConfig->pluginFields;

if (isset($_GET['action']) && $_GET['action'] == 'saveForm') {
        $data = $_POST;
        $dataFeedConfig->setData($data);
        $zoneId = $dataFeedConfig->getZoneId();

        $dataFeedConfig->remove();
        $dataFeedConfig->install();

    $sPath =$dataFeedPath . "sdk" . DIRECTORY_SEPARATOR . "feed.php";
        if(!file_exists($sPath)) {
            echo "<p>SDK NOT FOUND</p>";
            return false;
        }
        require_once($sPath);

        $sPluginName = "osdatafeed";
        $sPluginPath = $dataFeedPath . "plugin" . DIRECTORY_SEPARATOR . $sPluginName . ".php";

        /** @var Feed $dataFeed */
        $dataFeed = Feed::getInstance($sPluginPath,  $dataFeedConfig);

        $oRegisterEvent = new FeedEvent();
        Feed::getInstance($sPluginPath, $dataFeedConfig)->eventManager->dispatchEvent("onRegisterFeed",  $oRegisterEvent);

        $success = false;
        $message = '';
        if($oRegisterEvent->getResponse()->getStatus() == 204) {
            $success = true;
        } else {
            $success = false;
            $message = $oRegisterEvent->getResponse()->getStatusMsg();
        }

        if ($success) {
            $message = DATAFEED_SUCCES . $message;
        } else {
            $message = DATAFEED_ERROR . $message;
        }
    }
    $dataFeedConfig->initPreselectedFields();
?>

<style type="text/css">
    body { font: normal 12px Tahoma; padding: 15px; }
    input { border: 1px solid black; width: 150px;}
    .title{ padding: 15px; font: bold 22px Tahoma }
    .error {padding: 15px; font-weight: bold; color: red;}
    .success {text-align: center;background-color: greenyellow; font-size: 24px}
    .save-button {padding:5px 15px; background:#ccc; border:0 none;
        cursor:pointer;
        -webkit-border-radius: 5px;
        border-radius: 5px; }
    .input-format {padding:5px; border:2px solid #ccc;-webkit-border-radius: 5px;border-radius: 5px; width: 70%}
    .input-format:focus {border-color:#333; }
</style>

<body marginwidth="0" marginheight="0">
<br>
    <div class="" style="border: 1px solid #CCCCCC;">
        <form name="myedit" id="myedit" action="osdatafeed.php?action=saveForm" method="post">
            <table border="0" cellspacing="15">
                <tbody>
                    <tr>
                        <td colspan="4" class="<?php echo ($success) ? 'success' : 'error'; ?>"><?php echo $message ?></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: center;font-weight: bold;"><?php echo DATAFEED_MAIN; ?></td>
                    </tr>
                    <?php
                        $dataFeedConfig->formField('user', DATAFEED_USER, 1, 0, 0, 0, 1);
                        $dataFeedConfig->formField('pass', DATAFEED_PASS, 1, 0, 0, 0, 1, 'password');
                        $dataFeedConfig->formField('secret', DATAFEED_SECRET, 1, 0, 0, 0, 1, 'password');
                    ?>
                    <tr><td colspan="5"><hr></td></tr>
                    <tr>
                        <td colspan="2" style="text-align: center;font-weight: bold;"><?php echo DATAFEED_TRACKING_PIXEL; ?></td>
                    </tr>
                    <?php
                    $dataFeedConfig->formField('trackpixel',  DATAFEED_ENABLE_TRACKING, 1, 0, 0, 0, 0, 0, 'checkbox');
                    $dataFeedConfig->formField('ModelOwn', DATAFEED_MODELOWN, 0, 1, 0, 0, 0);
                    $dataFeedConfig->formField('client_id', DATAFEED_CLIENT_ID, 1, 0, 0, 0, 0);
                    ?>
                    <tr><td colspan="5"><hr></td></tr>
                    <tr>
                        <td colspan="2" style="text-align: center;font-weight: bold;"><?php echo DATAFEED_FIELDS; ?></td>
                    </tr>
                    <?php
                        $dataFeedConfig->formField('ean', DATAFEED_EAN, 0, 1, 1, 1, 0);
                        $dataFeedConfig->formField('isbn', DATAFEED_ISBN, 0, 1, 1, 1, 0);
                        $dataFeedConfig->formField('subtitle', DATAFEED_SUBTITLE, 0, 1, 1, 1, 0);
                        $dataFeedConfig->formField('uvp', DATAFEED_MANUFACTURER_UVP, 0, 1, 1, 1, 0);
                        $dataFeedConfig->formField('yatego', DATAFEED_YATEGO_ID, 0, 0, 1, 1, 0);
                        $dataFeedConfig->formField('base_unit', DATAFEED_BASE_UNIT, 0, 0, 1, 1, 0);
                    ?>
                    <tr><td colspan="5"><hr></td></tr>
                    <tr>
                        <td colspan="2" style="text-align: center;font-weight: bold;"><?php echo DATAFEED_ATTRIBUTES_LABEL; ?></td>
                    </tr>
                    <?php
                        $dataFeedConfig->formField('coupon', DATAFEED_COUPON, 0, 0, 1, 1, 0);
                        $dataFeedConfig->formField('color', DATAFEED_COLOR, 0, 0, 1, 1, 0);
                        $dataFeedConfig->formField('size', DATAFEED_SIZE, 0, 0, 1, 1, 0);
                        $dataFeedConfig->formField('material', DATAFEED_MATERIAL, 0, 0, 1, 1, 0);
                        $dataFeedConfig->formField('gender', DATAFEED_GENDER, 0, 0, 1, 1, 0);
                        $dataFeedConfig->formField('packet_size', DATAFEED_PACKET_SIZE, 0, 0, 1, 1, 0);
                        $dataFeedConfig->productCondition();
                    ?>
                    <tr><td colspan="5"><hr></td></tr>
                    <tr>
                        <td colspan="2" style="text-align: center;font-weight: bold;"><?php echo DATAFEED_SHIPPING_LABEL; ?></td>
                    </tr>
                    <?php
                        $dataFeedConfig->formField('country', DATAFEED_COUNTRY, 0, 1, 0, 0, 1);?>
                    <tr>
                        <td><?php echo DATAFEED_TAX_ZONE ?></td>
                        <td>
                            <select class='input-format' name="zone">
                                <option value=0 <?php if ($zoneId == 0) echo 'selected="selected" '?> >No Tax</option>
                                <?php
                                    if (isset($zones) && !empty($zones)) {
                                        foreach($zones as $key => $zone) {
                                            $option = "<option ";
                                            if ($key == $zoneId) {
                                                $option .= 'selected="selected" ';
                                            }
                                            $option .= 'value="' . $zone['zoneId'] . '">' . $zone['zoneName'] . '</option>';
                                            echo $option;
                                        }
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <?php
                        $dataFeedConfig->formField('shippingaddition', DATAFEED_SHIPPING_ADDITION, 1, 0, 0, 1, 0, 1);
                        $dataFeedConfig->formField('shipping_paypal_ost', DATAFEED_SHIPPING_OST, 1, 0, 0, 1, 0, 1);
                        $dataFeedConfig->formField('shipping_cod', DATAFEED_SHIPPING_COD, 1, 0, 0, 1, 0, 1);
                        $dataFeedConfig->formField('shipping_credit', DATAFEED_SHIPPING_CREDIT, 1, 0, 0, 1, 0, 1);
                        $dataFeedConfig->formField('shipping_paypal', DATAFEED_SHIPPING_PAYPAL, 1, 0, 0, 1, 0, 1);
                        $dataFeedConfig->formField('shipping_transfer', DATAFEED_SHIPPING_TRANSFER, 1, 0, 0, 1, 0, 1);
                        $dataFeedConfig->formField('shipping_account', DATAFEED_SHIPPING_ACCOUNT, 1, 0, 0, 1, 0, 1);
                        $dataFeedConfig->formField('shipping_debit', DATAFEED_SHIPPING_DEBIT, 1, 0, 0, 1, 0, 1);
                        $dataFeedConfig->formField('shipping_moneybookers', DATAFEED_SHIPPING_MONEYBOOKERS, 1, 0, 0, 1, 0, 1);
                        $dataFeedConfig->formField('shipping_click_buy', DATAFEED_SHIPPING_CLICK_BUY, 1, 0, 0, 1, 0, 1);
                        $dataFeedConfig->formField('shipping_giropay', DATAFEED_SHIPPING_GIRO_PAY, 1, 0, 0, 1, 0, 1);
                        $dataFeedConfig->formField('shipping_comment', DATAFEED_SHIPPING_COMMENT, 1, 0, 0, 1, 0, 1, 'textarea');
                    ?>
                    <tr><td colspan="5"><hr></td></tr>
                    <tr>
                        <td colspan="2" style="text-align: center; font-weight: bold;"><?php echo DATAFEED_DELIVERY_TIME_LABEL ?></td>
                    </tr>
                    <?php
                        $dataFeedConfig->formField('delivery_time', DATAFEED_DELIVERY_TIME, 0, 0, 1, 1, 0);
                    ?>
                    <tr>
                        <td><?php echo DATAFEED_FROM ?></td>
                        <td>
                            <input class='input-format' style="max-width: 66%" type="number" name="fromValue" value="<?php echo $data['fromValue']?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo DATAFEED_TO ?></td>
                        <td>
                            <input class='input-format' style='max-width: 66%' type="number" name="toValue" value="<?php echo $data['toValue']?>">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo DATAFEED_PERIOD; ?></td>
                        <td>
                            <select class='input-format' name="typeValue">
                                <option <?php if ($data['typeValue'] == "D") echo "selected=\"selected\"" ?> value="D">Days</option>
                                <option <?php if ($data['typeValue'] == "W") echo "selected=\"selected\"" ?> value="W">Weeks</option>
                            </select>
                        </td>
                    </tr>
                    <tr><td colspan="5"><hr></td></tr>
                     <tr>
                        <td colspan="2">
                            <input class="save-button" type="submit" name="save" value="<?php echo DATAFEED_REGISTER; ?>" >
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
</body>
