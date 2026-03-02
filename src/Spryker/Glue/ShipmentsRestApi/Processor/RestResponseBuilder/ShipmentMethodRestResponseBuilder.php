<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ShipmentsRestApi\Processor\RestResponseBuilder;

use Generated\Shared\Transfer\RestShipmentMethodsAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\ShipmentsRestApi\ShipmentsRestApiConfig;

class ShipmentMethodRestResponseBuilder implements ShipmentMethodRestResponseBuilderInterface
{
    /**
     * @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface
     */
    protected $restResourceBuilder;

    public function __construct(RestResourceBuilderInterface $restResourceBuilder)
    {
        $this->restResourceBuilder = $restResourceBuilder;
    }

    public function createShipmentMethodRestResource(
        string $idShipmentMethod,
        RestShipmentMethodsAttributesTransfer $restShipmentMethodsAttributesTransfer
    ): RestResourceInterface {
        return $this->restResourceBuilder->createRestResource(
            ShipmentsRestApiConfig::RESOURCE_SHIPMENT_METHODS,
            $idShipmentMethod,
            $restShipmentMethodsAttributesTransfer,
        );
    }
}
