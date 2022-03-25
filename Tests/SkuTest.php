<?php

use Tester\Assert;

class SkuTest extends Aiti_Testing_Model_TestCase {

    const SKU_MAX_LENGTH = Aiti_Marketplace_Model_Product_Attribute_Backend_Sku::SKU_MAX_LENGTH;

    private function _getConnection() {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    protected function setUp() {
        parent::setUp();

        $this->_getConnection()->beginTransaction();
    }

    protected function tearDown() {
        parent::tearDown();

        $this->_getConnection()->rollBack();
    }

    public function testModelProductAttributeBackendSku() {
        $model = Mage::getModel('catalog/product_attribute_backend_sku');

        Assert::type(Aiti_Marketplace_Model_Product_Attribute_Backend_Sku::class, $model); //actual
        Assert::type(Mage_Catalog_Model_Product_Attribute_Backend_Sku::class, $model); //parent
    }

    /**
     * SKU respect the correct format (start with SupplierCode_ followed by random alphanumeric and/or hyphen)
     * AA_correct-sku
     * AA_correct123
     */
    public function testCorrectSku() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate correct SKU
        $generateSku = $skuPrefix . $this->_getRandomAlphanumeric(rand(1, self::SKU_MAX_LENGTH - strlen($skuPrefix)));

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test correct SKU (should return product object)
        $product->validate(); //function throws so the test would fail
    }

    /**
     * SKU respect the basic fulfillment format [ supplierCode{_}F{stockCode}{_}{randomAlphanumericHyphenDotString} ]
     * AA_F01_correct-sku
     * @see Aiti_ReturnedParcels_Helper_Data::isFulfillSku
     */
    public function testCorrectSkuFulfillment() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_F' . rand() . '_';

