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

    protected const string KEY_COUNTRY = 'country';

    protected const string KEY_COUNTRY_NAME = 'name';

    protected const string KEY_ISO2_CODE = 'iso2Code';

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
            $shippingAddress = $this->flattenCountry($shippingAddress);
            $shippingAddress = $this->nullifyEmptyAddressFields($shippingAddress);
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

    /**
     * @param array<string, mixed> $shippingAddress
     *
     * @return array<string, mixed>
     */
    protected function flattenCountry(array $shippingAddress): array
    {
        $country = $shippingAddress[static::KEY_COUNTRY] ?? null;

        if (!is_array($country)) {
            return $shippingAddress;
        }

        $shippingAddress[static::KEY_COUNTRY] = $country[static::KEY_COUNTRY_NAME] ?? null;
        $shippingAddress[static::KEY_ISO2_CODE] ??= $country[static::KEY_ISO2_CODE] ?? null;

        return $shippingAddress;
    }

    /**
     * The shipment address array carries empty strings for unset optional fields (e.g. `email`,
     * `poBox`), but the legacy contract emits them as `null`. Reduce empty strings to null so the
     * serialized address matches the established response shape.
     *
     * @param array<string, mixed> $shippingAddress
     *
     * @return array<string, mixed>
     */
    protected function nullifyEmptyAddressFields(array $shippingAddress): array
    {
        foreach ($shippingAddress as $key => $value) {
            if ($value === '') {
                $shippingAddress[$key] = null;
            }
        }

        return $shippingAddress;
    }
}
