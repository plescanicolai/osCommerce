<?php


class FeedConnector implements FeedPlugin {

    /** @var  DataFeed */
    public $config;

    /**
     * constructor caller is forwarded
     *
     * @param Feed $container
     */
    public function __construct(Feed $container)
    {
        $this->config = $container->getShopContainer();
    }

    /**
     * Returns APIUsername
     * @return string
     */
    public function getApiUsername()
    {
        return $this->config->getUser();
    }

    /**
     * Return APIPassword
     * @return string
     */
    public function getApiPassword()
    {
        return $this->config->getPass();
    }

    /**
     * Returns APISecret code
     * @return string
     */
    public function getApiSecret()
    {
        return $this->config->getSecret();
    }

    /**
     * Returns identifyer (oxid, magento, opencart)
     * @return string
     */
    public function getShopName()
    {
        return 'oscommerce-2.3.4';
    }

    /**
     * Returns posible shop configuration option for different channels
     * @return stdClass
     */
    public function getShopConfig()
    {
        $oReturn = new stdClass();
        $oReturn->language = $this->config->getShopLanguageConfig();
        $oReturn->currency = $this->config->getShopCurrencyConfig();
        $oReturn->condition = $this->config->getShopConditionConfig();

        return $oReturn;
    }

    /**
     * Generates and returns the array of datafeed
     *
     * @param stdClass $queryParameters
     * @param array $fieldMap
     * @return stdClass
     */
    public function getFeed(stdClass $queryParameters, array $fieldMap)
    {
        set_time_limit(1800);

        header('Content-Encoding: UTF-8');
        header("Content-type: text/csv; charset=UTF-8");
        header('Content-Disposition: attachment; filename=oscommerceview.csv');
        mb_internal_encoding("UTF-8");
        $fh = fopen("php://output", 'w+');
        fputcsv($fh, array_keys($fieldMap), ';', '"');

        $specials = $this->config->getSpecials();
        $this->config->setLanguage($queryParameters->language);
        $this->config->setCurrencyParam($queryParameters->currency);
        $this->config->setStatus($queryParameters->condition);
        $this->config->initPreselectedFields();
        $sql = $this->config->getQuery($queryParameters->language);
        $result = tep_db_query($sql);

        while ($row = tep_db_fetch_array($result)) {
            if (isset($specials[$row['products_id']])) {
                $row = array_merge( $row, $specials[$row['products_id']] );
            }

            if ($attributes = $this->config->getCombinationsOfAttributes($row)){
                foreach ($attributes as $key => $attribute){
                    $row['attribute'] = $attribute;
                    $this->config->getFeedRow($fieldMap, $row, $fh);
                }
            }else{
                $this->config->getFeedRow($fieldMap, $row, $fh);
            }
        }
        fclose($fh);
    }

    /**
     * Returns the URL where to get generated DataFeed
     *
     * @param stdClass $queryParameters
     * @param array $fieldMap
     * @return string
     */
    public function getFeedUrl(stdClass $queryParameters, array $fieldMap = null)
    {
        // TODO: Implement getFeedUrl() method.
    }

    /**
     * Generates and returns the delta changes array
     *
     * @param stdClass $queryParameters
     * @param array $fieldMap
     * @param int $deltaTimestamp
     * @return stdClass
     */
    public function getDelta(stdClass $queryParameters, array $fieldMap, int $deltaTimestamp)
    {
        // TODO: Implement getDelta() method.
    }

    /**
     * Generates and returns the orders
     *
     * @param int $deltaTimestamp
     * @return stdClass
     */
    public function getOrders(int $deltaTimestamp)
    {
        // TODO: Implement getOrders() method.
    }

    /**
     * Returns the url from where to get the article
     *
     * @param int $deltaTimestamp
     * @return string
     */
    public function getOrdersUrl(int $deltaTimestamp)
    {
        // TODO: Implement getOrdersUrl() method.
    }

    /**
     * Returns the bridge URL throw the Feed is communicating with shop.
     *
     * @return string
     */
    public function getBridgeUrl()
    {
        return HTTP_SERVER . "/oscommerceview.php";
    }

    /**
     * Returns the bridge URL parameters the Feed is communicating with shop.
     *
     * @return string
     */
    public function getUrlParameters()
    {
        // TODO: Implement getUrlParameters() method.
    }

    /**
     * Returns posible shop fields configuration throw the Feed gets csv fields
     * @return stdClass
     */
    public function getShopFields()
    {
        return $this->config->pluginFields;
    }

    /**
     * Returns product info
     *
     * @param stdClass $queryParameters
     * @param array $fieldMap
     * @param string $id
     * @return mixed
     */
    public function getProductInfo(stdClass $queryParameters, array $fieldMap, $id)
    {
        if(!$id) {
            header('HTTP/1.0 404 Not Found');
            return false;
        }
        $productIds = explode('_',$id);
        $specials = $this->config->getSpecials();
        $this->config->setLanguage($queryParameters->lang);
        $this->config->setCurrencyParam($queryParameters->currency);
        $this->config->setStatus($queryParameters->condition);
        $query = $this->config->getQuery($queryParameters->language, $productIds[0]);
        $result = tep_db_query($query);
        if  ($row=tep_db_fetch_array($result)) {
            if (isset($specials[$row['products_id']])) {
                $row = array_merge($row, $specials[$row['products_id']]);
            }

            if ($combinations = $this->config->getCombinationsOfAttributes($row)){
                foreach ($combinations as $key => $values) {
                    $response = $productIds[0];
                    foreach ($values as $value) {
                        $response .= '_' . $value['products_options_value_id'];
                    }
                    if ($id == $response){
                        $row['attribute'] = $combinations[$key];
                        $article=$this->config->getFeedRow($fieldMap, $row);

                        return $article;
                    }
                }
            } elseif (count($productIds) == 1) {
                $article=$this->config->getFeedRow($fieldMap, $row);

                return $article;
            }
            header('HTTP/1.0 404 Not Found');
            return false;

        } else {
            header('HTTP/1.0 404 Not Found');
            return false;
        }
    }

    /**
     * @param stdClass $queryParameters
     * @param $id
     * @return mixed
     */
    public function getOrderProducts(stdClass $queryParameters, $id)
    {
        if(!$id || !is_numeric($id)) {
            header('HTTP/1.0 404 Not Found');
            return false;
        }
        $query = $this->config->getOrderProducts($id);
        $article = array();
        $i = 0;

        while ($row = tep_db_fetch_array($query)){
            $arrayAttr = $this->config->getAttributesOrder($id, $row['orders_products_id']);
            $article[$i]['ModelOwn'] = $row['products_id'];
            foreach ($arrayAttr as $key){
                $article[$i]['ModelOwn'] .= '_' . $key;
            }
            $article[$i]['Quantity'] = $row['products_quantity'];
            $article[$i]['BasePrice'] = round($row['products_price'] * $row['currency_value'], 2);
            $article[$i]['Currency'] = $row['currency'];
            $i++;
        }

        return $article;
    }

    /**
     * @return mixed
     */
    public function getFeatures()
    {
        $sRet = array (
            'getShopName',
            'getShopConfig',
            'getFeed',
            'getShopFields',
            'getBridgeUrl',
//            'getFeedUrl',
//            'getDelta',
//            'getOrders',
//            'getOrdersUrl',
            'getProduct',
            'getOrderProducts',
        );

        return $sRet;
    }
}