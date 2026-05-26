<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ShipmentsRestApi\Api\Storefront\Relationship;

use Generated\Api\Storefront\ShipmentMethodsStorefrontResource;
use Generated\Api\Storefront\ShipmentsStorefrontResource;
use Spryker\ApiPlatform\Relationship\AbstractRelationshipResolver;
use Spryker\ApiPlatform\Relationship\PerItemRelationshipResolverInterface;
use Spryker\Service\Serializer\SerializerServiceInterface;

class ShipmentMethodsByShipmentRelationshipResolver extends AbstractRelationshipResolver implements PerItemRelationshipResolverInterface
{
    public function __construct(
        protected SerializerServiceInterface $serializer,
    ) {
    }

    /**
     * @return array<\Generated\Api\Storefront\ShipmentMethodsStorefrontResource>
     */
    protected function resolveRelationship(): array
    {
        $allShipmentMethods = [];

        /** @var array<\Generated\Api\Storefront\ShipmentsStorefrontResource> $parentResources */
        $parentResources = $this->parentResources;

        foreach ($this->resolvePerItem($parentResources, $this->context) as $shipmentMethods) {
            array_push($allShipmentMethods, ...$shipmentMethods);
        }

        return $allShipmentMethods;
    }

    /**
     * @param array<\Generated\Api\Storefront\ShipmentsStorefrontResource> $parentResources
     *
     * @return array<string, array<\Generated\Api\Storefront\ShipmentMethodsStorefrontResource>>
     */
    public function resolvePerItem(array $parentResources, array $context): array
    {
        $result = [];

        foreach ($parentResources as $parent) {
            $result[$parent->shipmentsId] = $this->buildShipmentMethodsFromShipment($parent);
        }

        return $result;
    }

    /**
     * @return array<\Generated\Api\Storefront\ShipmentMethodsStorefrontResource>
     */
    protected function buildShipmentMethodsFromShipment(ShipmentsStorefrontResource $parent): array
    {
        $methods = $parent->context['availableShipmentMethods']['methods'] ?? [];

        $resources = [];

        foreach ($methods as $methodRow) {
            $idShipmentMethod = $methodRow['idShipmentMethod'] ?? null;
            if ($idShipmentMethod === null) {
                continue;
            }

            $resources[] = $this->serializer->denormalize(
                [
                    'idShipmentMethod' => (string)$idShipmentMethod,
                    'name' => $methodRow['name'] ?? null,
                    'carrierName' => $methodRow['carrierName'] ?? null,
                    'price' => $methodRow['storeCurrencyPrice'] ?? null,
                    'taxRate' => $methodRow['taxRate'] ?? null,
                    'deliveryTime' => $methodRow['deliveryTime'] ?? null,
                    'currencyIsoCode' => $methodRow['currencyIsoCode'] ?? null,
                ],
                ShipmentMethodsStorefrontResource::class,
            );
        }

        return $resources;
    }
}
