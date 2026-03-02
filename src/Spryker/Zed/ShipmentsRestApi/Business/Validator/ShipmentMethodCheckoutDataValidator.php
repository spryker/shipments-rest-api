<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ShipmentsRestApi\Business\Validator;

use Generated\Shared\Transfer\CheckoutDataTransfer;
use Generated\Shared\Transfer\CheckoutErrorTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Spryker\Zed\ShipmentsRestApi\Dependency\Facade\ShipmentsRestApiToShipmentFacadeInterface;

class ShipmentMethodCheckoutDataValidator implements ShipmentMethodCheckoutDataValidatorInterface
{
    /**
     * @var \Spryker\Zed\ShipmentsRestApi\Dependency\Facade\ShipmentsRestApiToShipmentFacadeInterface
     */
    protected $shipmentFacade;

    public function __construct(ShipmentsRestApiToShipmentFacadeInterface $shipmentFacade)
    {
        $this->shipmentFacade = $shipmentFacade;
    }

    public function validateShipmentMethodCheckoutData(CheckoutDataTransfer $checkoutDataTransfer): CheckoutResponseTransfer
    {
        $checkoutResponseTransfer = (new CheckoutResponseTransfer())->setIsSuccess(true);

        if (!$checkoutDataTransfer->getShipment()) {
            return $checkoutResponseTransfer;
        }

        $shipmentMethodTransfer = $this->shipmentFacade
            ->findMethodById($checkoutDataTransfer->getShipment()->getIdShipmentMethod());

        if (!$shipmentMethodTransfer) {
            return $this->addErrorToCheckoutResponseTransfer(
                $checkoutResponseTransfer,
                'shipment.validation.not_found',
            );
        }

        return $checkoutResponseTransfer;
    }

    protected function addErrorToCheckoutResponseTransfer(
        CheckoutResponseTransfer $checkoutResponseTransfer,
        string $message
    ): CheckoutResponseTransfer {
        return $checkoutResponseTransfer
            ->setIsSuccess(false)
            ->addError((new CheckoutErrorTransfer())
                ->setMessage($message));
    }
}
