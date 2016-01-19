<?php

class DataFeed {

    /** @var  string */
    protected $user;
    /** @var  string */
    protected $password;
    /** @var  string */
    protected $secret;
    /** @var  int */
    protected $language;
    /** @var  array */
    protected $categories;
    /** @var  array */
    protected $categoryPaths;
    /** @var  int */
    protected $zoneId;
    /** @var  string */
    protected $deliveryTime;
    /** @var  shipping */
    protected $shipping;
    /** @var  currencies */
    protected $currency;
    /** @var  int */
    protected $condition;
    /** @var  array */
    protected $clientId;

    /******************************** new variables **************************************************/
    protected $pixelActive;
    public $preselectedField;
    public $defaultValues;
    public $systemFields;
    public $shippingFields;
    public $attributes;
    public $keys = array(
        'user',
        'pass',
        'secret',
        'tax_zone',
        'delivery_time',
        'ShippingAddition',
        'ean',
        'isbn',
        'subtitle',
        'uvp',
        'yatego',
        'google_cat',
        'base_unit',
        'weight',
        'coupon',
        'color',
        'size',
        'material',
        'gender',
        'packet_size',
        'country',
        'shipping_paypal_ost',
        'shipping_cod',
        'shipping_credit',
        'shipping_paypal',
        'shipping_transfer',
        'shipping_account',
        'shipping_debit',
        'shipping_moneybookers',
        'shipping_click_buy',
        'shipping_giropay',
        'shipping_comment',
        'trackpixel',
        'client_id'
    );

    public $pluginFields =  array (
        'ModelOwn'              => 'ModelOwn',
        'Name'                  => 'Name',
        'Subtitle'              => 'Subtitle',
        'Description'           => 'Description',
        'AdditionalInfo'        => 'AdditionalInfo',
        'Image'                 => 'Image',
        'Manufacturer'          => 'Manufacturer',
        'Model'                 => 'Model',
        'Category'              => 'Category',
        'CategoriesGoogle'      => 'CategoriesGoogle',
        'CategoriesYatego'      => 'CategoriesYatego',
        'ProductsEAN'           => 'ProductsEAN',
        'ProductsISBN'          => 'ProductsISBN',
        'Productsprice_brut'    => 'Productsprice_brut',
        'Productspecial'        => 'Productspecial',
        'Productsprice_uvp'     => 'Productsprice_uvp',
        'BasePrice'             => 'BasePrice',
        'BaseUnit'              => 'BaseUnit',
        'Productstax'           => 'Productstax',
        'ProductsVariant'       => 'ProductsVariant',
        'Currency'              => 'Currency',
        'Quantity'              => 'Quantity',
        'Weight'                => 'Weight',
        'AvailabilityTxt'       => 'AvailabilityTxt',
        'Condition'             => 'Condition',
        'Coupon'                => 'Coupon',
        'Gender'                => 'Gender',
        'Size'                  => 'Size',
        'Color'                 => 'Color',
        'Material'              => 'Material',
        'Packet_size'           => 'Packet_size',
        'DeliveryTime'          => 'DeliveryTime',
        'Shipping'              => 'Shipping',
        'ShippingAddition'      => 'ShippingAddition',
        'shipping_paypal_ost'   => 'shipping_paypal_ost',
        'shipping_cod'          => 'shipping_cod',
        'shipping_credit'       => 'shipping_credit',
        'shipping_paypal'       => 'shipping_paypal',
        'shipping_transfer'     => 'shipping_transfer',
        'shipping_debit'        => 'shipping_debit',
        'shipping_account'      => 'shipping_account',
        'shipping_moneybookers' => 'shipping_moneybookers',
        'shipping_giropay'      => 'shipping_giropay',
        'shipping_click_buy'    => 'shipping_click_buy',
        'shipping_comment'      => 'shipping_comment'
    );

    /**
     * @param $condition
     */
    public function setStatus($condition)
    {
        $this->condition = $condition;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->condition;
    }

    /**
     * @param $currencyId
     */

    public function setCurrencyParam($currencyId)
    {
        $currencyResult = tep_db_query("SELECT code, symbol_left, symbol_right FROM " . TABLE_CURRENCIES . " 
                                        WHERE currencies_id = '" . $currencyId . "' OR code = '" . $currencyId . "'" 
        );
        $this->currency = tep_db_fetch_array($currencyResult);
    }

