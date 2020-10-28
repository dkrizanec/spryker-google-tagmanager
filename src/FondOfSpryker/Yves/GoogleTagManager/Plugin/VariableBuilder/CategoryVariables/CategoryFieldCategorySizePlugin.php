<?php

namespace FondOfSpryker\Yves\GoogleTagManager\Plugin\VariableBuilder\CategoryVariables;

use FondOfSpryker\Yves\GoogleTagManager\Dependency\VariableBuilder\CategoryFieldPluginInterface;
use Generated\Shared\Transfer\GoogleTagManagerCategoryTransfer;
use Spryker\Yves\Kernel\AbstractPlugin;

class CategoryFieldCategorySizePlugin extends AbstractPlugin implements CategoryFieldPluginInterface
{
    /**
     * @param \Generated\Shared\Transfer\GoogleTagManagerCategoryTransfer $googleTagManagerCategoryTransfer
     * @param array $category
     * @param array $products
     * @param array $params
     *
     * @return \Generated\Shared\Transfer\GoogleTagManagerCategoryTransfer
     */
    public function handle(
        GoogleTagManagerCategoryTransfer $googleTagManagerCategoryTransfer,
        array $category,
        array $products = [],
        array $params = []
    ): GoogleTagManagerCategoryTransfer {
        $googleTagManagerCategoryTransfer->setCategorySize(count($products));

        return $googleTagManagerCategoryTransfer;
    }
}
