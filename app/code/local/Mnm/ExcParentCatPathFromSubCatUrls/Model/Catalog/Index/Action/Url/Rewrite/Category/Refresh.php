<?php

class Mnm_ExcParentCatPathFromSubCatUrls_Model_Catalog_Index_Action_Url_Rewrite_Category_Refresh
    extends Enterprise_Catalog_Model_Index_Action_Url_Rewrite_Category_Refresh
    implements Enterprise_Mview_Model_Action_Interface
{
    public function __construct(array $args = array())
    {
        parent::__construct($args);
    }
	
    protected function _reindexCategoryUrlKey(Mage_Catalog_Model_Category $category, Mage_Core_Model_Store $store)
    {
        $requestPath = trim($category->getParentUrl(), '/');
		if(Mage::getStoreConfig('catalog/seo/category_use_parent_category')){
			$requestPath = (!empty($requestPath) ? $requestPath . '/' : '') . $category->getUrlKey();
		}else{
			$requestPath = $category->getUrlKey();
		} 
        $requestPath = $this->_cutRequestPath($requestPath);
        $urlKeyValue = $this->_getUrlKeyAttributeValueId($category, $store);
        if (empty($urlKeyValue) && empty($urlKeyValue['value_id'])) {
            $category = $this->_setUrlKeyForDefaultStore($category, $store);
            $urlKeyValue = $this->_getUrlKeyAttributeValueId($category, $store);
        }
        $valueId = $urlKeyValue['value_id'];
        $rewriteRow = $this->_getRewrite($requestPath, $store->getId());
        if (!$rewriteRow || $rewriteRow['value_id'] != $valueId) {
            $rewriteForValueId = $this->_getRewriteForValueId($store->getId(), $valueId);
            $suffix = trim(str_replace($requestPath, '', $category->getRequestPath()), '-');
            $requestPathIncrement = (int) $this->_getRewriteRequestIncrement($requestPath, $store);
            if (!$rewriteForValueId || !preg_match('#^(\d)+$#', $suffix) || ($suffix > $requestPathIncrement)) {
                if ($rewriteRow && $rewriteRow['value_id'] != $valueId) {
                    $requestPath .= '-' . ++$requestPathIncrement;
                }
                $category = $this->_saveRewrite($category, $store, $requestPath, $valueId);
            }
        }
        $this->_indexedCategoryIds[$store->getId()][$category->getId()] = 1;
        return $category;
    }
}
		