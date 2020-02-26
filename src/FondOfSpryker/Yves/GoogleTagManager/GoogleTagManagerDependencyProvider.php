<?php

namespace FondOfSpryker\Yves\GoogleTagManager;

use FondOfSpryker\Shared\GoogleTagManager\EnhancedEcommerceConstants;
use FondOfSpryker\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToCartClientBridge;
use FondOfSpryker\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToProductStorageClientBridge;
use FondOfSpryker\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToSessionClientBridge;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\EnhancedEcommerce\EnhancedEcommerceCartPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\EnhancedEcommerce\EnhancedEcommerceCheckoutBillingAddressPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\EnhancedEcommerce\EnhancedEcommerceCheckoutPaymentPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\EnhancedEcommerce\EnhancedEcommerceCheckoutSummaryPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\EnhancedEcommerce\EnhancedEcommerceProductDetailPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\EnhancedEcommerce\EnhencedEcommercePurchasePlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\Mapper\EnhancedEcommerceProductMapper\BrandProductFieldMapperPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\Mapper\EnhancedEcommerceProductMapper\Dimension1ProductFieldMapperPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\Mapper\EnhancedEcommerceProductMapper\IdProductFieldMapperPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\Mapper\EnhancedEcommerceProductMapper\NameProductFieldMapperPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\Mapper\EnhancedEcommerceProductMapper\PriceProductFieldMapperPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\Mapper\EnhancedEcommerceProductMapper\QuantityProductFieldMapperPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\Mapper\EnhancedEcommerceProductMapper\VariantProductFieldMapperPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\VariableBuilder\CategoryVariables\ProductSkuCategoryVariableBuilderPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\VariableBuilder\DefaultVariables\CurrencyVariableBuilderPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\VariableBuilder\DefaultVariables\CustomerEmailHashVariableBuilderPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\VariableBuilder\DefaultVariables\StoreNameVariableBuilderPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\VariableBuilder\OrderVariables\OrderDiscountPlugin;
use FondOfSpryker\Yves\GoogleTagManager\Plugin\VariableBuilder\ProductVariables\SalePricePlugin;
use Spryker\Shared\Kernel\Store;
use Spryker\Yves\Kernel\AbstractBundleDependencyProvider;
use Spryker\Yves\Kernel\Container;
use Spryker\Yves\Money\Plugin\MoneyPlugin;

/**
 * @method \FondOfSpryker\Yves\GoogleTagManager\GoogleTagManagerConfig getConfig()
 */
