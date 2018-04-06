<?php

/**
 * Google Tag Manager Data Layer Variables
 *
 * @author      Jozsef Geng <jozsef.geng@fondof.de>
 */
namespace FondOfSpryker\Yves\GoogleTagManager\DataLayer;

use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\StorageProductTransfer;
use Spryker\Yves\Money\Plugin\MoneyPlugin;


class Variable implements VariableInterface
{

    const PAGE_TYPE_CATEGORY  = "category";
    const PAGE_TYPE_CART  = "cart";
    const PAGE_TYPE_HOME  = "home";
    const PAGE_TYPE_ORDER = "order";
    const PAGE_TYPE_OTHER = "other";
    const PAGE_TYPE_PRODUCT  = "product";

    const TRANSACTION_ENTITY_QUOTE = 'QUOTE';
    const TRANSACTION_ENTITY_ORDER = 'ORDER';

    /**
     * @param string $page
     * @return array
     */
    public function getDefaultVariables($page) : array
    {
        return array(
            'pageType' => $page
        );
    }

    /**
     * @param Generated\Shared\Transfer\StorageProductTransfer $product
     * @return array
     */
    public function getProductVariables(StorageProductTransfer $product) : array
    {
        return array(
            'productId' => $product->getIdProductAbstract(),
            'productName' => $product->getName(),
            'productSku' => $product->getSku(),
            'productPrice' => '',
            'productPriceExcludingTax' => '',
            'productTax' => '',
            'productTaxRate' => ''
        );
    }

    /**
     * @param array $category
     * @param array $products
     * @return array
     */
    public function getCategoryVariables($category, $products) : array
    {
        $categoryProducts = [];
        $productSkus = [];

        foreach ($products as $product) {
            $productSkus[] = $product['abstract_sku'];
            $categoryProducts [] = array(
                'id' => $product['id_product_abstract'],
                'name' => $product['abstract_name'],
                'sku' => $product['abstract_sku'],
                'price' => $this->formatPrice($product['price'])
            );


        }

        return array(
            'categoryId' => $category['id_category'],
            'categoryName' => $category['name'],
            'categorySize' => count($categoryProducts),
            'categoryProducts' => $categoryProducts,
            'products' => $productSkus
        );
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @return array
     */
    public function getQuoteVariables(QuoteTransfer $quoteTransfer) : array
    {
        $transactionProducts = [];
        $transactionProductsSkus = [];
        $total = $quoteTransfer->getTotals()->getGrandTotal();
        $totalWithoutShippingAmount = 0;
        $quoteItems = $quoteTransfer->getItems();

        if (count($quoteItems) > 0) {
            foreach ($quoteItems as $item) {
                $transactionProductsSkus[] = $item->getSku() ;
                $transactionProducts [] = $this->getProductForTransaction($item);
            }
        }

        if ($quoteTransfer->getShipment()) {
            $totalWithoutShippingAmount = $total - $quoteTransfer->getShipment()->getMethod()->getStoreCurrencyPrice();
        }

        return  array(
            'transactionEntity' => self::TRANSACTION_ENTITY_QUOTE,
            'transactionId' => '',
            'transactionAffiliation' => $quoteTransfer->getStore()->getName(),
            'transactionTotal' => $this->formatPrice($total),
            'transactionTotalWithoutShippingAmount' => $this->formatPrice($totalWithoutShippingAmount),
            'transactionTax' => $this->formatPrice($quoteTransfer->getTotals()->getTaxTotal()->getAmount()),
            'transactionProducts' => $transactionProducts,
            'transactionProductsSkus' => $transactionProductsSkus
        );
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @return array
     */
    public function getOrderVariables(OrderTransfer $orderTransfer)
    {
        $transactionProducts = [];
        $transactionProductsSkus = [];
        $orderItems = $orderTransfer->getItems();

        if (count($orderItems) > 0) {
            foreach ($orderItems as $item) {
                $transactionProductsSkus[] = $item->getSku() ;
                $transactionProducts [] = $this->getProductForTransaction($item);
            }
        }

        return  array(
            'transactionEntity' => self::TRANSACTION_ENTITY_ORDER,
            'transactionId' => $orderTransfer->getOrderReference(),
            'transactionDate' => $orderTransfer->getCreatedAt(),
            'transactionAffiliation' => $orderTransfer->getStore(),
            'transactionTotal' => $this->formatPrice($orderTransfer->getTotals()->getGrandTotal()),
            'transactionTotalWithoutShippingAmount' => '',
            'transactionSubtotal' => $this->formatPrice($orderTransfer->getTotals()->getSubtotal()),
            'transactionTax' => $this->formatPrice($orderTransfer->getTotals()->getTaxTotal()->getAmount()),
            'transactionShipping' => $orderTransfer->getShipment()->getMethod()->getName(),
            'transactionPayment' => $orderTransfer->getPayment()->getPaymentMethod(),
            'transactionCurrency' => $orderTransfer->getCurrency(),
            'transactionProducts' => $transactionProducts,
            'transactionProductsSkus' => $transactionProductsSkus
        );
    }

    /**
     * @param Generated\Shared\Transfer\ItemTransfer $product
     * @return array
     */
    protected function getProductForTransaction (ItemTransfer $product)
    {
        return array(
            'id' => $product->getIdProductAbstract(),
            'sku' => $product->getSku(),
            'name' => $product->getName(),
            'price' => $this->formatPrice($product->getUnitPrice()),
            'priceexcludingtax' => ($product->getUnitNetPrice()) ? $this->formatPrice($product->getUnitNetPrice()) :  $this->formatPrice($product->getUnitPrice() - $product->getUnitTaxAmount()),
            'tax' => $this->formatPrice($product->getUnitTaxAmount()),
            'taxrate' => $product->getTaxRate()
        );
    }

    /**
     * @param int $amount
     * @return decimal
     */
    protected function formatPrice($amount)
    {
        $money = new MoneyPlugin();

        return $money->convertIntegerToDecimal($amount);
    }
}