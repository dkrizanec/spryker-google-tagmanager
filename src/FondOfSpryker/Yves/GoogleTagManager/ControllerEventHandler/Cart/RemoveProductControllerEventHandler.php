<?php


namespace FondOfSpryker\Yves\GoogleTagManager\ControllerEventHandler\Cart;

use FondOfSpryker\Shared\GoogleTagManager\EnhancedEcommerceConstants;
use FondOfSpryker\Yves\GoogleTagManager\ControllerEventHandler\ControllerEventHandlerInterface;
use FondOfSpryker\Yves\GoogleTagManager\Dependency\Client\GoogleTagManagerToCartClientInterface;
use FondOfSpryker\Yves\GoogleTagManager\Session\EnhancedEcommerceSessionHandlerInterface;
use Generated\Shared\Transfer\EnhancedEcommerceProductDataTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Spryker\Yves\Kernel\FactoryResolverAwareTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \FondOfSpryker\Yves\GoogleTagManager\GoogleTagManagerFactory getFactory()
 */
class RemoveProductControllerEventHandler implements ControllerEventHandlerInterface
{
    /**
     * @var EnhancedEcommerceSessionHandlerInterface
     */
    protected $sessionHandler;

    /**
     * @var GoogleTagManagerToCartClientInterface
     */
    protected $cartClient;

    /**
     * @param EnhancedEcommerceSessionHandlerInterface $sessionHandler
     * @param GoogleTagManagerToCartClientInterface $cartClient
     */
    public function __construct(
        EnhancedEcommerceSessionHandlerInterface $sessionHandler,
        GoogleTagManagerToCartClientInterface $cartClient
    ) {
        $this->sessionHandler = $sessionHandler;
        $this->cartClient = $cartClient;
    }

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return 'removeAction';
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $locale
     *
     * @return void
     */
    public function handle(Request $request, string $locale): void
    {
        $sku = $request->get(EnhancedEcommerceConstants::PRODUCT_FIELD_SKU);

        if (!$sku) {
            return;
        }

        $itemTransfer = $this->getProductFromQuote($sku);

        if ($itemTransfer === null) {
            return;
        }

        $enhancedEcommerceProductData = new EnhancedEcommerceProductDataTransfer();
        $enhancedEcommerceProductData->setProductAbstractId($itemTransfer->getIdProductAbstract());
        $enhancedEcommerceProductData->setSku($sku);
        $enhancedEcommerceProductData->setQuantity($itemTransfer->getQuantity());
        $enhancedEcommerceProductData->setPrice($itemTransfer->getUnitPrice());

        $this->sessionHandler->removeProduct($enhancedEcommerceProductData);
    }

    /**
     * @param string $sku
     *
     * @return \Generated\Shared\Transfer\ItemTransfer|null
     */
    protected function getProductFromQuote(string $sku): ?ItemTransfer
    {
        $quoteTransfer = $this->cartClient->getQuote();

        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            if ($itemTransfer->getSku() === $sku) {
                return $itemTransfer;
            }
        }

        return null;
    }
}