class GoogleTagManagerDependencyProvider extends AbstractBundleDependencyProvider
{
    public const CART_CLIENT = 'CART_CLIENT';
    public const PRODUCT_CLIENT = 'PRODUCT_CLIENT';
    public const PRODUCT_STORAGE_CLIENT = 'PRODUCT_STORAGE_CLIENT';
    public const TAX_PRODUCT_CONNECTOR_CLIENT = 'TAX_PRODUCT_CONNECTOR_CLIENT';
    public const PLUGIN_MONEY = 'PLUGIN_MONEY';
    public const SESSION_CLIENT = 'SESSION_CLIENT';
    public const PRODUCT_VARIABLE_BUILDER_PLUGINS = 'PRODUCT_VARIABLE_BUILDER_PLUGINS';
    public const DEFAULT_VARIABLE_BUILDER_PLUGINS = 'DEFAULT_VARIABLE_BUILDER_PLUGINS';
    public const CATEGORY_VARIABLE_BUILDER_PLUGINS = 'CATEGORY_VARIABLE_BUILDER_PLUGINS';
    public const ORDER_VARIABLE_BUILDER_PLUGINS = 'ORDER_VARIABLE_BUILDER_PLUGINS';
    public const QUOTE_VARIABLE_BUILDER_PLUGINS = 'QUOTE_VARIABLE_BUILDER_PLUGINS';
    public const CART_CONTROLLER_EVENT_HANDLER = 'CART_CONTROLLER_EVENT_HANDLER';
    public const ENHANCED_ECOMMERCE_PAGE_PLUGINS = 'ENHANCED_ECOMMERCE_PAGE_PLUGINS';
    public const STORE = 'STORE';
    public const PRODUCT_FIELD_MAPPER_PLUGINS = 'PRODUCT_FIELD_MAPPER_PLUGINS';

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    public function provideDependencies(Container $container)
    {
        $this->provideCartClient($container);
        $this->provideProductClient($container);
        $this->provideTaxProductConnectorClient($container);
        $this->provideMoneyPlugin($container);
        $this->provideSessionClient($container);
        $this->addProductVariableBuilderPlugins($container);
        $this->addCategoryVariableBuilderPlugins($container);
        $this->addDefaultVariableBuilderPlugins($container);
        $this->addOrderVariableBuilderPlugins($container);
        $this->addQuoteVariableBuilderPlugins($container);
        $this->addEnhancedEcommercePlugins($container);
        $this->addProductStorageClient($container);
        $this->addStore($container);
        $this->addProductFieldMapperPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container $container
     */
    protected function provideCartClient(Container $container): Container
    {
        $container[static::CART_CLIENT] = function (Container $container) {
            return new GoogleTagManagerToCartClientBridge($container->getLocator()->cart()->client());
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container $container
     */
    protected function provideProductClient(Container $container)
    {
        $container[static::PRODUCT_CLIENT] = function (Container $container) {
            return $container->getLocator()->product()->client();
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container $container
     */
    protected function provideTaxProductConnectorClient(Container $container)
    {
        $container[static::TAX_PRODUCT_CONNECTOR_CLIENT] = function (Container $container) {
            return $container->getLocator()->taxProductConnector()->client();
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function provideMoneyPlugin(Container $container)
    {
        $container[static::PLUGIN_MONEY] = function () {
            return new MoneyPlugin();
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function provideSessionClient(Container $container): Container
    {
        $container[static::SESSION_CLIENT] = function (Container $container) {
            return new GoogleTagManagerToSessionClientBridge($container->getLocator()->session()->client());
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addProductVariableBuilderPlugins(Container $container): Container
    {
        $container[static::PRODUCT_VARIABLE_BUILDER_PLUGINS] = function (Container $container) {
            return $this->getProductVariableBuilderPlugins($container);
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \FondOfSpryker\Yves\GoogleTagManager\Plugin\VariableBuilder\ProductVariables\ProductVariableBuilderPluginInterface[]
     */
    protected function getProductVariableBuilderPlugins(Container $container): array
    {
        return [
            new SalePricePlugin(new MoneyPlugin(), $this->getConfig()),
        ];
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addCategoryVariableBuilderPlugins(Container $container): Container
    {
        $container[static::CATEGORY_VARIABLE_BUILDER_PLUGINS] = function (Container $container) {
            return $this->getCategoryVariableBuilderPlugins($container);
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \FondOfSpryker\Yves\GoogleTagManager\Plugin\VariableBuilder\CategoryVariables\CategoryVariableBuilderPluginInterface[]
     */
    protected function getCategoryVariableBuilderPlugins(Container $container): array
    {
        return [
            new ProductSkuCategoryVariableBuilderPlugin(),
        ];
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addDefaultVariableBuilderPlugins(Container $container): Container
    {
        $container[static::DEFAULT_VARIABLE_BUILDER_PLUGINS] = function (Container $container) {
            return $this->getDefaultVariableBuilderPlugins($container);
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \FondOfSpryker\Yves\GoogleTagManager\Plugin\VariableBuilder\DefaultVariables\DefaultVariableBuilderPluginInterface[]
     */
    protected function getDefaultVariableBuilderPlugins(Container $container): array
    {
        return [
            new CustomerEmailHashVariableBuilderPlugin(),
            new StoreNameVariableBuilderPlugin(),
            new CurrencyVariableBuilderPlugin(),
        ];
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addOrderVariableBuilderPlugins(Container $container): Container
    {
        $container[static::ORDER_VARIABLE_BUILDER_PLUGINS] = function (Container $container) {
            return $this->getOrderVariableBuilderPlugins($container);
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \FondOfSpryker\Yves\GoogleTagManager\Plugin\VariableBuilder\OrderVariables\OrderVariableBuilderPluginInterface[]
     */
    protected function getOrderVariableBuilderPlugins(Container $container): array
    {
        return [
            new OrderDiscountPlugin(),
        ];
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addQuoteVariableBuilderPlugins(Container $container): Container
    {
        $container[static::QUOTE_VARIABLE_BUILDER_PLUGINS] = function (Container $container) {
            return $this->getQuoteVariableBuilderPlugins($container);
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \FondOfSpryker\Yves\GoogleTagManager\Plugin\VariableBuilder\ProductVariables\QuoteVariableBuilderPluginInterface[]
     */
    protected function getQuoteVariableBuilderPlugins(Container $container): array
    {
        return [];
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container $container
     */
    protected function addEnhancedEcommercePlugins(Container $container): Container
    {
        $container[static::ENHANCED_ECOMMERCE_PAGE_PLUGINS] = function () {
            return $this->getEnhancedEcommercePlugins();
        };

        return $container;
    }

    /**
     * @return \FondOfSpryker\Yves\GoogleTagManager\Plugin\EnhancedEcommerce\EnhancedEcommercePageTypePluginInterface[]
     */
    protected function getEnhancedEcommercePlugins(): array
    {
        return [
            EnhancedEcommerceConstants::PAGE_TYPE_CART => new EnhancedEcommerceCartPlugin(),
            EnhancedEcommerceConstants::PAGE_TYPE_PRODUCT_DETAIL => new EnhancedEcommerceProductDetailPlugin(),
            EnhancedEcommerceConstants::PAGE_TYPE_CHECKOUT_BILLING_ADDRESS => new EnhancedEcommerceCheckoutBillingAddressPlugin(),
            EnhancedEcommerceConstants::PAGE_TYPE_CHECKOUT_PAYMENT => new EnhancedEcommerceCheckoutPaymentPlugin(),
            EnhancedEcommerceConstants::PAGE_TYPE_CHECKOUT_SUMMARY => new EnhancedEcommerceCheckoutSummaryPlugin(),
            EnhancedEcommerceConstants::PAGE_TYPE_PURCHASE => new EnhencedEcommercePurchasePlugin(),
        ];
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addProductStorageClient(Container $container): Container
    {
        $container[static::PRODUCT_STORAGE_CLIENT] = function (Container $container) {
            return new GoogleTagManagerToProductStorageClientBridge(
                $container->getLocator()->productStorage()->client()
            );
        };

        return $container;
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addStore(Container $container): Container
    {
        $container[static::STORE] = function (Container $container) {
            return $this->getStore();
        };

        return $container;
    }

    /**
     * @return \Spryker\Shared\Kernel\Store
     */
    protected function getStore(): Store
    {
        return Store::getInstance();
    }

    /**
     * @param \Spryker\Yves\Kernel\Container $container
     *
     * @return \Spryker\Yves\Kernel\Container
     */
    protected function addProductFieldMapperPlugins(Container $container): Container
    {
        $container[static::PRODUCT_FIELD_MAPPER_PLUGINS] = function (Container $container) {
            return $this->getProductFieldMapperPlugins();
        };

        return $container;
    }

    /**
     * @return \FondOfSpryker\Yves\GoogleTagManager\Plugin\Mapper\EnhancedEcommerceProductMapper\ProductFieldMapperPluginInterface[]
     */
    protected function getProductFieldMapperPlugins(): array
    {
        return [
            new IdProductFieldMapperPlugin(),
            new NameProductFieldMapperPlugin(),
            new VariantProductFieldMapperPlugin(),
            new BrandProductFieldMapperPlugin(),
            new Dimension1ProductFieldMapperPlugin(),
            new QuantityProductFieldMapperPlugin(),
            new PriceProductFieldMapperPlugin(),
        ];
    }
}
