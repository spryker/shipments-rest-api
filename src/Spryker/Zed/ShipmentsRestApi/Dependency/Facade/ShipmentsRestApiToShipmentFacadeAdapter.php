<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ShipmentsRestApi\Dependency\Facade;

use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\ShipmentMethodsCollectionTransfer;

class ShipmentsRestApiToShipmentFacadeAdapter implements ShipmentsRestApiToShipmentFacadeInterface
{
    /**
     * @var \Spryker\Zed\Shipment\Business\ShipmentFacadeInterface
     */
    protected $shipmentFacade;

    /**
     * @param \Spryker\Zed\Shipment\Business\ShipmentFacadeInterface $shipmentFacade
     */
    public function __construct($shipmentFacade)
    {
        $this->shipmentFacade = $shipmentFacade;
    }

    /**
     * @param int $idShipmentMethod
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\ShipmentMethodTransfer|null
     */
    public function findAvailableMethodById($idShipmentMethod, QuoteTransfer $quoteTransfer)
    {
        return $this->shipmentFacade->findAvailableMethodById($idShipmentMethod, $quoteTransfer);
    }

    /**
     * @param int $idShipmentMethod
     *
     * @return \Generated\Shared\Transfer\ShipmentMethodTransfer|null
     */
    public function findMethodById($idShipmentMethod)
    {
        return $this->shipmentFacade->findMethodById($idShipmentMethod);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\ShipmentMethodsCollectionTransfer
     */
    public function getAvailableMethodsByShipment(QuoteTransfer $quoteTransfer): ShipmentMethodsCollectionTransfer
    {
        //Added for BC reason
        if (!method_exists($this->shipmentFacade, 'getAvailableMethodsByShipment')) {
            return new ShipmentMethodsCollectionTransfer();
        }

        return $this->shipmentFacade->getAvailableMethodsByShipment($quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function expandQuoteWithShipmentGroups(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        //Added for BC reason
        if (!method_exists($this->shipmentFacade, 'expandQuoteWithShipmentGroups')) {
            return $quoteTransfer;
        }

        return $this->shipmentFacade->expandQuoteWithShipmentGroups($quoteTransfer);
    }
}
