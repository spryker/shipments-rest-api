<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ShipmentsRestApi\Plugin\OrdersRestApi;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\RestOrderDetailsAttributesTransfer;
use Spryker\Glue\Kernel\AbstractPlugin;
use Spryker\Glue\OrdersRestApiExtension\Dependency\Plugin\RestOrderDetailsAttributesMapperPluginInterface;

/**
 * @method \Spryker\Glue\ShipmentsRestApi\ShipmentsRestApiFactory getFactory()
 */
class ShipmentRestOrderDetailsAttributesMapperPlugin extends AbstractPlugin implements RestOrderDetailsAttributesMapperPluginInterface
{
    /**
     * {@inheritDoc}
     * - Maps `RestOrderDetailsAttributesTransfer.items[*].idShipment` from `OrderTransfer.items[*].shipment.idSalesShipment`.
     * - Maps `RestOrderDetailsAttributesTransfer.expenses[*].idShipment` from `OrderTransfer.expenses[*].shipment.idSalesShipment`.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\RestOrderDetailsAttributesTransfer $restOrderDetailsAttributesTransfer
     *
     * @return \Generated\Shared\Transfer\RestOrderDetailsAttributesTransfer
     */
    public function mapOrderTransferToRestOrderDetailsAttributesTransfer(
        OrderTransfer $orderTransfer,
        RestOrderDetailsAttributesTransfer $restOrderDetailsAttributesTransfer
    ): RestOrderDetailsAttributesTransfer {
        return $this->getFactory()
            ->createOrderShipmentMapper()
            ->mapOrderTransferToRestOrderDetailsAttributesTransfer(
                $orderTransfer,
                $restOrderDetailsAttributesTransfer,
            );
    }
}
