<?php

/**
 * Google Tag Manager tracking integration for Spryker
 *
 * @author      Jozsef Geng <jozsef.geng@fondof.de>
 */

namespace FondOFSpryker\Yves\GoogleTagManager\Twig;

use FondOfSpryker\Yves\GoogleTagManager\DataLayer\Variable;
use FondOfSpryker\Yves\GoogleTagManager\DataLayer\VariableInterface;
use Spryker\Client\Cart\CartClientInterface;
use Spryker\Shared\Twig\TwigExtension;
use Twig_Environment;
use Twig_SimpleFunction;

class GoogleTagManagerTwigExtension extends TwigExtension
{
    const FUNCTION_GOOGLE_TAG_MANAGER   = 'fondOfSpykerGoogleTagManager';
    const FUNCTION_DATA_LAYER           = 'fondOfSpykerDataLayer';


    /**
     * @var string
     */
    protected $containerID;

    /**
     * @var \Spryker\Client\Cart\CartClientInterface
     */
    protected $cartClient;

    /**
     * @var \Spryker\Yves\Cart\CartFactory
     */
    protected $cartFactory;

    /**
     * @var array
     */
    protected $dataLayerVariables = [];

    /**
     * @var bool
     */
    protected $isEnabled;

    /**
     * @var \Spryker\Yves\Kernel\Application
     */
    protected $productClient;

    /**
     * @var \FondOfSpryker\Yves\GoogleTagManager\DataLayer\VariableInterface
     */
    protected $variable;

    /**
     * @param string $containerID
     * @param \Spryker\Yves\Kernel\Application $application
     */
    public function __construct(
        string $containerID,
        bool $isEnabled,
        VariableInterface $variable,
        CartClientInterface $cartClient
    )
    {
        $this->containerID = $containerID;
        $this->cartClient = $cartClient;
        $this->isEnabled = $isEnabled;
        $this->variable = $variable;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            $this->createGoogleTagManagerFunction(),
            $this->createDataLayerFunction(),
        ];
    }

    /**
     * @return \Twig_SimpleFunction
     */
    protected function createGoogleTagManagerFunction()
    {
        return new Twig_SimpleFunction(
            static::FUNCTION_GOOGLE_TAG_MANAGER,
            [$this, 'renderGoogleTagManager'],
            [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]
        );
    }

    /**
     * @return \Twig_SimpleFunction
     */
    protected function createDataLayerFunction()
    {
        return new Twig_SimpleFunction(
            static::FUNCTION_DATA_LAYER,
            [$this, 'renderDataLayer'],
            [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]
        );
    }


    /**
     * @param Twig_Environment $twig
     * @param string $templateName
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function renderGoogleTagManager(Twig_Environment $twig, $templateName): string
    {
        if (!$this->isEnabled || !$this->containerID) {
            return '';
        }

        return $twig->render($templateName, [
            'containerID' => $this->containerID,
        ]);
    }

    /**
     * @param Twig_Environment $twig
     * @param string $page
     * @param array $params
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function renderDataLayer(Twig_Environment $twig, $page, $params): string
    {
        if (!$this->isEnabled || !$this->containerID) {
            return '';
        }

        $this->addDefaultVariables($page);

        if ($page == Variable::PAGE_TYPE_PRODUCT) {
            $this->addProductVariables($params['product']);
        }

        if ($page == Variable::PAGE_TYPE_CATEGORY) {
            $this->addCategoryVariables($params['category'], $params['products']);
        }

        if ($page == Variable::PAGE_TYPE_ORDER) {
            $this->addOrderVariables($params['order']);
        }else{
            $this->addQuoteVariables();
        }

        return $twig->render($this->getDataLayerTemplateName(), [
            'data' => $this->dataLayerVariables
        ]);
    }


    /**
     * @param string $page
     * @return array
     */
    protected function addDefaultVariables($page) : array
    {
        return $this->dataLayerVariables = array_merge(
            $this->dataLayerVariables,
            $this->variable->getDefaultVariables($page)
        );
    }

    /**
     * @param \Generated\Shared\Transfer\StorageProductTransfer $product
     * @return array
     */
    protected function addProductVariables($product) : array
    {
        return $this->dataLayerVariables = array_merge(
            $this->dataLayerVariables,
            $this->variable->getProductVariables($product)
        );

    }

    /**
     * @param array $category
     * @param array $products
     * @return array
     */
    protected function addCategoryVariables($category, $products) : array
    {
        return $this->dataLayerVariables = array_merge(
            $this->dataLayerVariables,
            $this->variable->getCategoryVariables($category, $products)
        );
    }

    /**
     * @return array
     */
    protected function addQuoteVariables() : array
    {
        /**
         * @var Generated\Shared\Transfer\QuoteTransfer
         */
        $quoteTransfer = $this->cartClient->getQuote();

        if (!$quoteTransfer || count($quoteTransfer->getItems()) == 0 ) {
            return $this->dataLayerVariables;
        }

        return $this->dataLayerVariables = array_merge(
            $this->dataLayerVariables,
            $this->variable->getQuoteVariables($quoteTransfer)
        );
    }

    /**
     * @param $orderTransfer
     * @return array
     */
    protected function addOrderVariables($orderTransfer)
    {

        return $this->dataLayerVariables = array_merge(
            $this->dataLayerVariables,
            $this->variable->getOrderVariables($orderTransfer)
        );
    }

    /**
     * @return string
     */
    protected function getDataLayerTemplateName() : string
    {
        return '@GoogleTagManager/partials/data-layer.twig';
    }
}