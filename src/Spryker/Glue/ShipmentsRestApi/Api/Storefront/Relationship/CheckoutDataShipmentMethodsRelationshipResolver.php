<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\ShipmentsRestApi\Api\Storefront\Relationship;

use Generated\Api\Storefront\ShipmentMethodsStorefrontResource;
use Spryker\ApiPlatform\Relationship\AbstractRelationshipResolver;
use Spryker\Service\Serializer\SerializerServiceInterface;

class CheckoutDataShipmentMethodsRelationshipResolver extends AbstractRelationshipResolver
{
    protected const string RELATIONSHIP_DATA_PROPERTY = 'shipmentMethodContexts';

    public function __construct(
        protected SerializerServiceInterface $serializer,
    ) {
    }

    /**
     * @return array<\Generated\Api\Storefront\ShipmentMethodsStorefrontResource>
     */
    protected function resolveRelationship(): array
    {
        $resources = [];

        foreach ($this->getParentResources() as $parent) {
            if (!property_exists($parent, static::RELATIONSHIP_DATA_PROPERTY)) {
                continue;
            }

            foreach ($parent->{static::RELATIONSHIP_DATA_PROPERTY} ?? [] as $shipmentMethodContext) {
                $resources[] = $this->buildShipmentMethodResource($shipmentMethodContext);
            }
        }

        return $resources;
    }

    /**
     * @param array<string, mixed> $shipmentMethodContext
     */
    protected function buildShipmentMethodResource(array $shipmentMethodContext): ShipmentMethodsStorefrontResource
    {
        return $this->serializer->denormalize(
            [
                'idShipmentMethod' => (string)($shipmentMethodContext['idShipmentMethod'] ?? ''),
                'name' => $shipmentMethodContext['name'] ?? null,
                'carrierName' => $shipmentMethodContext['carrierName'] ?? null,
                'price' => $shipmentMethodContext['storeCurrencyPrice'] ?? null,
                'taxRate' => $shipmentMethodContext['taxRate'] ?? null,
                'deliveryTime' => $shipmentMethodContext['deliveryTime'] ?? null,
                'currencyIsoCode' => $shipmentMethodContext['currencyIsoCode'] ?? null,
            ],
            ShipmentMethodsStorefrontResource::class,
        );
    }
}
