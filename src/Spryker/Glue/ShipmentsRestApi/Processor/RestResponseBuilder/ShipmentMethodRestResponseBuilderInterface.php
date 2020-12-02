<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ShipmentsRestApi\Processor\RestResponseBuilder;

use Generated\Shared\Transfer\RestShipmentMethodsAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;

interface ShipmentMethodRestResponseBuilderInterface
{
    /**
     * @param string $idShipmentMethod
     * @param \Generated\Shared\Transfer\RestShipmentMethodsAttributesTransfer $restShipmentMethodsAttributesTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface
     */
    public function createShipmentMethodRestResource(
        string $idShipmentMethod,
        RestShipmentMethodsAttributesTransfer $restShipmentMethodsAttributesTransfer
    ): RestResourceInterface;
}
