<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ShipmentsRestApi\Api\Storefront\Relationship;

use Generated\Api\Storefront\ShipmentsStorefrontResource;
use Spryker\ApiPlatform\Relationship\AbstractRelationshipResolver;
use Spryker\Service\Serializer\SerializerServiceInterface;

class CheckoutDataShipmentsRelationshipResolver extends AbstractRelationshipResolver
{
    protected const string RELATIONSHIP_DATA_PROPERTY = 'shipmentGroupContexts';

    public function __construct(
        protected SerializerServiceInterface $serializer,
    ) {
    }

    /**
     * @return array<\Generated\Api\Storefront\ShipmentsStorefrontResource>
     */
    protected function resolveRelationship(): array
    {
        $resources = [];

        foreach ($this->getParentResources() as $parent) {
            if (!property_exists($parent, static::RELATIONSHIP_DATA_PROPERTY)) {
                continue;
            }

            foreach ($parent->{static::RELATIONSHIP_DATA_PROPERTY} ?? [] as $index => $shipmentGroupContext) {
                $resources[] = $this->buildShipmentResource($shipmentGroupContext, $index);
            }
        }

        return $resources;
    }

    /**
     * @param array<string, mixed> $shipmentGroupContext
     */
    protected function buildShipmentResource(array $shipmentGroupContext, int $fallbackIndex): ShipmentsStorefrontResource
    {
        $shipment = $shipmentGroupContext['shipment'] ?? [];
        $method = $shipment['method'] ?? null;

        $selectedShipmentMethod = [];
        if (is_array($method) && ($method['idShipmentMethod'] ?? null) !== null) {
            $selectedShipmentMethod = [
                'id' => $method['idShipmentMethod'],
                'name' => $method['name'] ?? null,
                'carrierName' => $method['carrierName'] ?? null,
                'price' => $method['storeCurrencyPrice'] ?? null,
                'taxRate' => $method['taxRate'] ?? null,
                'deliveryTime' => $method['deliveryTime'] ?? null,
                'currencyIsoCode' => $method['currencyIsoCode'] ?? null,
            ];
        }

        $items = [];
        foreach ($shipmentGroupContext['items'] ?? [] as $item) {
            $groupKey = $item['groupKey'] ?? null;
            if ($groupKey !== null) {
                $items[] = $groupKey;
            }
        }

        $shippingAddress = $shipment['shippingAddress'] ?? null;
        if (is_array($shippingAddress)) {
            $shippingAddress = $this->applyLegacyCompanyBusinessUnitAddressAlias($shippingAddress);
        }

        return $this->serializer->denormalize(
            [
                'shipmentsId' => $shipmentGroupContext['hash'] ?? (string)$fallbackIndex,
                'items' => $items,
                'selectedShipmentMethod' => $selectedShipmentMethod,
                'shippingAddress' => $shippingAddress,
                'requestedDeliveryDate' => $shipment['requestedDeliveryDate'] ?? null,
                'context' => $shipmentGroupContext,
            ],
            ShipmentsStorefrontResource::class,
        );
    }

    /**
     * Legacy REST API exposed the company-business-unit-address UUID under the key
     * `idCompanyBusinessUnitAddress`. Preserve that contract.
     *
     * @param array<string, mixed> $shippingAddress
     *
     * @return array<string, mixed>
     */
    protected function applyLegacyCompanyBusinessUnitAddressAlias(array $shippingAddress): array
    {
        if (!empty($shippingAddress['companyBusinessUnitAddressUuid'])) {
            $shippingAddress['idCompanyBusinessUnitAddress'] = $shippingAddress['companyBusinessUnitAddressUuid'];
        }

        return $shippingAddress;
    }
}
