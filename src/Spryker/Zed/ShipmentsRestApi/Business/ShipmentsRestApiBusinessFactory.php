<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ShipmentsRestApi\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\ShipmentsRestApi\Business\Expander\CheckoutDataExpander;
use Spryker\Zed\ShipmentsRestApi\Business\Expander\CheckoutDataExpanderInterface;
use Spryker\Zed\ShipmentsRestApi\Business\Mapper\ShipmentQuoteItemMapper;
use Spryker\Zed\ShipmentsRestApi\Business\Mapper\ShipmentQuoteItemMapperInterface;
use Spryker\Zed\ShipmentsRestApi\Business\Quote\ShipmentQuoteMapper;
use Spryker\Zed\ShipmentsRestApi\Business\Quote\ShipmentQuoteMapperInterface;
use Spryker\Zed\ShipmentsRestApi\Business\Validator\CartItemCheckoutDataValidator;
use Spryker\Zed\ShipmentsRestApi\Business\Validator\CartItemCheckoutDataValidatorInterface;
use Spryker\Zed\ShipmentsRestApi\Business\Validator\ShipmentMethodCheckoutDataValidator;
use Spryker\Zed\ShipmentsRestApi\Business\Validator\ShipmentMethodCheckoutDataValidatorInterface;
use Spryker\Zed\ShipmentsRestApi\Dependency\Facade\ShipmentsRestApiToShipmentFacadeInterface;
use Spryker\Zed\ShipmentsRestApi\ShipmentsRestApiDependencyProvider;

/**
 * @method \Spryker\Zed\ShipmentsRestApi\ShipmentsRestApiConfig getConfig()
 */
class ShipmentsRestApiBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\ShipmentsRestApi\Business\Expander\CheckoutDataExpanderInterface
     */
    public function createCheckoutDataExpander(): CheckoutDataExpanderInterface
    {
        return new CheckoutDataExpander($this->getShipmentFacade());
    }

    /**
     * @return \Spryker\Zed\ShipmentsRestApi\Business\Quote\ShipmentQuoteMapperInterface
     */
    public function createShipmentQuoteMapper(): ShipmentQuoteMapperInterface
    {
        return new ShipmentQuoteMapper($this->getShipmentFacade());
    }

    /**
     * @return \Spryker\Zed\ShipmentsRestApi\Business\Mapper\ShipmentQuoteItemMapperInterface
     */
    public function createShipmentQuoteItemMapper(): ShipmentQuoteItemMapperInterface
    {
        return new ShipmentQuoteItemMapper(
            $this->getShipmentFacade(),
            $this->getAddressProviderStrategyPlugins(),
        );
    }

    /**
     * @return \Spryker\Zed\ShipmentsRestApi\Business\Validator\CartItemCheckoutDataValidatorInterface
     */
    public function createCartItemCheckoutDataValidator(): CartItemCheckoutDataValidatorInterface
    {
        return new CartItemCheckoutDataValidator();
    }

    /**
     * @return \Spryker\Zed\ShipmentsRestApi\Business\Validator\ShipmentMethodCheckoutDataValidatorInterface
     */
    public function createShipmentMethodCheckoutDataValidator(): ShipmentMethodCheckoutDataValidatorInterface
    {
        return new ShipmentMethodCheckoutDataValidator($this->getShipmentFacade());
    }

    /**
     * @return \Spryker\Zed\ShipmentsRestApi\Dependency\Facade\ShipmentsRestApiToShipmentFacadeInterface
     */
    public function getShipmentFacade(): ShipmentsRestApiToShipmentFacadeInterface
    {
        return $this->getProvidedDependency(ShipmentsRestApiDependencyProvider::FACADE_SHIPMENT);
    }

    /**
     * @return array<\Spryker\Zed\ShipmentsRestApiExtension\Dependency\Plugin\AddressProviderStrategyPluginInterface>
     */
    public function getAddressProviderStrategyPlugins(): array
    {
        return $this->getProvidedDependency(ShipmentsRestApiDependencyProvider::PLUGINS_ADDRESS_PROVIDER_STRATEGY);
    }
}