    /**
     * @return mixed
     */
    public function getCurrencyParam()
    {
        return $this->currency;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    public function getSpecials()
    {
        $result = tep_db_query("SELECT s.products_id AS id, s.specials_new_products_price AS price, 
                                s.specials_date_added AS date, s.expires_date AS expireDate FROM " 
                                . TABLE_SPECIALS . 
                                " AS s WHERE s.status = 1"
        );
        $specials = array();
        while ($row = tep_db_fetch_array($result)) {
            $specials[$row['id']] = array (
                'specialPrice'      => $row['price'],
                'specialDateAdded'  => $row['date'],
                'specialExpireDate' => $row['expireDate']
            );
        }

        return $specials;
    }

    public function getQuery($lang, $productId = null)
    {
        $zoneId = $this->getZoneParams();
        $productsFields = $this->getProductsFields();
        $descriptionFields = $this->getProductsDescriptionFields();
        $status = $this->getStatus();

        switch ($status){
            case 0:
                $sqlPart = '1';
                break;
            case 1:
                $sqlPart = 'products.products_status = 1';
                break;
            case 2:
                $sqlPart = 'products.products_quantity > 0';
                break;
            case 3:
                $sqlPart = 'products.products_status = 1 AND products.products_quantity > 0';
                break;
            default :
                $sqlPart = '1';
                break;
        }

        $query = "SELECT DISTINCT ";
        foreach ($productsFields as $key => $value) {
            $query .= " products.{$value} AS '{$value}', ";
        }
        foreach ($descriptionFields as $key => $value) {
            $query .= "description.{$value} AS '{$value}', ";
        }

        $query .= "manufacturers.manufacturers_name AS manufacturersName,
                   products_categories.categories_id AS categoryId,
                   products.products_tax_class_id as TaxRateId, ";
        $query .= "'{$zoneId['localZoneId']}' AS zoneId, '{$zoneId['countryId']}' AS countryId ";

        $query .= "FROM " . TABLE_PRODUCTS . " AS products
            LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " description ON (products.products_id = description.products_id AND description.language_id = " . $lang . ")
            LEFT JOIN ". TABLE_MANUFACTURERS . " manufacturers ON manufacturers.manufacturers_id = products.manufacturers_id
            LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " products_categories ON products_categories.products_id = products.products_id
                        WHERE " . $sqlPart;

        if (isset($productId) && !empty($productId) && is_numeric($productId)) {
            $query .= " AND products.products_id = {$productId}";
        }

        return $query;
    }

    public function getCombinationsOfAttributes($product)
    {
        $products_options_name_query = tep_db_query("SELECT distinct
                                                   popt.products_options_id,
                                                   popt.products_options_name
                                              FROM " . TABLE_PRODUCTS_OPTIONS . " popt,
                                                   " . TABLE_PRODUCTS_ATTRIBUTES . " patrib
                                             WHERE patrib.products_id='" . $product['products_id'] . "'
                                               AND patrib.options_id = popt.products_options_id
                                               AND popt.language_id = '" . $this->getLanguage() . "'
                                          ORDER BY popt.products_options_id"
        );

        $row = 0;

        $products_options_data = array ();
        while ($products_options_name = tep_db_fetch_array($products_options_name_query,true))        {
            $products_options_query = tep_db_query("SELECT pov.products_options_values_id,
                                                pov.products_options_values_name,
                                                pa.*
                                           FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa,
                                                " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                                          WHERE pa.products_id = '" . $product['products_id'] . "'
                                            AND pa.options_id = '" . $products_options_name['products_options_id'] . "'
                                            AND pa.options_values_id = pov.products_options_values_id
                                            AND pov.language_id = '" . $this->getLanguage() . "'
                                       ORDER BY pov.products_options_values_id
                                        ");
            $col = 0;
            while ($products_options = tep_db_fetch_array($products_options_query, true)) {

                $products_options_data[$row][$col] = array (
                    'products_options_value_id' => $products_options['products_options_values_id'],
                    'products_options_id' => $products_options['options_id'],
                    'values_name' => $products_options['products_options_values_name'],
                    'values_price' => $products_options['options_values_price'],
                    );
                $col++;
            }
            $row++;
        }

        return $this->array_comb($products_options_data);
    }

    /**
     * @param $data
     * @param $arrayWithAttr
     * @return array|string
     */
    public function getAttributesForOneProduct($data, $arrayWithAttr)
   {
        $productId = $arrayWithAttr[0];
        array_shift($arrayWithAttr);
        $queryString = '';

        if (count($arrayWithAttr)){
            foreach ($arrayWithAttr as $attributeId){
                $queryString .= $attributeId . ",";
            }
            $query = tep_db_query("SELECT pov.products_options_values_id,
                                                    pov.products_options_values_name,
                                                    pa.*
                                               FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa,
                                                    " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                                              WHERE pa.products_id = '" . $productId . "'
                                                AND pa.options_values_id = pov.products_options_values_id
                                                AND	pa.options_values_id IN (" . substr($queryString, 0, -1) . ")
                                           ORDER BY pa.options_id"
            );

            $col = 0;
            $products_options_data = array();
            while ($products_options = tep_db_fetch_array($query, true)) {
                    $products_options_data[$col] = array (
                            'products_options_value_id' => $products_options['products_options_values_id'],
                            'products_options_id' => $products_options['options_id'],
                            'values_name' => $products_options['products_options_values_name'],
                            'values_price' => $products_options['options_values_price'],
                            );
                    $col++;
              }

            return $products_options_data;
        }

        return '';
    }

    /**
     * @param $arrays
     * @return array
     */
    public function array_comb($arrays)
    {
        $result = array();
        $arrays = array_values($arrays);
        $sizeIn = sizeof($arrays);
        $size = $sizeIn > 0 ? 1 : 0;
        foreach ($arrays as $array)
            $size = $size * sizeof($array);
        for ($i = 0; $i < $size; $i ++)
        {
            $result[$i] = array();
            for ($j = 0; $j < $sizeIn; $j ++)
                array_push($result[$i], current($arrays[$j]));
            for ($j = ($sizeIn -1); $j >= 0; $j --)
            {
                if (next($arrays[$j]))
                    break;
                elseif (isset ($arrays[$j]))
                    reset($arrays[$j]);
            }
        }
        return $result;
    }

    /**
     * @param $id
     * @return bool|mysqli_result|resource
     */
    public function getOrderProducts($id)
    {
        $query = "
            SELECT DISTINCT op.orders_products_id, op.products_id, op.products_price, op.products_quantity,o.currency,o.currency_value
            FROM " . TABLE_ORDERS . " o
            LEFT JOIN " . TABLE_ORDERS_PRODUCTS . " op ON (o.orders_id = op.orders_id)
            WHERE o.orders_id=" . $id;
        $resource = tep_db_query($query);

        return $resource;
    }

    /**
     * @param $orderId
     * @param $productId
     * @return array
     */
    public function getAttributesOrder($orderId, $productId)
    {
        $query = tep_db_query("SELECT products_options_values_id
                                           FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " as pov
                                           RIGHT JOIN " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " as opa
                                           ON pov.products_options_values_name = opa.products_options_values
                                          WHERE orders_products_id = '" . $productId . "' AND orders_id='" . $orderId . "'"
        );
        $row = array();
        while ($products_options = tep_db_fetch_array($query, true)) {
            $row[] = $products_options['products_options_values_id'];
        }

        return $row;
    }

    /**
     * @param $fieldMap
     * @param $data
     * @param null $fh
     * @return array
     */
    public function getFeedRow($fieldMap, $data , $fh=null)
    {
        $data['Productstax'] = tep_get_tax_rate($data['TaxRateId'], $data['countryId'], $data['zoneId']);
        $row = array();

        foreach($fieldMap as $key => $value) {
            $row[$key] = str_replace(array("\r", "\r\n", "\n"), '', mb_convert_encoding($this->getFeedColumnValue($value, $data), 'UTF-8'));
        }
        if (isset($fh)){
            fputcsv($fh, $row, ';', '"');
            flush();
        }

        return $row;
    }

    /**
     * @param $field
     * @param $data
     * @return string
     */
    protected function getFeedColumnValue($field, $data ) {
        switch($field) {
            case 'ModelOwn' :
                return $this->getModelOwn($data);
                break;
            case 'Model' :
                return $data['products_model'];
                break;
            case 'ProductsVariant' :
                return $data['products_id'];
                break;
            case 'ProductsEAN' :
                $response = $this->prepareExportField('ean', $data);
                return $response->value;
                break;
            case 'ProductsISBN' :
                $response = $this->prepareExportField('isbn', $data);
                return $response->value;
                break;
            case 'Name' :
                return strip_tags($data['products_name']);
                break;
            case 'Subtitle' :
                $response = $this->prepareExportField('subtitle', $data);
                return strip_tags($response->value);
                break;
            case 'Description':
                return strip_tags($data['products_description']);
                break;
            case 'Manufacturer':
                return $data['manufacturersName'];
            case 'Image':
                if (isset($data['products_image']) && !empty($data['products_image'])) {
                    return HTTP_SERVER . "/images/" . $data['products_image'];
                } else {
                    return '';
                }
                break;
            case 'AdditionalInfo':
                return HTTP_SERVER . "/product_info.php?products_id=" . $data['products_id'];
                break;
            case 'Category':
                return $this->getCategoryPath($data['categoryId'], $this->getLanguage());
                break;
            case 'YategoCat':
                $response = $this->prepareExportField('yatego', $data);
                return $response->value;
                break;
            case 'CategoriesGoogle':
                $response = $this->prepareExportField('google_cat', $data);
                return $response->value;
                break;
            case 'Productsprice_brut':
                if (isset($data['specialPrice'])) {
                    return $this->getPrice($data['specialPrice'], $data['Productstax'], $this->getCurrency());
                } else {
                    return $this->getPrice($data['products_price'], $data['Productstax'],$this->getCurrency(), $data['attribute']);
                }
                break;
            case 'Productspecial':
                if (isset($data['specialPrice'])) {
                    return $this->getPrice($data['products_price'], $data['Productstax'], $this->getCurrency());
                } else {
                    return '';
                }
                break;
            case 'Weight':
                return $data['products_weight'];
                break;
            case 'Productstax':
                return $data['Productstax'];
                break;
            case 'Productsprice_uvp':
                $response = $this->prepareExportField('uvp', $data);
                return $response->value;
                break;
            case 'BasePrice':
                if (isset($data['specialPrice'])) {
                    return $this->getPrice($data['specialPrice'], 0, $this->getCurrency());
                } else {
                    return $this->getPrice($data['products_price'], 0, $this->getCurrency());
                }
                break;
            case 'BaseUnit' :
                $response = $this->prepareExportField('base_unit', $data);
                return $response->value;
                break;
            case 'Currency':
                $ret = $this->getCurrency();
                return $ret['code'];
                break;
            case 'Quantity':
                return $data['products_quantity'];
                break;
            case 'DeliveryTime':
                $response = $this->prepareExportField('delivery_time', $data);
                if ( $response->value) {
                    return $response->value;
                } else {
                    return $this->getDeliveryTime();
                    break;
                }
            case 'AvailabilityTxt':
                return $this->getAvailabilityTxt($data);
                break;
            case 'Condition':
                if ($this->preselectedField['MODULE_DATAFEED_DATAFEED_CONDITION']) {
                    return $this->preselectedField['MODULE_DATAFEED_DATAFEED_CONDITION'];
                } elseif (($this->preselectedField['MODULE_DATAFEED_DATAFEED_CONDITION_INPUT'])) {
                    return $this->preselectedField['MODULE_DATAFEED_DATAFEED_CONDITION_INPUT'];
                }
                return '';
                break;
            case 'Coupon':
                $response = $this->prepareExportField('coupon', $data);
                return $response->value;
                break;
            case 'Shipping':
                return $this->getShippingPrice($data['products_weight'] ,$this->getCurrency());
                break;
            case 'shipping_paypal_ost':
                return $this->prepareShippingField('shipping_paypal_ost', $data);
                break;
            case 'shipping_cod':
                return $this->prepareShippingField('shipping_cod', $data);
                break;
            case 'shipping_credit':
                return $this->prepareShippingField('shipping_credit', $data);
                break;
            case 'shipping_paypal':
                return $this->prepareShippingField('shipping_paypal', $data);
                break;
            case 'shipping_transfer':
                return $this->prepareShippingField('shipping_transfer', $data);
                break;
            case 'shipping_debit':
                return $this->prepareShippingField('shipping_debit', $data);
                break;
            case 'shipping_moneybookers':
                return $this->prepareShippingField('shipping_moneybookers', $data);
                break;
            case 'shipping_giropay':
                return $this->prepareShippingField('shipping_giropay', $data);
                break;
            case 'shipping_click_buy':
                return $this->prepareShippingField('shipping_click_buy', $data);
                break;
            case 'shipping_comment':
                return $this->prepareShippingField('shipping_comment', $data);
                break;
            case 'shipping_account':
                return $this->prepareShippingField('shipping_account', $data);
                break;
            case 'ShippingAddition':
                return $this->prepareShippingField('ShippingAddition', $data);
                break;
            case 'Size':
                $response = $this->prepareExportField('Size', $data);
                return $response->value;
                break;
            case 'Color':
                $response = $this->prepareExportField('Color', $data);
                return $response->value;
                break;
            case 'Material':
                $response = $this->prepareExportField('Material', $data);
                return $response->value;
                break;
            case 'Gender':
                $response = $this->prepareExportField('Gender', $data);
                return $response->value;
                break;
            case 'Packet_size':
                $response = $this->prepareExportField('Packet_size', $data);
                return $response->value;
                break;
            default:
                if (isset($data[$field])) {
                    return $data[$field];
                } else {
                    return '';
                }
                break;
        }
    }

    /**
     * @param $article
     * @return string
     */
    public function getModelOwn($article)
    {
        $response = $article['products_id'];

        if ($attributeCombination = $article['attribute']) {
            foreach ($attributeCombination as $arrayValue ){
                $response .= '_' . $arrayValue['products_options_value_id'];
            }
        }
        return $response;
    }

    /**
     * @param $name
     * @param $article
     * @return stdClass
     */
    public function prepareExportField($name, $article)
    {
        $response = new stdClass();
        $rez = array();
        $response->key = 0;
        $response->value = '';
        $attributeCombination = $article['attribute'];
        $article[$name . "_field"] = $this->getFromConfigurationTable($name . '_field');

        if (!empty($article[$name . "_field"]) && $article[$name . "_field"] != -1){
            $querry = explode(';', $article[$name . "_field"]);
            switch($querry[1]){
                case 'categories':
                    $result = tep_db_query("SELECT cat." . $querry[0] . " as value from " . $querry[1] . " as cat
                                RIGHT JOIN products_to_categories as ptc
                                ON cat.categories_id = ptc.categories_id
                               WHERE ptc.products_id =" . $article['products_id']);
                    break;
                case 'manufacturers':
                    $result = tep_db_query("SELECT man." . $querry[0] . " as value from " . $querry[1] . " as man
                                RIGHT JOIN products as p
                                ON man.manufacturers_id = p.manufacturers_id
                               WHERE p.products_id =" . $article['products_id']);
                    break;
                case 'products':
                case 'products_description':
                    $result = tep_db_query("SELECT tab." . $querry[0] . " as value from " . $querry[1] . " as tab WHERE tab.products_id =" . $article['products_id']);
                    break;
                default:
                    $result = '';
                    break;
            }

            $rez = tep_db_fetch_array($result);
            if (!empty($rez['value'])){
            $response->value = $rez['value'];}
        }
        if (!empty($rez['value'])){
        $response->value = $rez['value'];
        }elseif (count($attributeCombination) && $variantId = $this->getFromConfigurationTable($name . '_attribute')){
            foreach ($attributeCombination as $arrayValue){
                if ($arrayValue['products_options_id'] == $variantId){
                    $response->value = $arrayValue['values_name'];
                }
            }
        } elseif ($selectedDefaultValue = $this->getFromConfigurationTable($name . '_default')){
            $response->value = $selectedDefaultValue;
            $response->key = 1;
        } elseif ($input = $this->getFromConfigurationTable($name . '_input')){
            $response->value = $input;
        }

        return $response;
    }

    /**
     * @param $article
     * @return string
     */
    public function getAvailabilityTxt($article)
    {
        if (isset($article['products_status']) && $article['products_status'] > 0)
        {
            if ($article['products_ordered'] > 0)
            {
                return "3";
            }
            elseif($article['products_quantity'] > 0)
            {
                return "2";
            }
                return "1";
        }

        return "0";
    }

    /**
     * @param $name
     * @param $data
     * @return string
     */
    public function prepareShippingField($name, $data)
    {
        global $total_weight;
        $response = '';
        $total_weight = $data['products_weight'];
        $id = $this->getFromConfigurationTable($name . '_field');
        $shipping = $this->getShipping();
        $x = $shipping->quote();

        if(isset($id) && isset($x[$id]['methods'][0]['cost']))
            $response = $x[$id]['methods'][0]['cost'];
        elseif ($input = $this->getFromConfigurationTable($name . '_input')){
            $response = $input;
        }

        return $response;
    }

    /**
     * @return stdClass
     */
    public function getShopLanguageConfig()
    {
        $oConfig = new stdClass();
        $aLanguages = $this->getLanguages();
        $oConfig->key = "lang";
        $oConfig->title = "language";
        foreach ($aLanguages as $id => $language) {
            $oValue = new stdClass();
            $oValue->key = $id;
            $oValue->title = $language;
            $oConfig->values[] = $oValue;
        }

        return $oConfig;
    }

    /**
     * @return stdClass
     */
    public function getShopCurrencyConfig()
    {
        $oConfig = new stdClass();
        $aCurrencies = $this->getCurrencies();
        $oConfig->key = "currency";
        $oConfig->title = "currency";
        foreach($aCurrencies as $id => $value) {
            $oValue = new stdClass();
            $oValue->key = $id;
            $oValue->title = $value;
            $oConfig->values[] = $oValue;
        }

        return $oConfig;
    }

    public function getShopConditionConfig()
    {
        $oConfig = new stdClass();
        $oConfig->key = "status";
        $oConfig->title = "Status";
        $oValue = new stdClass();
        $oValue->key = 0;
        $oValue->title = 'export_all_products';
        $oConfig->values[]=$oValue;
        $oValue = new stdClass();
        $oValue->key = 1;
        $oValue->title = 'export_active_products';
        $oConfig->values[]=$oValue;
        $oValue = new stdClass();
        $oValue->key = 2;
        $oValue->title = 'export_products_in_stock';
        $oConfig->values[]=$oValue;
        $oValue = new stdClass();
        $oValue->key = 3;
        $oValue->title = 'export_active_products_in_stock';
        $oConfig->values[]=$oValue;

        return $oConfig;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setInConfigurationTable($key, $value)
    {
        tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
                            VALUES ('" . $key . "','" . $value . "', '1', '1','', now() )");
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getFromConfigurationTable($key)
    {
        $key = 'MODULE_DATAFEED_' . strtoupper($key);
        $query = tep_db_query("SELECT configuration_value
                                  FROM " . TABLE_CONFIGURATION . "
                                 WHERE configuration_key = '" . $key . "'");
        $rez = tep_db_fetch_array($query);

        return $rez['configuration_value'];
    }

    public function remove()
    {
        tep_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_DATAFEED%' ");
    }

    public function install()
    {
        foreach ($_POST as $key => $value){
            $this->setInConfigurationTable('MODULE_DATAFEED_' . strtoupper($key), $value);
            $this->keys .= 'MODULE_DATAFEED_' . strtoupper($key) . ';';
        }
    }

    public function initPreselectedFields()
    {
        $query = tep_db_query("SELECT configuration_key, configuration_value
                                  FROM " . TABLE_CONFIGURATION . "
                                 WHERE configuration_key LIKE 'MODULE_DATAFEED%'");
        while ($row = tep_db_fetch_array($query)){
            $this->preselectedField[$row['configuration_key']] = $row['configuration_value'];
        };
    }

    /**
     * @param $langId
     */
    public function getOptionsForForm($langId)
    {
        $query = tep_db_query("SELECT popt.products_options_id as value,
                               popt.products_options_name as name
                          FROM " . TABLE_PRODUCTS_OPTIONS . " popt
                         WHERE popt.language_id = '" . $langId . "'
                      ORDER BY popt.products_options_id"
        );

        $array_options = array();
        while ($row = tep_db_fetch_array($query, true)){
            array_push($array_options, $row);
        }

        $this->attributes =  $array_options;
    }

    /**
     * @param $name
     * @param $const
     * @param $inputType
     * @param $defaultType
     * @param $attributeType
     * @param $fieldType
     * @param $required
     * @param int $shipping
     * @param string $type
     */
    public function formField($name, $const, $inputType, $defaultType, $attributeType, $fieldType, $required, $shipping = 0, $type = 'text')
    {
        if ($required) {
            $required = "*";
        } else {
            $required = '';
        }

        echo "<tr><td class='table-row-min-w'>" . $const . $required . "</td>";

        if ($fieldType) {
            echo "<td><select class='input-format' name='" . $name . "_field' >";
            $ok = true;
            $tablesCopy = $shipping ? $this->shippingFields : $this->systemFields;
            foreach ($tablesCopy as $key => $field) {
                if (isset($this->preselectedField[strtoupper('module_datafeed_' . $name . '_field')])
                    && $this->preselectedField[strtoupper('module_datafeed_' . $name . '_field')] == $key
                ) {
                    echo "<option value=\"" . $key . "\">" . $field . "</option>";
                    unset($tablesCopy[$key]);
                    $ok = false;
                }
            }

            if ($ok) echo "<option value='-1'>-----</option>";

            foreach ($tablesCopy as $key => $field) {
                echo "<option value=\"" . $key . "\">" . $field . "</option>";
            }
            if (!$ok) echo "<option value='-1'>-----</option>";
            echo "</select></td>";
        }

        if ($attributeType) {
            echo "<td><select class='input-format' name='" . $name . "_attribute'>";
            $ok = true;
            $tablesCopy = $this->attributes;
            foreach ($tablesCopy as $key => $attribute) {
                if ($this->preselectedField[strtoupper('module_datafeed_' . $name . '_attribute')] == $attribute['value']) {
                    echo "<option value=\"" . $attribute['value'] . "\">" . $attribute['name'] . "</option>";
                    unset($tablesCopy[$key]);
                    $ok = false;
                }
            }

            if ($ok) echo "<option value='-1'>-----</option>";

            foreach ($tablesCopy as $attribute) {
                echo "<option value=\"" . $attribute['value'] . "\">" . $attribute['name'] . "</option>";
            }
            if (!$ok) echo "<option value='-1'>-----</option>";
            echo "</select></td>";
        }


        if ($defaultType && isset($this->defaultValues[$name])) {
            echo "<td><select class='input-format' name='" . $name . "_default'>";
            $ok = true;
            foreach ($this->defaultValues[$name] as $key => $value) {
                if ($this->preselectedField[strtoupper('module_datafeed_' . $name . '_default')] == $key) {
                    echo "<option value=\"" . $key . "\">" . $value . "</option>";
                    unset($this->defaultValues[$name][$key]);
                    $ok = false;
                }
            }

            if ($ok && $const != 'ModelOwn: ') {
                echo "<option value='-1'>-----</option>";
            }

            foreach ($this->defaultValues[$name] as $key => $value) {
                echo "<option value=\"" . $key . "\">" . $value . "</option>";
            }
            if (!$ok) {
                echo "<option value='-1'>-----</option>";
            }
            echo "</select></td>";
        }

        if ($inputType && $type == 'textarea') {
            echo "<td><textarea class='input-format' rows='4' name='" . $name . "_input' style='max-width: 66%;' cols='50'>" .
                $this->preselectedField[strtoupper('module_datafeed_' . $name . 'input')] . "</textarea></td>";
        } else if ($inputType && $type == 'checkbox') {
            if ($this->preselectedField[strtoupper('module_datafeed_' . $name . '_input')] != null) {
                $str = 'checked';
            } else {
                $str = 'checked';
            }
            echo "<td><input class='input-format' type='" . $type . "' name='" . $name . "_input' style='margin-left: 2px; max-width: 66%;'
                value='1' " . $str . "></td>";
        } else if ($inputType) {
            echo "<td><input class='input-format' type='" . $type . "' name='" . $name . "_input' style='margin-left: 2px; max-width: 66%;'
                value='" . $this->preselectedField[strtoupper('module_datafeed_' . $name . '_input')] . "'></td>";
        }

        echo "</tr>";
    }

    public function productCondition()
    {
        $new = $used = $refurbished = '';
        switch ((int)$this->preselectedField['MODULE_DATAFEED_DATAFEED_CONDITION']) {
            case 1:
                $new = ' selected';
                break;
            case 2:
                $used = ' selected';
                break;
            case 3:
                $refurbished = ' selected';
                break;
        }

        echo '<tr><td>' . DATAFEED_CONDITION . '</td>
            <td>
            <select class=\'input-format\' name="datafeed_condition">
                <option value 0 >-----</option>
                <option value="1" ' . $new . '>' . CONDITION_NEW . '</option>
                <option value="2" ' . $used . '>' . CONDITION_USED . '</option>
                <option value="3" ' . $refurbished . '>' . CONDITION_REFURBISHED . '</option>
            </select>
        </td>
        <td><input class=\'input-format\' type="text" name="datafeed_condition_input" style="margin-left: 2px;max-width: 66%;"
        value=' . $this->preselectedField['MODULE_DATAFEED_DATAFEED_CONDITION_INPUT'] . '></td>
    </tr>';
    }

    public function initCountries()
    {
        $rez = array();
        $query = tep_db_query("SELECT countries_id as id,countries_name as name FROM " . TABLE_COUNTRIES);
        while ($row = tep_db_fetch_array($query)){
            $rez[$row['id']] = $row['name'];
        };

        $this->defaultValues['country'] = $rez;
    }

    public function initZones()
    {
        $rez = array();
        $query = tep_db_query("SELECT geo_zone_id as id, geo_zone_name as name FROM ". TABLE_GEO_ZONES);
        while ($row = tep_db_fetch_array($query)){
            $rez[$row['id']] = $row['name'];
        };

        $this->defaultValues['tax_zone'] = $rez;
    }

    /**
     * @param $code
     */
    public function initShippingStatusArray($code)
    {
        $rez = array();
        $query = tep_db_query("SELECT shipping_status_name as name
                                 FROM shipping_status s
                            LEFT JOIN languages l ON(l.languages_id=s.language_id)
                                WHERE l.code ='" . $code . "'");
        while ($row = tep_db_fetch_array($query)){
            $rez[$row['name']] = $row['name'];
        };

        $this->defaultValues['delivery_time'] = $rez;
    }

    public function initTablesColumns()
    {
        $rez = array();
        $query = tep_db_query("SHOW COLUMNS FROM " . TABLE_PRODUCTS);
        while ($row = tep_db_fetch_array($query)){
            $rez[$row['Field'].';' . TABLE_PRODUCTS] = $row['Field'] . "--[" . TABLE_PRODUCTS . "]";
        };

        $query = tep_db_query("SHOW COLUMNS FROM " . TABLE_PRODUCTS_DESCRIPTION);
        while ($row = tep_db_fetch_array($query)){
            $rez[$row['Field'].';' . TABLE_PRODUCTS_DESCRIPTION] = $row['Field']."--[" . TABLE_PRODUCTS_DESCRIPTION . "]";
        };

        $query = tep_db_query("SHOW COLUMNS FROM " . TABLE_MANUFACTURERS);
        while ($row = tep_db_fetch_array($query)){
            $rez[$row['Field'] . ';' . TABLE_MANUFACTURERS] = $row['Field'] . "--[" . TABLE_MANUFACTURERS . "]";
        };

        $query = tep_db_query("SHOW COLUMNS FROM " . TABLE_CATEGORIES);
        while ($row = tep_db_fetch_array($query)){
            $rez[$row['Field'] . ';' . TABLE_CATEGORIES] = $row['Field'] . "--[" . TABLE_CATEGORIES . "]";
        };

        $this->systemFields = $rez;
    }

    public function initShipping()
    {
        $rez = array();
        if (defined('MODULE_SHIPPING_INSTALLED') && tep_not_null(MODULE_SHIPPING_INSTALLED)) {
            $rez = explode(';', MODULE_SHIPPING_INSTALLED);
        }
        foreach ($rez as $key => $value){
            $rez[$key] = str_replace('.php', '', $value);
        }
        $this->shippingFields = $rez;
    }

    public function getTrackableName()
    {
        return array(
            'ModelOwn' => $this->getFromConfigurationTable('ModelOwn_default')
        );
    }

    /**
     * @param $id
     * @param $lang
     * @param $currencyId
     * @return mixed
     */
    public function getTrackableID($id, $lang, $currencyId)
    {
        $field = array();
        $productIds = explode('_', $id);
        $this->setLanguage($lang);
        $this->setCurrencyParam($currencyId);
        $specials = $this->getSpecials();
        $query = $this->getQuery($lang, $productIds[0]);
        $result = tep_db_query($query);

        if ($row = tep_db_fetch_array($result)) {
            if (isset($specials[$row['products_id']])) {
                $row = array_merge( $row, $specials[$row['products_id']] );
            }

            $row['attribute'] = $this->getAttributesForOneProduct($row, $productIds);
            $field = $this->getFeedRow($this->getTrackableName(), $row);
        }

        return $field['ModelOwn'];
    }

    public function getClientId()
    {
        return $this->getFromConfigurationTable("client_id_input");
    }

    public function getTrackpixel()
    {
        return $this->getFromConfigurationTable("trackpixel_input");
    }

    public function getUser()
    {
        return $this->getFromConfigurationTable("user_input");
    }

    public function getPass()
    {
        return $this->getFromConfigurationTable("pass_input");
    }

    public function getSecret()
    {
        return $this->getFromConfigurationTable("secret_input");
    }

    //--------------------------------------- old functions ------------------------------

     /* @return currencies
     */
    public function getCurrency()
    {
        if (!isset($this->currency)) {
            $this->currency = new currencies();
        }

        return $this->currency;
    }

    /**
     * @return mixed
     */
    public function getShipping()
    {
        if (!isset($this->shipping)) {
            $this->shipping = new shipping();
        }

        return $this->shipping;
    }

    /**
     * @return string
     */
    public function getDeliveryTime()
    {
        if (isset($this->deliveryTime)) {
            return $this->deliveryTime;
        }
        $fromSelect = tep_db_query("SELECT configuration_value FROM ". TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_DATAFEED_FROMVALUE'");
        $from = tep_db_fetch_array($fromSelect);
        $toSelect = tep_db_query("SELECT configuration_value FROM ". TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_DATAFEED_TOVALUE'");
        $to = tep_db_fetch_array($toSelect);
        $periodSelect = tep_db_query("SELECT configuration_value FROM ". TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_DATAFEED_TYPEVALUE'");
        $period = tep_db_fetch_array($periodSelect);

        $return = '';
        if (isset($from['configuration_value'])) {
            $return = $from['configuration_value'] . '_';
        }
        if (isset($to['configuration_value'])) {
            $return .= $to['configuration_value'] . '_';
        }
        if (isset($period['configuration_value'])) {
            $return .= strtolower($period['configuration_value']);
        } else {
            $return .= 'd';
        }

        return $return;
    }

    /**
     * @param string $deliveryTime
     */
    public function setDeliveryTime($deliveryTime)
    {
        $result = tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, sort_order, date_added)
                        VALUES ('datafeed_delivery_time', 'MODULE_DATAFEED_DELIVERY', '" . $deliveryTime . "','Delivery time', 2, now())");
        if ($result !== false) {
            $this->deliveryTime = $deliveryTime;
        } else {
            $this->deliveryTime = '';
        }
    }

    /**
     * @return int
     */
    public function getZoneId()
    {
        if (isset($this->zoneId)) {
            return $this->zoneId;
        }
        $result = tep_db_query("SELECT configuration_value FROM ". TABLE_CONFIGURATION ." WHERE configuration_key = 'MODULE_DATAFEED_ZONE'");
        $row = tep_db_fetch_array($result);
        if (isset($row['configuration_value']) && !empty($row['configuration_value'])) {
            return $row['configuration_value'];
        } else {
            return 0;
        }
    }

    /**
     * @param int $zoneId
     */
    public function setZoneId($zoneId)
    {
        $result = tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, sort_order, date_added)
                        VALUES ('datafeed_zone','MODULE_DATAFEED_ZONE', '" . $zoneId . "', 'Zone ID', 2, now())");
        if ($result !== false) {
            $this->zoneId = $zoneId;
        } else {
            $this->zoneId = 0;
        }
    }

    public function getZoneParams()
    {
        $taxZoneId = $this->getZoneId();
        $result = tep_db_query("SELECT zone_country_id AS countryId, zone_id AS localZoneId FROM " . TABLE_ZONES_TO_GEO_ZONES . "
                                WHERE geo_zone_id = {$taxZoneId}");

        $zone = tep_db_fetch_array($result);
        if (isset($zone) && !empty($zone)) {
            return $zone;
        } else {
            return array(
                'countryId'     =>  0,
                'localZoneId'   =>  0,
            );
        }
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data['zoneId'] = $this->getZoneId();

        $deliveryTime = $this->getDeliveryTime();
        $deliveryTime = explode('_', $deliveryTime);

        $data['fromValue'] = $deliveryTime[0];
        if (is_numeric($deliveryTime[1])) {
            $data['toValue'] = $deliveryTime[1];
            $data['typeValue'] = $deliveryTime[2];
        } else {
            $data['toValue'] = $data['fromValue'];
            $data['typeValue'] = $deliveryTime[1];
        }

        return $data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        if (isset($data['zone'])) {
            $this->setZoneId($data['zone']);
        }
        if (isset($data['fromValue']) && isset($data['typeValue'])) {
            if (empty($data['fromValue'])) {
                $data['fromValue'] = '0';
            }

            if (empty($data['toValue'])) {
                $data['toValue'] = $data['fromValue'];
            }
            if ($data['fromValue'] != $data['toValue']) {
                $deliveryTime = implode('_', array($data['fromValue'], $data['toValue'], $data['typeValue']));
            } else {
                $deliveryTime = implode('_', array($data['fromValue'], $data['typeValue']));
            }

            $this->setDeliveryTime($deliveryTime);
        }
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        $result = tep_db_query("SELECT languages_id, name FROM languages");
        $languages = array();
        while($language = tep_db_fetch_array($result)) {
            $languages[$language['languages_id']] = $language['name'];
        }

        return $languages;
    }

    /**
     * @return array
     */
    public function getCurrencies()
    {
        $result = tep_db_query("SELECT currencies_id, title FROM currencies");
        $currencies = array();
        while($currency = tep_db_fetch_array($result)) {
            $currencies[$currency['currencies_id']] = $currency['title'];
        }

        return $currencies;
    }

    /**
     * @return array
     */
    public function getShopFields()
    {
        $fields = $this->getProductsFields();
        $fieldsArray = array();
        foreach ($fields as $key => $value ) {
            $fieldsArray[] = TABLE_PRODUCTS . '_' . $value;
        }
        $fields = $this->getProductsDescriptionFields();
        foreach ($fields as $key => $value) {
            $fieldsArray[] = TABLE_PRODUCTS_DESCRIPTION . '_' . $value;
        }

        return $fieldsArray;
    }

    /**
     * @return array
     */
    public function getProductsFields()
    {
        $result = tep_db_query("SHOW COLUMNS FROM " . TABLE_PRODUCTS);

        $fieldsArray = array();
        while ($row = tep_db_fetch_array($result)) {
            $fieldsArray[] = $row['Field'];
        }

        return $fieldsArray;
    }

    /**
     * @return array
     */
    public function getProductsDescriptionFields()
    {
        $result = tep_db_query("SHOW COLUMNS FROM " . TABLE_PRODUCTS_DESCRIPTION);

        $fieldsArray = array();
        while ($row = tep_db_fetch_array($result)) {
            $fieldsArray[] = $row['Field'];
        }

        return $fieldsArray;
    }

    /**
     * @param int $languageId
     * @return array
     */
    public function getCategories($languageId)
    {
        if (isset($this->categories)) {
            return $this->categories;
        }
        $query = tep_db_query("SELECT * FROM " . TABLE_CATEGORIES_DESCRIPTION . " WHERE language_id = " . $languageId);
        $cats = array();
        while ($cat = tep_db_fetch_array($query)) {
            $cats[] = $cat;
        }
        $categories = array();
        foreach ($cats as $key => $value) {
            $categories[$value['categories_id']] = $value['categories_name'];
        }
        $this->categories = $categories;

        return $this->categories;
    }

    /**
     * @param int $categoryId
     * @param int $languageId
     * @return string
     */
    public function getCategoryPath($categoryId, $languageId)
    {
        if (isset($this->categoryPaths[$categoryId]) && !empty($this->categoryPaths[$categoryId])) {
            return $this->categoryPaths[$categoryId];
        }
        $originalCategoryId = $categoryId;
        $categories = $this->getCategories($languageId);
        $categoryPath = $categories[$categoryId];
        $result = tep_db_query("SELECT parent_id FROM " . TABLE_CATEGORIES . " WHERE " . TABLE_CATEGORIES . " . categories_id = {$categoryId}");

        while ( ($categoryId = tep_db_fetch_array($result)) && $categoryId['parent_id'] != 0 ) {
            $categoryId = $categoryId['parent_id'];

            if (isset($this->categoryPaths[$categoryId]) && !empty($this->categoryPaths[$categoryId])) {
                $categoryPath = $this->categoryPaths[$categoryId] . '| '. $categoryPath;
                break;
            } else {
                $categoryPath = $categories[$categoryId] . '|' . $categoryPath;
            }
        }
        $this->categoryPaths[$originalCategoryId] = $categoryPath;

        return $categoryPath;
    }

    /**
     * @return array
     */
    public function getZones()
    {
        $result = tep_db_query("SELECT geo_zone_name AS zoneName, geo_zone_id AS zoneId FROM " . TABLE_GEO_ZONES);
        $zones = array();
        while ($zone = tep_db_fetch_array($result)) {
            $zones[$zone['zoneId']] = $zone;
        }

        return $zones;
    }
     /**
     * @param mixed|string $weight
     * @param array $requestCurrency
     * @return int|mixed|string
     */
    public function getShippingPrice($weight, $requestCurrency)
    {
        global $total_weight;
        $total_weight = $weight;
        $shipping = $this->getShipping();
        $shipping->quote();
        $price = $shipping->cheapest();

        if (isset($price['cost']) && !empty($price['cost'])) {
            $price = $price['cost'];
        } else {
            $price = 0;
        }

        if (isset($requestCurrency['symbol_left']) && !empty($requestCurrency['symbol_left'])) {
            $price = str_replace($requestCurrency['symbol_left'], '', $price);
        }
        if (isset($requestCurrency['symbol_right']) && !empty($requestCurrency['symbol_right'])) {
            $price = str_replace($requestCurrency['symbol_right'], '', $price);
        }

        return $price;
    }

    /**
     * @param mixed|string $price
     * @param int|string $tax
     * @param array $requestCurrency
     * @param array $attribute
     * @return mixed|string
     */
    public function getPrice($price, $tax, $requestCurrency, $attribute = array())
    {
        $currencies = $this->getCurrency();
        if ($tax > 0) {
            $price = $price + tep_round($price * $tax / 100, $currencies->currencies[$requestCurrency['code']]['decimal_places']);
        }
        if (count($attribute)){
            foreach ($attribute as $arrayValue ){
                $price += $arrayValue['values_price'];
            }
        }
        if (isset($requestCurrency['symbol_left']) && !empty($requestCurrency['symbol_left'])) {
            $price = str_replace($requestCurrency['symbol_left'], '', $price);
        }
        if (isset($requestCurrency['symbol_right']) && !empty($requestCurrency['symbol_right'])) {
            $price = str_replace($requestCurrency['symbol_right'], '', $price);
        }

        return $price;
    }

}
