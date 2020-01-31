<?php

namespace FondOfSpryker\Yves\GoogleTagManager\Plugin\EnhancedEcommerce;

use FondOfSpryker\Shared\GoogleTagManager\GoogleTagManagerConstants;
use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

/**
 * @method \FondOfSpryker\Yves\GoogleTagManager\GoogleTagManagerFactory getFactory()
 * @method \FondOfSpryker\Client\GoogleTagManager\GoogleTagManagerClient getClient()
 */
class EnhancedEcommerceCheckoutSummaryPlugin extends AbstractPlugin implements EnhancedEcommercePageTypePluginInterface
{
    /**
     * @param \Twig_Environment $twig
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array|null $params
     *
     * @throws
     *
     * @return string
     */
    public function handle(Twig_Environment $twig, Request $request, ?array $params = []): string
    {
        return $twig->render($this->getTemplate(), [
            'data' => [
                $this->renderCheckoutPaymentSelection($this->getClient()->getCartClient()->getQuote()),
                $this->renderSummary(),
            ],
        ]);
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return '@GoogleTagManager/partials/enhanced-ecommerce-default.twig';
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array
     */
    protected function renderCheckoutPaymentSelection(QuoteTransfer $quoteTransfer): array
    {
        return [
            'event' => 'eec.checkout_option',
            'ecommerce' => [
                'checkout_option' => [
                    'actionField' => [
                        'step' => GoogleTagManagerConstants::EEC_CHECKOUT_STEP_PAYMENT,
                        'option' => $quoteTransfer->getPayment() instanceof PaymentTransfer ? $quoteTransfer->getPayment()->getPaymentProvider() : '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function renderSummary(): array
    {
        return [
            'event' => 'eec.checkout',
            'ecommerce' => [
                'checkout' => [
                    'actionField' => [
                        'step' => GoogleTagManagerConstants::EEC_CHECKOUT_STEP_SUMMARY,
                    ],
                ],
            ],
        ];
    }
}