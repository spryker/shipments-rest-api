<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ShipmentsRestApi\Processor\Mapper;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\RestOrderDetailsAttributesTransfer;
use Generated\Shared\Transfer\RestOrderShipmentsAttributesTransfer;
use Generated\Shared\Transfer\ShipmentGroupTransfer;

class OrderShipmentMapper implements OrderShipmentMapperInterface
{
    public function mapOrderTransferToRestOrderDetailsAttributesTransfer(
        OrderTransfer $orderTransfer,
        RestOrderDetailsAttributesTransfer $restOrderDetailsAttributesTransfer
    ): RestOrderDetailsAttributesTransfer {
        $restOrderDetailsAttributesTransfer = $this->expandRestOrderDetailsAttributesTransferWithItemShipmentId(
            $orderTransfer,
            $restOrderDetailsAttributesTransfer,
        );

        $restOrderDetailsAttributesTransfer = $this->expandRestOrderDetailsAttributesTransferWithExpenseShipmentId(
            $orderTransfer,
            $restOrderDetailsAttributesTransfer,
        );

        return $restOrderDetailsAttributesTransfer;
    }

    public function mapShipmentGroupTransferToRestOrderShipmentsAttributesTransfer(
        ShipmentGroupTransfer $shipmentGroupTransfer,
        RestOrderShipmentsAttributesTransfer $restOrderShipmentsAttributesTransfer
    ): RestOrderShipmentsAttributesTransfer {
        $itemUuids = [];
        foreach ($shipmentGroupTransfer->getItems() as $itemTransfer) {
            $itemUuids[] = $itemTransfer->getUuid();
        }

        $shipmentTransfer = $shipmentGroupTransfer->getShipment();
        $restOrderShipmentsAttributesTransfer
            ->fromArray($shipmentTransfer->toArray(), true)
            ->setItemUuids($itemUuids)
            ->setMethodName($shipmentTransfer->getMethod()->getName())
            ->setCarrierName($shipmentTransfer->getCarrier()->getName());

        $restOrderShipmentsAttributesTransfer
            ->getShippingAddress()
            ->setCountry($shipmentTransfer->getShippingAddress()->getCountry()->getName());

        return $restOrderShipmentsAttributesTransfer;
    }

    protected function expandRestOrderDetailsAttributesTransferWithItemShipmentId(
        OrderTransfer $orderTransfer,
        RestOrderDetailsAttributesTransfer $restOrderDetailsAttributesTransfer
    ): RestOrderDetailsAttributesTransfer {
        foreach ($restOrderDetailsAttributesTransfer->getItems() as $key => $restOrderItemsAttributesTransfer) {
            foreach ($orderTransfer->getItems() as $itemTransfer) {
                if (!$itemTransfer->getShipment() || $restOrderItemsAttributesTransfer->getUuid() !== $itemTransfer->getUuid()) {
                    continue;
                }

                $restOrderItemsAttributesTransfer->setIdShipment($itemTransfer->getShipment()->getIdSalesShipment());
                $restOrderDetailsAttributesTransfer->getItems()->offsetSet($key, $restOrderItemsAttributesTransfer);

                break;
            }
        }

        return $restOrderDetailsAttributesTransfer;
    }

    protected function expandRestOrderDetailsAttributesTransferWithExpenseShipmentId(
        OrderTransfer $orderTransfer,
        RestOrderDetailsAttributesTransfer $restOrderDetailsAttributesTransfer
    ): RestOrderDetailsAttributesTransfer {
        foreach ($restOrderDetailsAttributesTransfer->getExpenses() as $key => $restOrderExpensesAttributesTransfer) {
            foreach ($orderTransfer->getExpenses() as $expenseTransfer) {
                if (!$expenseTransfer->getShipment() || $restOrderExpensesAttributesTransfer->getIdSalesExpense() !== $expenseTransfer->getIdSalesExpense()) {
                    continue;
                }

                $restOrderExpensesAttributesTransfer->setIdShipment($expenseTransfer->getShipment()->getIdSalesShipment());
                $restOrderDetailsAttributesTransfer->getExpenses()->offsetSet($key, $restOrderExpensesAttributesTransfer);

                break;
            }
        }

        return $restOrderDetailsAttributesTransfer;
    }
}
