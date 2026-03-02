<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ShipmentsRestApi\Processor\Mapper;

use Generated\Shared\Transfer\RestShipmentsAttributesTransfer;
use Generated\Shared\Transfer\ShipmentGroupTransfer;

interface ShipmentMapperInterface
{
    public function mapShipmentGroupTransferToRestShipmentsAttributesTransfers(
        ShipmentGroupTransfer $shipmentGroupTransfer,
        RestShipmentsAttributesTransfer $restShipmentsAttributesTransfer
    ): RestShipmentsAttributesTransfer;
}
