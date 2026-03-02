<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ShipmentsRestApi\Processor\Mapper;

use ArrayObject;
use Generated\Shared\Transfer\RestCheckoutDataResponseAttributesTransfer;
use Generated\Shared\Transfer\RestCheckoutDataTransfer;
use Generated\Shared\Transfer\RestCheckoutRequestAttributesTransfer;

interface ShipmentMethodMapperInterface
{
    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ShipmentMethodTransfer> $shipmentMethodTransfers
     * @param array<\Generated\Shared\Transfer\RestShipmentMethodsAttributesTransfer> $restShipmentMethodsAttributesTransfers
     *
     * @return array<\Generated\Shared\Transfer\RestShipmentMethodsAttributesTransfer>
     */
    public function mapShipmentMethodTransfersToRestShipmentMethodsAttributesTransfers(
        ArrayObject $shipmentMethodTransfers,
        array $restShipmentMethodsAttributesTransfers = []
    ): array;

    public function mapRestCheckoutDataResponseTransferToRestCheckoutDataResponseAttributesTransfer(
        RestCheckoutDataTransfer $restCheckoutDataTransfer,
        RestCheckoutRequestAttributesTransfer $restCheckoutRequestAttributesTransfer,
        RestCheckoutDataResponseAttributesTransfer $restCheckoutDataResponseAttributesTransfer
    ): RestCheckoutDataResponseAttributesTransfer;
}
