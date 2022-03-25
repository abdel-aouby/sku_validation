<?php

class aiti_catalog_Model_Product_Attribute_Backend_Sku extends Mage_Catalog_Model_Product_Attribute_Backend_Sku {

    /**
     * @param Mage_Catalog_Model_Product $object
     * @return bool
     * @throws Mage_Core_Exception
     * @throws Mage_Eav_Exception
     */
    public function validate(Mage_Catalog_Model_Product $object): bool {
        $helper = Mage::helper('core/string');
        $skuMaxLength = Mage::helper('aiti_catalog')->getSkuMaxLength();
        $sku = $object->getSku();
        $merchantCode = $object->getNewSupplier();

        if (!$merchantCode) {
            $merchant = Mage::helper('aiti_merchant')->getMerchant($object->getOwnerId());
            $merchantCode = $merchant->getData('supplier_code');
        }

        /** SKU limit length */
        if ($helper->strlen($sku) > $skuMaxLength) {
            Mage::throwException(
                Mage::helper('catalog')->__('SKU length should be %s characters maximum.', self::SKU_MAX_LENGTH)
            );
        }

        /** SKU must start with supplierCode_ (supplier code and underscore) */
        if (!(substr($sku, 0, strlen($merchantCode . '_')) === $merchantCode . '_') || substr($sku, 0) === $merchantCode . '_') {
            Mage::throwException(
                Mage::helper('aiti_catalog')->__('Product SKU must start with %s', $merchantCode . '_')
            );
        }

        /**
         * If fulfillment SKU,
         * the part after second _ (underscore)
         * must be alphanumeric and/or - (only letters, numbers, hyphen and this special characters x < > = ( ) / are allowed)
         * @see Aiti_ReturnedParcels_Helper_Data::isFulfillSku
         *
         * IF not fulfillment SKU, the part after supplierCode_ (supplier code and underscore)
         * must be alphanumeric and/or - (only letters, numbers, hyphen and this special characters x < > = ( ) / are allowed)
         */
        $isFulfillmentSku = Mage::helper('aiti_returnedparcels')->isFulfillSku($sku);
        if ($isFulfillmentSku) {
            $skuWithoutPrefix = substr($sku, strrpos($sku, '_') + 1); // get the string after last (_) underscore
        } else {
            $skuWithoutPrefix = substr($sku, strlen($merchantCode . '_'));
        }

        /**
         * [A-Za-z0-9]      => Suffix must start with alphanumeric
         * (?!.*--)         => Sequential hyphen (--) not allowed in any part of the suffix
         * (?!.*\.\.)       => Sequential dots (..) not allowed in any part of the suffix
         * (?!.*\s\s)       => Sequential spaces (  ) not allowed in any part of the suffix
         * [A-Za-z0-9- ]*   => Rest of suffix may contain zero or more alphanumeric and/or hyphen (-) and/or space and/or this special characters x < > = ( ) /
         * $(?<!-)          => Hyphen (-) not allowed at the end of suffix
         * (?<!\s)          => Space ( ) not allowed at the end of suffix
         * (?<!\.)          => dot (.) not allowed at the end of suffix
         */
        if (!preg_match('~^[A-Za-z0-9](?!.*--)(?!.*\.\.)(?!.*\s\s)[A-Za-z0-9\-.()<>/* ]*$(?<!-)(?<!\s)(?<!\.)~', $skuWithoutPrefix)) {
            Mage::throwException(
                Mage::helper('aiti_catalog')->__('Product SKU suffix (%s) must contain only (letters, numbers and/or hyphen, or this special characters x < > = ( ) / *). Hyphen at the beginning, the end or sequential (--) is not valid.', $skuWithoutPrefix)
            );
        }

        return Mage_Eav_Model_Entity_Attribute_Backend_Abstract::validate($object);
    }
}