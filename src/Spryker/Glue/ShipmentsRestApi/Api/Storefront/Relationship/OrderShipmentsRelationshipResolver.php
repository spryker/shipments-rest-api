<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ShipmentsRestApi\Api\Storefront\Relationship;

use Generated\Api\Storefront\OrderShipmentsStorefrontResource;
use Generated\Api\Storefront\OrdersStorefrontResource;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\RestOrderShipmentsAttributesTransfer;
use Generated\Shared\Transfer\ShipmentGroupTransfer;
use Spryker\ApiPlatform\Relationship\AbstractRelationshipResolver;
use Spryker\ApiPlatform\Relationship\PerItemRelationshipResolverInterface;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Spryker\Service\Shipment\ShipmentServiceInterface;

class OrderShipmentsRelationshipResolver extends AbstractRelationshipResolver implements PerItemRelationshipResolverInterface
{
    public function __construct(
        protected SerializerServiceInterface $serializer,
        protected ShipmentServiceInterface $shipmentService,
    ) {
    }

    /**
     * @return array<\Generated\Api\Storefront\OrderShipmentsStorefrontResource>
     */
    protected function resolveRelationship(): array
    {
        $allShipments = [];

        /** @var array<\Generated\Api\Storefront\OrdersStorefrontResource> $parentResources */
        $parentResources = $this->parentResources;

        foreach ($this->resolvePerItem($parentResources, $this->context) as $shipments) {
            array_push($allShipments, ...$shipments);
        }

        return $allShipments;
    }

    /**
     * @param array<\Generated\Api\Storefront\OrdersStorefrontResource> $parentResources
     *
     * @return array<string, array<\Generated\Api\Storefront\OrderShipmentsStorefrontResource>>
     */
    public function resolvePerItem(array $parentResources, array $context): array
    {
        $result = [];

        foreach ($parentResources as $parent) {
            $result[$parent->orderReference] = $this->buildShipmentsFromOrder($parent);
        }

        return $result;
    }

    /**
     * @return array<\Generated\Api\Storefront\OrderShipmentsStorefrontResource>
     */
    protected function buildShipmentsFromOrder(OrdersStorefrontResource $parent): array
    {
        // Single-shipment orders carry a top-level shippingAddress; only split-shipment orders expose order-shipments
        if ($parent->shippingAddress !== null) {
            return [];
        }

        $orderTransfer = (new OrderTransfer())->fromArray($parent->context ?? [], true);

        $resources = [];

        foreach ($this->shipmentService->groupItemsByShipment($orderTransfer->getItems()) as $shipmentGroupTransfer) {
            $resources[] = $this->serializer->denormalize(
                $this->prepareShipmentGroupResourceData($shipmentGroupTransfer),
                OrderShipmentsStorefrontResource::class,
            );
        }

        return $resources;
    }

    /**
     * @return array<string, mixed>
     */
    protected function prepareShipmentGroupResourceData(ShipmentGroupTransfer $shipmentGroupTransfer): array
    {
        $shipmentTransfer = $shipmentGroupTransfer->getShipment();

        $itemUuids = [];
        foreach ($shipmentGroupTransfer->getItems() as $itemTransfer) {
            $itemUuids[] = $itemTransfer->getUuid();
        }

        $restOrderShipmentsAttributesTransfer = (new RestOrderShipmentsAttributesTransfer())
            ->fromArray($shipmentTransfer->toArray(), true)
            ->setItemUuids($itemUuids)
            ->setMethodName($shipmentTransfer->getMethod()->getName())
            ->setCarrierName($shipmentTransfer->getCarrier()->getName());

        $restOrderShipmentsAttributesTransfer
            ->getShippingAddress()
            ->setCountry($shipmentTransfer->getShippingAddress()->getCountry()->getName());

        $data = $restOrderShipmentsAttributesTransfer->toArray(true, true);
        $data['orderShipmentId'] = (string)$shipmentTransfer->getIdSalesShipment();

        return $data;
    }
}