        // Generate correct SKU
        $generateSku = $skuPrefix . $this->_getRandomAlphanumeric(rand(1, self::SKU_MAX_LENGTH - strlen($skuPrefix)));

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test correct SKU (should return product object)
        $product->validate(); //function throws so the test would fail
    }

    /**
     * SKU respect fulfillment format second version [ supplierCode{_}F{stockCode}{_}F{stockCode}{_}{randomAlphanumeric} ]
     * AB_F01_F05_AB46546598465465465465654
     */
    public function testSecondCorrectSkuFulfillment() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_F' . rand() . '_F' . rand() . '_';

        // Generate correct SKU
        $generateSku = $skuPrefix . $this->_getRandomAlphanumeric(rand(1, self::SKU_MAX_LENGTH - strlen($skuPrefix)));

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test correct SKU (should return product object)
        $product->validate(); //function throws so the test would fail
    }

    /**
     * SKU respect fulfillment format third version [ supplierCode{_}F{stockCode}{_}supplierCode{_}{randomAlphanumeric} ]
     * LP_F01_LP_5444664
     */
    public function testThirdCorrectSkuFulfillment() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_F' . rand() . '_' . $merchantCode . '_';

        // Generate correct SKU suffix contain underscore
        $skuSuffix = $this->_getRandomAlphanumeric(rand(1, self::SKU_MAX_LENGTH - strlen($skuPrefix)));

        // Set correct SKU
        $generateSku = $skuPrefix . $skuSuffix;

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test correct SKU
        $product->validate();
    }

    /**
     * SKU respect fulfillment format fourth version [ supplierCode{_}F{stockCode}{_}{randomAlphanumericDot} ]
     * BR_F01_BR40654654654654654-red-1-6.5
     */
    public function testFourthCorrectSkuFulfillment() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_F' . rand();

        // Generate correct SKU suffix contain underscore
        $skuSuffix = $this->_getRandomAlphanumeric(1) . '.' . $this->_getRandomAlphanumeric(1);

        // Set correct SKU
        $generateSku = $skuPrefix . $skuSuffix;

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test correct SKU
        $product->validate();
    }

    /**
     * SKU contain one allowed space
     * AA_correct sku
     */
    public function testCorrectSkuWithSpace() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate correct SKU
        $generateSku = $skuPrefix . $this->_getRandomAlphanumeric(1) . ' ' . $this->_getRandomAlphanumeric(1);

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test correct SKU (should return product object)
        $product->validate(); //function throws so the test would fail
    }

    /**
     * SKU contain multiple allowed spaces
     * AA_fdgdfg corect ffdg sku
     */
    public function testCorrectSkuWithMultipleSpaces() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate correct SKU
        $generateSku = $skuPrefix . $this->_getRandomAlphanumeric(1) . ' ' . $this->_getRandomAlphanumeric(1)  . ' ' . $this->_getRandomAlphanumeric(1);

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test correct SKU (should return product object)
        $product->validate(); //function throws so the test would fail
    }

    public function testCorrectSkuWithAllowedSpecialCharacters() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate correct SKU
        $generateSku = $skuPrefix . $this->_getRandomAlphanumeric(1) . $this->_getAllowedSpecialCharacters(1)  . ' ' . $this->_getRandomAlphanumeric(1);

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test correct SKU (should return product object)
        $product->validate(); //function throws so the test would fail
    }

    public function testWrongSkuWithAllowedSpecialCharactersAtBeginning() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate wrong SKU suffix contain sequential Hyphen
        $skuSuffix = $this->_getAllowedSpecialCharacters(1) . $this->_getRandomAlphanumeric(1);

        // Set wrong SKU
        $generateSku = $skuPrefix . $skuSuffix;

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test wrong SKU suffix
        Assert::exception(function() use ($product) {
            $product->validate();
        }, Mage_Eav_Model_Entity_Attribute_Exception::class,
            'Product SKU suffix (' . $skuSuffix . ') must contain only (letters, numbers and/or hyphen, or this special characters x < > = ( ) / *). Hyphen at the beginning, the end or sequential (--) is not valid.'
        );
    }

    /**
     * SKU contain not allowed sequential spaces
     * AA_wrong  sku
     */
    public function testWrongSkuWithSequentialSpaces() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate wrong SKU suffix contain sequential Hyphen
        $skuSuffix = $this->_getRandomAlphanumeric(1) . ' ' . ' ' . $this->_getRandomAlphanumeric(1);

        // Set wrong SKU
        $generateSku = $skuPrefix . $skuSuffix;

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test wrong SKU suffix
        Assert::exception(function() use ($product) {
            $product->validate();
        }, Mage_Eav_Model_Entity_Attribute_Exception::class,
            'Product SKU suffix (' . $skuSuffix . ') must contain only (letters, numbers and/or hyphen, or this special characters x < > = ( ) / *). Hyphen at the beginning, the end or sequential (--) is not valid.'
        );
    }

    /**
     * SKU ends with space
     */
    public function testWrongSkuEndsWithSpace() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate wrong SKU suffix contain sequential Hyphen
        $skuSuffix = $this->_getRandomAlphanumeric(1) . $this->_getRandomAlphanumeric(1) . ' ';

        // Set wrong SKU
        $generateSku = $skuPrefix . $skuSuffix;

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test wrong SKU suffix
        Assert::exception(function() use ($product) {
            $product->validate();
        }, Mage_Eav_Model_Entity_Attribute_Exception::class,
            'Product SKU suffix (' . $skuSuffix . ') must contain only (letters, numbers and/or hyphen, or this special characters x < > = ( ) / *). Hyphen at the beginning, the end or sequential (--) is not valid.'
        );
    }

    /**
     * SKU start with space
     */
    public function testWrongSkuStartWithSpace() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate wrong SKU suffix contain sequential Hyphen
        $skuSuffix = ' ';
        $skuSuffix .= $this->_getRandomAlphanumeric(rand(1, self::SKU_MAX_LENGTH - strlen($skuPrefix . $skuSuffix)));

        // Set wrong SKU
        $generateSku = $skuPrefix . $skuSuffix;

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test wrong SKU suffix
        Assert::exception(function() use ($product) {
            $product->validate();
        }, Mage_Eav_Model_Entity_Attribute_Exception::class,
            'Product SKU suffix (' . $skuSuffix . ') must contain only (letters, numbers and/or hyphen, or this special characters x < > = ( ) / *). Hyphen at the beginning, the end or sequential (--) is not valid.'
        );
    }

    /**
     * SKU start with dot
     * LP_.wrong-sku
     */
    public function testWrongSkuStartWithDot() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate wrong SKU suffix contain sequential Hyphen
        $skuSuffix = '.';
        $skuSuffix .= $this->_getRandomAlphanumeric(rand(1, self::SKU_MAX_LENGTH - strlen($skuPrefix . $skuSuffix)));

        // Set wrong SKU
        $generateSku = $skuPrefix . $skuSuffix;

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test wrong SKU suffix
        Assert::exception(function() use ($product) {
            $product->validate();
        }, Mage_Eav_Model_Entity_Attribute_Exception::class,
            'Product SKU suffix (' . $skuSuffix . ') must contain only (letters, numbers and/or hyphen, or this special characters x < > = ( ) / *). Hyphen at the beginning, the end or sequential (--) is not valid.'
        );
    }

    /**
     * SKU ends with dot
     * AA_wrong-sku.
     */
    public function testWrongSkuEndsWithDot() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate wrong SKU suffix contain sequential Hyphen
        $skuSuffix = $this->_getRandomAlphanumeric(1) . $this->_getRandomAlphanumeric(1) . '.';

        // Set wrong SKU
        $generateSku = $skuPrefix . $skuSuffix;

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test wrong SKU suffix
        Assert::exception(function() use ($product) {
            $product->validate();
        }, Mage_Eav_Model_Entity_Attribute_Exception::class,
            'Product SKU suffix (' . $skuSuffix . ') must contain only (letters, numbers and/or hyphen, or this special characters x < > = ( ) / *). Hyphen at the beginning, the end or sequential (--) is not valid.'
        );
    }

    /**
     * SKU contain not allowed sequential dots
     * AA_wrong..sku
     */
    public function testWrongSkuWithSequentialDots() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate wrong SKU suffix contain sequential Hyphen
        $skuSuffix = $this->_getRandomAlphanumeric(1) . '.' . '.' . $this->_getRandomAlphanumeric(1);

        // Set wrong SKU
        $generateSku = $skuPrefix . $skuSuffix;

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test wrong SKU suffix
        Assert::exception(function() use ($product) {
            $product->validate();
        }, Mage_Eav_Model_Entity_Attribute_Exception::class,
            'Product SKU suffix (' . $skuSuffix . ') must contain only (letters, numbers and/or hyphen, or this special characters x < > = ( ) / *). Hyphen at the beginning, the end or sequential (--) is not valid.'
        );
    }

    /**
     * Prefix different than supplier code
     */
    public function testWrongSkuPrefix() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');

        // Generate Wrong SKU prefix
        $testPrefix = $this->_getRandomSpecialCharacters(2) . '_';
        Assert::notSame($merchantCode, $testPrefix); // Make sure our chosen prefix is different than merchant code

        // Generate wrong SKU
        $generateSku = $testPrefix . $this->_getRandomAlphanumeric(rand(1, self::SKU_MAX_LENGTH - strlen($testPrefix)));

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test wrong SKU prefix (not staring with supplierCode_)
        Assert::exception(function() use ($product) {
            $product->validate();
        }, Mage_Eav_Model_Entity_Attribute_Exception::class, 'Product SKU must start with ' . $merchantCode . '_');
    }

    /**
     * Suffix contain special characters
     * AA_wrong/*sku
     */
    public function testWrongSkuSuffix() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate wrong SKU suffix contain special characters
        $skuSuffix = $this->_getRandomAlphanumeric(1) . $this->_getRandomSpecialCharacters(1) . $this->_getRandomAlphanumeric(1);

        // Set wrong SKU
        $generateSku = $skuPrefix . $skuSuffix;

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test wrong SKU suffix
        Assert::exception(function() use ($product) {
            $product->validate();
        }, Mage_Eav_Model_Entity_Attribute_Exception::class,
            'Product SKU suffix (' . $skuSuffix . ') must contain only (letters, numbers and/or hyphen, or this special characters x < > = ( ) / *). Hyphen at the beginning, the end or sequential (--) is not valid.'
        );
    }

    /**
     * Suffix starts with Hyphen
     * AA_-wrong123
     */
    public function testWrongSkuSuffixStartWithHyphen() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate wrong SKU suffix starts with Hyphen
        $skuSuffix = '-';
        $skuSuffix .= $this->_getRandomAlphanumeric(rand(1, self::SKU_MAX_LENGTH - strlen($skuPrefix . $skuSuffix)));

        // Set wrong SKU
        $generateSku = $skuPrefix . $skuSuffix;

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test wrong SKU suffix
        Assert::exception(function() use ($product) {
            $product->validate();
        }, Mage_Eav_Model_Entity_Attribute_Exception::class,
            'Product SKU suffix (' . $skuSuffix . ') must contain only (letters, numbers and/or hyphen, or this special characters x < > = ( ) / *). Hyphen at the beginning, the end or sequential (--) is not valid.'
        );
    }

    /**
     * Suffix ends with Hyphen
     * AA_wrong-sku-
     */
    public function testWrongSkuSuffixEndsWithHyphen() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate wrong SKU suffix ends with Hyphen
        $skuSuffix = $this->_getRandomAlphanumeric(rand(1, self::SKU_MAX_LENGTH - strlen($skuPrefix)) - 1) . '-';

        // Set wrong SKU
        $generateSku = $skuPrefix . $skuSuffix;

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test wrong SKU suffix
        Assert::exception(function() use ($product) {
            $product->validate();
        }, Mage_Eav_Model_Entity_Attribute_Exception::class,
            'Product SKU suffix (' . $skuSuffix . ') must contain only (letters, numbers and/or hyphen, or this special characters x < > = ( ) / *). Hyphen at the beginning, the end or sequential (--) is not valid.'
        );
    }

    /**
     * Suffix contain sequential Hyphen (--)
     * AA_wrong--sku
     */
    public function testWrongSkuSuffixSequentialHyphen() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate wrong SKU suffix contain sequential Hyphen
        $skuSuffix = $this->_getRandomAlphanumeric(1) . '--' . $this->_getRandomAlphanumeric(1);

        // Set wrong SKU
        $generateSku = $skuPrefix . $skuSuffix;

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test wrong SKU suffix
        Assert::exception(function() use ($product) {
            $product->validate();
        }, Mage_Eav_Model_Entity_Attribute_Exception::class,
            'Product SKU suffix (' . $skuSuffix . ') must contain only (letters, numbers and/or hyphen, or this special characters x < > = ( ) / *). Hyphen at the beginning, the end or sequential (--) is not valid.'
        );
    }

    /**
     * SKU exceed max length limit
     */
    public function testWrongSkuExceedLengthLimit() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate wrong SKU exceed length limit
        $skuSuffix = "";
        do {
            $skuSuffix .= $this->_getRandomAlphanumeric(self::SKU_MAX_LENGTH);
        } while(strlen($skuSuffix) < self::SKU_MAX_LENGTH);

        // Set wrong SKU
        $generateSku = $skuPrefix . $skuSuffix;

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test wrong SKU length limit
        Assert::exception(function() use ($product) {
            $product->validate();
        }, Mage_Eav_Model_Entity_Attribute_Exception::class,
            'SKU length should be ' . self::SKU_MAX_LENGTH . ' characters maximum.'
        );
    }

    public function testDuplicatedSku() {
        $merchant = $this->_createTestingMerchant();
        $merchantCode = $merchant->getData('supplier_code');
        $skuPrefix = $merchantCode . '_';

        // Generate correct SKU
        $generateSku = $skuPrefix . $this->_getRandomAlphanumeric(rand(1, self::SKU_MAX_LENGTH - strlen($skuPrefix)));

        $product = $this->_createProductObject($merchantCode, $generateSku);

        // Test correct SKU (should return product object)
        $product->validate(); //function throws so the test would fail

        $product->save(); // save product to use its SKU in second product

        if ($product->getId()) {
            // Create second product with SKU same as existing product
            $secondProduct = $this->_createProductObject($merchantCode, $product->getSku());

            // Test existing SKU
            Assert::exception(function() use ($secondProduct) {
                $secondProduct->validate();
            }, Mage_Eav_Model_Entity_Attribute_Exception::class,
                'The value of attribute "SKU" must be unique'
            );
        }
    }

    private function _createTestingMerchant() {
        $merchant = Mage::getModel('aiti_merchant/merchant');
        $merchant->addData(array(
                'supplier_code' => Mage::helper('suppmanager')->generateNewSupplierCode(),
                'supplier_name' => 'supplier for test sku',
                'status' => Aiti_Suppmanager_Model_Status::STATUS_ACTIVE,
                'created_at' => Mage::getSingleton('core/date')->gmtDate(),
                'accepted_at' => Mage::getSingleton('core/date')->gmtDate()
            )
        );

        $merchant->save();

        return $merchant;
    }

    private function _createProductObject($merchantCode, $sku) {
        return Mage::getModel('catalog/product')->addData([
            'name' => 'Product for correct sku testing',
            'new_supplier' => $merchantCode,
            'sku' => $sku
        ]);
    }

    private function _getRandomAlphanumeric($length) {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";

        return substr(str_shuffle($alphabet),1, $length);
    }

    private function _getAllowedSpecialCharacters($length) {
        $chars = '()<>/*';

        return substr(str_shuffle($chars),1, $length);
    }

    private function _getRandomSpecialCharacters($length) {
        $specialCharacters = "!@#$%^&_~+{}[]?";

        return substr(str_shuffle($specialCharacters),1, $length);
    }
}
