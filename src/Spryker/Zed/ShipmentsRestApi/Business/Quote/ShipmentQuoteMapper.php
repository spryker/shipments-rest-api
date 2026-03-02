<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ShipmentsRestApi\Business\Quote;

use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\RestCheckoutRequestAttributesTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Spryker\Shared\ShipmentsRestApi\ShipmentsRestApiConfig;
use Spryker\Zed\ShipmentsRestApi\Dependency\Facade\ShipmentsRestApiToShipmentFacadeInterface;

class ShipmentQuoteMapper implements ShipmentQuoteMapperInterface
{
    /**
     * @uses \Spryker\Shared\Price\PriceConfig::PRICE_MODE_NET
     *
     * @var string
     */
    protected const PRICE_MODE_NET = 'NET_MODE';

    /**
     * @var \Spryker\Zed\ShipmentsRestApi\Dependency\Facade\ShipmentsRestApiToShipmentFacadeInterface
     */
    protected ShipmentsRestApiToShipmentFacadeInterface $shipmentFacade;

    /**
     * @var array<\Spryker\Zed\ShipmentsRestApiExtension\Dependency\Plugin\QuoteItemExpanderPluginInterface>
     */
    protected array $quoteItemExpanderPlugins;

    /**
     * @param \Spryker\Zed\ShipmentsRestApi\Dependency\Facade\ShipmentsRestApiToShipmentFacadeInterface $shipmentFacade
     * @param list<\Spryker\Zed\ShipmentsRestApiExtension\Dependency\Plugin\QuoteItemExpanderPluginInterface> $quoteItemExpanderPlugins
     */
    public function __construct(
        ShipmentsRestApiToShipmentFacadeInterface $shipmentFacade,
        array $quoteItemExpanderPlugins
    ) {
        $this->shipmentFacade = $shipmentFacade;
        $this->quoteItemExpanderPlugins = $quoteItemExpanderPlugins;
    }

    public function mapShipmentToQuote(
        RestCheckoutRequestAttributesTransfer $restCheckoutRequestAttributesTransfer,
        QuoteTransfer $quoteTransfer
    ): QuoteTransfer {
        if (
            !$restCheckoutRequestAttributesTransfer->getShipment()
            || !$restCheckoutRequestAttributesTransfer->getShipment()->getIdShipmentMethod()
        ) {
            return $quoteTransfer;
        }
        $idShipmentMethod = $restCheckoutRequestAttributesTransfer->getShipment()->getIdShipmentMethod();

        $shipmentTransfer = $this->createShipmentTransfer($idShipmentMethod);
        $quoteTransfer = $this->setShipmentTransferIntoQuote($quoteTransfer, $shipmentTransfer);

        $shipmentMethodTransfer = $this->shipmentFacade->findAvailableMethodById($idShipmentMethod, $quoteTransfer);
        if ($shipmentMethodTransfer === null) {
            return $this->removeShipmentTransferFromQuote($quoteTransfer);
        }

        $shipmentTransfer = new ShipmentTransfer();
        $shipmentTransfer->setMethod($shipmentMethodTransfer)
            ->setShipmentSelection((string)$idShipmentMethod)
            ->setShippingAddress($quoteTransfer->getShippingAddress());

        $quoteTransfer = $this->setShipmentTransferIntoQuote($quoteTransfer, $shipmentTransfer);
        $quoteTransfer = $this->executeQuoteItemExpanderPlugins($quoteTransfer);
        $expenseTransfer = $this->createShippingExpenseTransfer($shipmentTransfer, $quoteTransfer);

        return $this->setShipmentExpense($quoteTransfer, $expenseTransfer);
    }

    protected function executeQuoteItemExpanderPlugins(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        foreach ($this->quoteItemExpanderPlugins as $quoteShipmentExpanderPlugin) {
            $quoteTransfer = $quoteShipmentExpanderPlugin->expandQuoteItems($quoteTransfer);
        }

        return $quoteTransfer;
    }

    protected function createShippingExpenseTransfer(ShipmentTransfer $shipmentTransfer, QuoteTransfer $quoteTransfer): ExpenseTransfer
    {
        $shipmentExpenseTransfer = new ExpenseTransfer();
        $shipmentExpenseTransfer->fromArray($shipmentTransfer->getMethod()->toArray(), true);
        $shipmentExpenseTransfer->setType(ShipmentsRestApiConfig::SHIPMENT_EXPENSE_TYPE);
        $shipmentExpenseTransfer->setQuantity(1);
        $shipmentExpenseTransfer->setShipment($shipmentTransfer);
        if ($quoteTransfer->getPriceMode() === static::PRICE_MODE_NET) {
            return $shipmentExpenseTransfer
                ->setUnitNetPrice($shipmentTransfer->getMethod()->getStoreCurrencyPrice())
                ->setUnitGrossPrice(0);
        }

        return $shipmentExpenseTransfer
            ->setUnitNetPrice(0)
            ->setUnitGrossPrice($shipmentTransfer->getMethod()->getStoreCurrencyPrice());
    }

    protected function setShipmentTransferIntoQuote(QuoteTransfer $quoteTransfer, ShipmentTransfer $shipmentTransfer): QuoteTransfer
    {
        $quoteTransfer->setShipment($shipmentTransfer);
        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            $itemTransfer->setShipment($shipmentTransfer);
        }

        return $quoteTransfer;
    }

    protected function removeShipmentTransferFromQuote(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        $quoteTransfer->setShipment(null);
        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            $itemTransfer->setShipment(null);
        }

        return $quoteTransfer;
    }

    protected function setShipmentExpense(QuoteTransfer $quoteTransfer, ExpenseTransfer $expenseTransfer): QuoteTransfer
    {
        foreach ($quoteTransfer->getExpenses() as $expenseKey => $quoteExpenseTransfer) {
            if ($quoteExpenseTransfer->getType() === ShipmentsRestApiConfig::SHIPMENT_EXPENSE_TYPE) {
                $quoteTransfer->getExpenses()->getIterator()->offsetSet($expenseKey, $expenseTransfer);

                return $quoteTransfer;
            }
        }

        $quoteTransfer->addExpense($expenseTransfer);

        return $quoteTransfer;
    }

    protected function createShipmentTransfer(int $idShipmentMethod): ShipmentTransfer
    {
        $shipmentMethodTransfer = (new ShipmentMethodTransfer())->setIdShipmentMethod($idShipmentMethod);

        return (new ShipmentTransfer())->setMethod($shipmentMethodTransfer);
    }
}
