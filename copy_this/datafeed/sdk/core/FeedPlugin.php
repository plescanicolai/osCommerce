<?php

interface FeedPlugin
{
    /**
     * constructor caller is forwarded
     *
     * @param Feed $container
     */
    public function __construct(Feed $container);

    /**
     * Returns APIUsername
     * @return string
     */
    public function getApiUsername();

    /**
     * Return APIPassword
     * @return string
     */
    public function getApiPassword();

    /**
     * Returns APISecret code
     * @return string
     */
    public function getApiSecret();

    /**
     * Returns identifyer (oxid, magento, opencart)
     * @return string
     */
    public function getShopName();

    /**
     * Returns posible shop configuration option for different channels
     * @return stdClass
     */
    public function getShopConfig();

    /**
     * Generates and returns the array of datafeed
     *
     * @param stdClass $queryParameters
     * @param array $fieldMap
     * @return stdClass
     */
    public function getFeed(stdClass $queryParameters, array $fieldMap);

    /**
     * Returns the URL where to get generated DataFeed
     *
     * @param stdClass $queryParameters
     * @param array $fieldMap
     * @return string
     */
    public function getFeedUrl(stdClass $queryParameters, array $fieldMap = null);

    /**
     * Generates and returns the delta changes array
     *
     * @param stdClass $queryParameters
     * @param array $fieldMap
     * @param int $deltaTimestamp
     * @return stdClass
     */
    public function getDelta(stdClass $queryParameters, array $fieldMap, int $deltaTimestamp);

    /**
     * Generates and returns the orders
     *
     * @param int $deltaTimestamp
     * @return stdClass
     */
    public function getOrders(int $deltaTimestamp);

    /**
     * Returns the url from where to get the article
     *
     * @param int $deltaTimestamp
     * @return string
     */
    public function getOrdersUrl(int $deltaTimestamp);

    /**
     * Returns the bridge URL throw the Feed is communicating with shop.
     *
     * @return string
     */
    public function getBridgeUrl();

    /**
     * Returns the bridge URL parameters the Feed is communicating with shop.
     *
     * @return string
     */
    public function getUrlParameters();

    /**
     * Returns posible shop fields configuration throw the Feed gets csv fields
     * @return stdClass
     */
    public function getShopFields();

    /**
     * Returns product info
     *
     * @param stdClass $queryParameters
     * @param array $fieldMap
     * @param string $id
     * @return mixed
     */
    public function getProductInfo(stdClass $queryParameters, array $fieldMap, $id);

    /**
     * @param stdClass $queryParameters
     * @param $id
     * @return mixed
     */
    public function getOrderProducts(stdClass $queryParameters, $id);

    /**
     * @return mixed
     */
    public function getFeatures();
}
