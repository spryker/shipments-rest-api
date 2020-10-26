<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ShipmentsRestApi\Business\Quote;

use Generated\Shared\Transfer\AddressTransfer;
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
     * @var \Spryker\Zed\ShipmentsRestApi\Dependency\Facade\ShipmentsRestApiToShipmentFacadeInterface
     */
    protected $shipmentFacade;

    /**
     * @param \Spryker\Zed\ShipmentsRestApi\Dependency\Facade\ShipmentsRestApiToShipmentFacadeInterface $shipmentFacade
     */
    public function __construct(
        ShipmentsRestApiToShipmentFacadeInterface $shipmentFacade
    ) {
        $this->shipmentFacade = $shipmentFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\RestCheckoutRequestAttributesTransfer $restCheckoutRequestAttributesTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
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

        $shipmentMethodTransfer = $this->shipmentFacade->findAvailableMethodById($idShipmentMethod, $quoteTransfer);
        if ($shipmentMethodTransfer === null) {
            return $quoteTransfer;
        }

        $shipmentTransfer = new ShipmentTransfer();
        $shipmentTransfer->setMethod($shipmentMethodTransfer)
            ->setShipmentSelection((string)$idShipmentMethod)
            ->setShippingAddress($quoteTransfer->getShippingAddress());

        $quoteTransfer = $this->setShipmentTransferIntoQuote($quoteTransfer, $shipmentTransfer);

        $expenseTransfer = $this->createShippingExpenseTransfer($shipmentTransfer);
        $quoteTransfer->addExpense($expenseTransfer);

        return $quoteTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\RestCheckoutRequestAttributesTransfer $restCheckoutRequestAttributesTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function mapShipmentsToQuote(
        RestCheckoutRequestAttributesTransfer $restCheckoutRequestAttributesTransfer,
        QuoteTransfer $quoteTransfer
    ): QuoteTransfer {
        if (!$restCheckoutRequestAttributesTransfer->getShipments()->count()) {
            return $quoteTransfer;
        }

        foreach ($restCheckoutRequestAttributesTransfer->getShipments() as $restShipmentsTransfer) {
            $shipmentTransfer = (new ShipmentTransfer())
                ->setShippingAddress(
                    (new AddressTransfer())
                        ->fromArray($restShipmentsTransfer->getShippingAddress()->toArray(), true)
                )
                ->setMethod(
                    (new ShipmentMethodTransfer())
                        ->setIdShipmentMethod($restShipmentsTransfer->getShipmentMethodId())
                )
                ->setRequestedDeliveryDate($restShipmentsTransfer->getRequestedDeliveryDate());

            foreach ($quoteTransfer->getItems() as $itemTransfer) {
                if (!in_array($itemTransfer->getGroupKey(), $restShipmentsTransfer->getItems())) {
                    continue;
                }

                $itemTransfer->setShipment($shipmentTransfer);
            }
        }

        return $quoteTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     *
     * @return \Generated\Shared\Transfer\ExpenseTransfer
     */
    protected function createShippingExpenseTransfer(ShipmentTransfer $shipmentTransfer): ExpenseTransfer
    {
        $shipmentExpenseTransfer = new ExpenseTransfer();
        $shipmentExpenseTransfer->fromArray($shipmentTransfer->getMethod()->toArray(), true);
        $shipmentExpenseTransfer->setType(ShipmentsRestApiConfig::SHIPMENT_EXPENSE_TYPE);
        $shipmentExpenseTransfer->setUnitNetPrice(0);
        $shipmentExpenseTransfer->setUnitGrossPrice($shipmentTransfer->getMethod()->getStoreCurrencyPrice());
        $shipmentExpenseTransfer->setQuantity(1);
        $shipmentExpenseTransfer->setShipment($shipmentTransfer);

        return $shipmentExpenseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function setShipmentTransferIntoQuote(QuoteTransfer $quoteTransfer, ShipmentTransfer $shipmentTransfer): QuoteTransfer
    {
        $quoteTransfer->setShipment($shipmentTransfer);
        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            $itemTransfer->setShipment($shipmentTransfer);
        }

        return $quoteTransfer;
    }
}
