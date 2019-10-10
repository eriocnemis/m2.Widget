<?php
/**
 * Copyright © Eriocnemis, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Eriocnemis\Widget\Controller\Adminhtml\Widget\Instance;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random as MathRandom;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use Magento\Backend\App\Action\Context;
use Magento\Widget\Controller\Adminhtml\Widget\Instance as Action;
use Magento\Widget\Model\Widget\InstanceFactory;
use Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Mass delete controller
 */
class MassDelete extends Action
{
    /**
     * Widget collection factory
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Initialize controller
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param InstanceFactory $widgetFactory
     * @param LoggerInterface $logger
     * @param MathRandom $mathRandom
     * @param InlineInterface $translateInline
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        InstanceFactory $widgetFactory,
        LoggerInterface $logger,
        MathRandom $mathRandom,
        InlineInterface $translateInline,
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;

        parent::__construct(
            $context,
            $coreRegistry,
            $widgetFactory,
            $logger,
            $mathRandom,
            $translateInline
        );
    }

    /**
     * MassDelete action
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $instanceIds = (array)$this->getRequest()->getParam('instance_ids');
        if (!count($instanceIds)) {
            $this->messageManager->addError(
                __('Please correct the widgets you requested.')
            );
            return $this->_redirect('*/*/*');
        }

        try {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('instance_id', ['in' => $instanceIds]);
            $collection->walk('delete');

            $this->messageManager->addSuccess(
                __('You deleted a total of %1 records.', count($instanceIds))
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('We can\'t delete these widgets right now. Please review the log and try again.')
            );
            $this->_logger->critical($e);
        }
        $this->_redirect('*/*/index');
    }
}
