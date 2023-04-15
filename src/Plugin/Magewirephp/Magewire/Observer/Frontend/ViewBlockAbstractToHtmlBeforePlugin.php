<?php

declare(strict_types=1);

namespace MVenghaus\MagewirePluginWidgetResolver\Plugin\Magewirephp\Magewire\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Model\Widget;
use Magewirephp\Magewire\Component;
use Magewirephp\Magewire\Observer\Frontend\ViewBlockAbstractToHtmlBefore;

class ViewBlockAbstractToHtmlBeforePlugin
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly Widget $widget,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(ViewBlockAbstractToHtmlBefore $subject, Observer $observer): array
    {
        /** @var Template $block */
        $block = $observer->getBlock();

        if ($block instanceof BlockInterface && empty($block->getData('magewire'))) {
            $blockClass = preg_replace('/\\\\Interceptor$/', '', $block::class);

            $widgets = array_filter(
                $this->widget->getWidgets(),
                fn(array $widgetData) => ($widgetData['@']['type'] ?? '') === $blockClass
            );

            $widgetId = array_key_first($widgets);

            $magewireClass = $widgets[$widgetId]['parameters']['magewire']['value'] ?? null;
            if ($magewireClass) {
                /** @var Component $component */
                $component = $this->objectManager->create($magewireClass);

                $component->setMetaData([
                    'widget_id' => $widgetId,
                    'widget_data' => $this->getWidgetData($block)
                ]);

                $block->setData('magewire', $component);
            }
        }

        return [$observer];
    }

    private function getWidgetData(AbstractBlock $block): array
    {
        return array_filter(
            $block->getData(),
            fn(string $name) => !in_array($name, ['type', 'magewire',  'module_name']),
            ARRAY_FILTER_USE_KEY
        );
    }
}
