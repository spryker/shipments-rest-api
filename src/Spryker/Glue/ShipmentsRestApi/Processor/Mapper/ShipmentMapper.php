<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ShipmentsRestApi\Processor\Mapper;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\RestAddressTransfer;
use Generated\Shared\Transfer\RestShipmentMethodTransfer;
use Generated\Shared\Transfer\RestShipmentsAttributesTransfer;
use Generated\Shared\Transfer\ShipmentGroupTransfer;

class ShipmentMapper implements ShipmentMapperInterface
{
    /**
     * @var list<\Spryker\Glue\ShipmentsRestApiExtension\Dependency\Plugin\RestAddressResponseMapperPluginInterface>
     */
    protected array $restAddressResponseMapperPlugins;

    /**
     * @param list<\Spryker\Glue\ShipmentsRestApiExtension\Dependency\Plugin\RestAddressResponseMapperPluginInterface> $restAddressResponseMapperPlugins
     */
    public function __construct(array $restAddressResponseMapperPlugins)
    {
        $this->restAddressResponseMapperPlugins = $restAddressResponseMapperPlugins;
    }

    public function mapShipmentGroupTransferToRestShipmentsAttributesTransfers(
        ShipmentGroupTransfer $shipmentGroupTransfer,
        RestShipmentsAttributesTransfer $restShipmentsAttributesTransfer
    ): RestShipmentsAttributesTransfer {
        $restShipmentsAttributesTransfer
            ->fromArray($shipmentGroupTransfer->getShipment()->toArray(), true)
            ->setItems($this->getItemsGroupKeys($shipmentGroupTransfer))
            ->setShippingAddress($this->createRestAddressTransfer($shipmentGroupTransfer))
            ->setSelectedShipmentMethod($this->createRestShipmentMethodTransfer($shipmentGroupTransfer));

        return $restShipmentsAttributesTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentGroupTransfer $shipmentGroupTransfer
     *
     * @return array<string>
     */
    protected function getItemsGroupKeys(ShipmentGroupTransfer $shipmentGroupTransfer): array
    {
        $groupKeys = [];
        foreach ($shipmentGroupTransfer->getItems() as $itemTransfer) {
            $groupKeys[] = $itemTransfer->getGroupKey();
        }

        return $groupKeys;
    }

    protected function createRestAddressTransfer(ShipmentGroupTransfer $shipmentGroupTransfer): RestAddressTransfer
    {
        $addressTransfer = $shipmentGroupTransfer->getShipment()->getShippingAddress();
        if (!$addressTransfer) {
            return new RestAddressTransfer();
        }

        $restAddressTransfer = (new RestAddressTransfer())
            ->fromArray($addressTransfer->toArray(), true)
            ->setId($addressTransfer->getUuid());

        return $this->executeRestAddressResponseMapperPlugins($addressTransfer, $restAddressTransfer);
    }

    protected function createRestShipmentMethodTransfer(
        ShipmentGroupTransfer $shipmentGroupTransfer
    ): RestShipmentMethodTransfer {
        $shipmentMethodTransfer = $shipmentGroupTransfer->getShipment()->getMethod();
        if (!$shipmentMethodTransfer) {
            return new RestShipmentMethodTransfer();
        }

        return (new RestShipmentMethodTransfer())
            ->fromArray($shipmentMethodTransfer->toArray(), true)
            ->setPrice($shipmentMethodTransfer->getStoreCurrencyPrice())
            ->setId($shipmentMethodTransfer->getIdShipmentMethod());
    }

    protected function executeRestAddressResponseMapperPlugins(
        AddressTransfer $addressTransfer,
        RestAddressTransfer $restAddressTransfer
    ): RestAddressTransfer {
        foreach ($this->restAddressResponseMapperPlugins as $restAddressResponseMapperPlugin) {
            $restAddressTransfer = $restAddressResponseMapperPlugin->map($addressTransfer, $restAddressTransfer);
        }

        return $restAddressTransfer;
    }
}
