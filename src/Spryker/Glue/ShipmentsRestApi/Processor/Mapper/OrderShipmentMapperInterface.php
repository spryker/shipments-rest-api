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

interface OrderShipmentMapperInterface
{
    public function mapOrderTransferToRestOrderDetailsAttributesTransfer(
        OrderTransfer $orderTransfer,
        RestOrderDetailsAttributesTransfer $restOrderDetailsAttributesTransfer
    ): RestOrderDetailsAttributesTransfer;

    public function mapShipmentGroupTransferToRestOrderShipmentsAttributesTransfer(
        ShipmentGroupTransfer $shipmentGroupTransfer,
        RestOrderShipmentsAttributesTransfer $restOrderShipmentsAttributesTransfer
    ): RestOrderShipmentsAttributesTransfer;
}
