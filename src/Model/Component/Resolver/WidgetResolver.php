<?php

declare(strict_types=1);

namespace MVenghaus\MagewirePluginWidgetResolver\Model\Component\Resolver;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Result\PageFactory as ResultPageFactory;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Model\Widget;
use Magewirephp\Magewire\Component;
use Magewirephp\Magewire\Model\Component\Resolver\Layout;
use Magewirephp\Magewire\Model\ComponentFactory;
use Magewirephp\Magewire\Model\RequestInterface as MagewireRequestInterface;

class WidgetResolver extends Layout
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly Widget $widget,
        ResultPageFactory $resultPageFactory,
        EventManagerInterface $eventManager,
        ComponentFactory $componentFactory
    ) {
        parent::__construct($resultPageFactory, $eventManager, $componentFactory);
    }


    public function getName(): string
    {
        return 'widget';
    }

    public function complies(AbstractBlock $block): bool
    {
        return ($block instanceof BlockInterface);
    }

    public function reconstruct(MagewireRequestInterface $request): Component
    {
        $page = $this->resultPageFactory->create();
        $page->addHandle(strtolower($request->getFingerprint('handle')))->initLayout();

        $dataMeta = $request->getServerMemo('dataMeta');

        $widgetId = $dataMeta['widget_id'];
        $widgetData = $dataMeta['widget_data'];

        $widget = $this->widget->getWidgets()[$widgetId];

        $blockClass = $widget['@']['type'] ?? null;
        $magewireClass = $widget['parameters']['magewire']['value'] ?? null;

        /** @var Component $component */
        $component = $this->objectManager->create($magewireClass);

        $block = $page->getLayout()->createBlock($blockClass)
            ->setData('magewire', $component)
            ->addData($widgetData);

        return $this->construct($block);
    }
}
