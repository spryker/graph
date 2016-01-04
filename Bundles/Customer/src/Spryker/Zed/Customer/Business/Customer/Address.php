<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Customer\Business\Customer;

use Generated\Shared\Transfer\AddressesTransfer;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Exception\PropelException;
use Spryker\Zed\Customer\Persistence\CustomerQueryContainer;
use Spryker\Zed\Kernel\Persistence\QueryContainer\QueryContainerInterface;
use Spryker\Zed\Customer\Business\Exception\AddressNotFoundException;
use Spryker\Zed\Customer\Business\Exception\CountryNotFoundException;
use Spryker\Zed\Customer\Business\Exception\CustomerNotFoundException;
use Spryker\Zed\Customer\Dependency\Facade\CustomerToCountryInterface;
use Spryker\Zed\Customer\Dependency\Facade\CustomerToLocaleInterface;
use Orm\Zed\Customer\Persistence\SpyCustomer;
use Orm\Zed\Customer\Persistence\SpyCustomerAddress;

class Address
{

    /**
     * @var CustomerQueryContainer
     */
    protected $queryContainer;

    /**
     * @var CustomerToCountryInterface
     */
    private $countryFacade;

    /**
     * @var CustomerToLocaleInterface
     */
    private $localeFacade;

    /**
     * @param QueryContainerInterface $queryContainer
     * @param CustomerToCountryInterface $countryFacade
     * @param CustomerToLocaleInterface $localeFacade
     */
    public function __construct(QueryContainerInterface $queryContainer, CustomerToCountryInterface $countryFacade, CustomerToLocaleInterface $localeFacade)
    {
        $this->queryContainer = $queryContainer;
        $this->countryFacade = $countryFacade;
        $this->localeFacade = $localeFacade;
    }

    /**
     * @param AddressTransfer $addressTransfer
     *
     * @throws CustomerNotFoundException
     * @throws PropelException
     *
     * @return AddressTransfer
     */
    public function createAddress(AddressTransfer $addressTransfer)
    {
        $customerEntity = $this->getCustomerFromAddressTransfer($addressTransfer);

        $addressEntity = $this->createCustomerAddress($addressTransfer, $customerEntity);

        $this->updateCustomerDefaultAddresses($addressTransfer, $customerEntity, $addressEntity);

        return $this->entityToAddressTransfer($addressEntity);
    }

    /**
     * @param AddressTransfer $addressTransfer
     *
     * @throws AddressNotFoundException
     *
     * @return AddressTransfer
     */
    public function getAddress(AddressTransfer $addressTransfer)
    {
        $idCustomer = $addressTransfer->getFkCustomer();

        return $this->getAddressTransferById($addressTransfer->getIdCustomerAddress(), $idCustomer);
    }

    /**
     * @param int $idAddress
     * @param int|null $idCustomer
     *
     * @throws AddressNotFoundException
     *
     * @return AddressTransfer
     */
    protected function getAddressTransferById($idAddress, $idCustomer = null)
    {
        $addressQuery = $this->queryContainer->queryAddress($idAddress);
        if ($idCustomer !== null) {
            $addressQuery->filterByFkCustomer($idCustomer);
        }
        $addressEntity = $addressQuery->findOne();

        if ($addressEntity === null) {
            throw new AddressNotFoundException();
        }

        $addressTransfer = $this->entityToAddressTransfer($addressEntity);
        $addressTransfer->setIso2Code($addressEntity->getCountry()->getIso2Code());

        return $addressTransfer;
    }

    /**
     * @param CustomerTransfer $customerTransfer
     *
     * @return AddressesTransfer
     */
    public function getAddresses(CustomerTransfer $customerTransfer)
    {
        $entities = $this->queryContainer->queryAddresses()
            ->filterByFkCustomer($customerTransfer->getIdCustomer())
            ->find();

        return $this->entityCollectionToTransferCollection($entities);
    }

    /**
     * @param AddressTransfer $addressTransfer
     *
     * @throws AddressNotFoundException
     * @throws CustomerNotFoundException
     * @throws PropelException
     *
     * @return AddressTransfer
     */
    public function updateAddress(AddressTransfer $addressTransfer)
    {
        $customer = $this->getCustomerFromAddressTransfer($addressTransfer);

        $addressEntity = $this->updateCustomerAddress($addressTransfer, $customer);

        return $this->entityToAddressTransfer($addressEntity);
    }

    /**
     * @param AddressTransfer $addressTransfer
     *
     * @throws AddressNotFoundException
     * @throws CustomerNotFoundException
     * @throws PropelException
     *
     * @return bool
     */
    public function setDefaultShippingAddress(AddressTransfer $addressTransfer)
    {
        $customer = $this->getCustomerFromAddressTransfer($addressTransfer);

        $entity = $this->queryContainer->queryAddressForCustomer($addressTransfer->getIdCustomerAddress(), $customer->getEmail())
            ->findOne();

        if (!$entity) {
            throw new AddressNotFoundException();
        }

        $customer->setDefaultShippingAddress($addressTransfer->getIdCustomerAddress());
        $customer->save();

        return true;
    }

    /**
     * @param AddressTransfer $addressTransfer
     *
     * @throws AddressNotFoundException
     * @throws CustomerNotFoundException
     * @throws PropelException
     *
     * @return bool
     */
    public function setDefaultBillingAddress(AddressTransfer $addressTransfer)
    {
        $customer = $this->getCustomerFromAddressTransfer($addressTransfer);

        $entity = $this->queryContainer->queryAddressForCustomer($addressTransfer->getIdCustomerAddress(), $customer->getEmail())
            ->findOne();

        if (!$entity) {
            throw new AddressNotFoundException();
        }

        $customer->setDefaultBillingAddress($addressTransfer->getIdCustomerAddress());
        $customer->save();

        return true;
    }

    /**
     * @param AddressTransfer $addressTransfer
     *
     * @return string
     */
    public function getFormattedAddressString(AddressTransfer $addressTransfer)
    {
        return implode("\n", $this->getFormattedAddressArray($addressTransfer));
    }

    /**
     * @param AddressTransfer $addressTransfer
     *
     * @return array
     */
    public function getFormattedAddressArray(AddressTransfer $addressTransfer)
    {
        $address = [];

        if (count($addressTransfer->getCompany()) > 0) {
            $address[] = $addressTransfer->getCompany();
        }

        $address[] = sprintf('%s %s %s', $addressTransfer->getSalutation(), $addressTransfer->getFirstName(), $addressTransfer->getLastName());

        if (count($addressTransfer->getAddress1()) > 0) {
            $address[] = $addressTransfer->getAddress1();
        }
        if (count($addressTransfer->getAddress2()) > 0) {
            $address[] = $addressTransfer->getAddress2();
        }
        if (count($addressTransfer->getAddress3()) > 0) {
            $address[] = $addressTransfer->getAddress3();
        }

        $address[] = sprintf('%s %s', $addressTransfer->getZipCode(), $addressTransfer->getCity());

        return $address;
    }

    /**
     * @param SpyCustomerAddress $entity
     *
     * @return AddressTransfer
     */
    protected function entityToAddressTransfer(SpyCustomerAddress $entity)
    {
        $addressTransfer = new AddressTransfer();

        return $addressTransfer->fromArray($entity->toArray(), true);
    }

    /**
     * @param ObjectCollection $entities
     *
     * @return AddressesTransfer
     */
    protected function entityCollectionToTransferCollection(ObjectCollection $entities)
    {
        $addressTransferCollection = new AddressesTransfer();
        foreach ($entities->getData() as $entity) {
            $addressTransferCollection->addAddress($this->entityToAddressTransfer($entity));
        }

        return $addressTransferCollection;
    }

    /**
     * @param AddressTransfer $addressTransfer
     *
     * @throws CustomerNotFoundException
     *
     * @return SpyCustomer
     */
    protected function getCustomerFromAddressTransfer(AddressTransfer $addressTransfer)
    {
        if ($addressTransfer->getEmail()) {
            $customer = $this->queryContainer->queryCustomerByEmail($addressTransfer->getEmail())
                ->findOne();
        } elseif ($addressTransfer->getFkCustomer()) {
            $customer = $this->queryContainer->queryCustomerById($addressTransfer->getFkCustomer())
                ->findOne();
        }

        if (!isset($customer) || $customer === null) {
            throw new CustomerNotFoundException();
        }

        return $customer;
    }

    /**
     * @param CustomerTransfer $customerTransfer
     *
     * @throws CustomerNotFoundException
     *
     * @return SpyCustomer
     */
    protected function getCustomerFromCustomerTransfer(CustomerTransfer $customerTransfer)
    {
        if ($customerTransfer->getEmail()) {
            $customer = $this->queryContainer->queryCustomerByEmail($customerTransfer->getEmail())
                ->findOne();
        } elseif ($customerTransfer->getIdCustomer()) {
            $customer = $this->queryContainer->queryCustomerById($customerTransfer->getIdCustomer())
                ->findOne();
        }

        if (!isset($customer) || $customer === null) {
            throw new CustomerNotFoundException();
        }

        return $customer;
    }

    /**
     * @throws CountryNotFoundException
     *
     * @return int
     */
    protected function getCustomerCountryId()
    {
        $idCountry = $this->countryFacade->getIdCountryByIso2Code($this->getIsoCode());

        if ($idCountry === null) {
            throw new CountryNotFoundException();
        }

        return $idCountry;
    }

    /**
     * @param CustomerTransfer $customerTransfer
     *
     * @throws AddressNotFoundException
     *
     * @return AddressTransfer
     */
    public function getDefaultShippingAddress(CustomerTransfer $customerTransfer)
    {
        $customerEntity = $this->getCustomerFromCustomerTransfer($customerTransfer);
        $idAddress = $customerEntity->getDefaultShippingAddress();

        return $this->getAddressTransferById($idAddress);
    }

    /**
     * @param CustomerTransfer $customerTransfer
     *
     * @throws AddressNotFoundException
     *
     * @return AddressTransfer
     */
    public function getDefaultBillingAddress(CustomerTransfer $customerTransfer)
    {
        $customerEntity = $this->getCustomerFromCustomerTransfer($customerTransfer);
        $idAddress = $customerEntity->getDefaultBillingAddress();

        return $this->getAddressTransferById($idAddress);
    }

    /**
     * @return string
     */
    private function getIsoCode()
    {
        $localeName = $this->localeFacade->getCurrentLocale()
            ->getLocaleName();

        return explode('_', $localeName)[1];
    }

    /**
     * @param AddressTransfer $addressTransfer
     *
     * @throws AddressNotFoundException
     * @throws CustomerNotFoundException
     * @throws PropelException
     *
     * @return AddressTransfer
     */
    public function deleteAddress(AddressTransfer $addressTransfer)
    {
        $customer = $this->getCustomerFromAddressTransfer($addressTransfer);

        $entity = $this->queryContainer
            ->queryAddressForCustomer(
                $addressTransfer->getIdCustomerAddress(),
                $customer->getEmail()
            )
            ->findOne();

        if (!$entity) {
            throw new AddressNotFoundException();
        }

        $wasDefault = false;
        if ($customer->getDefaultShippingAddress() === $entity->getIdCustomerAddress()) {
            $customer->setDefaultShippingAddress(null);
            $wasDefault = true;
        }
        if ($customer->getDefaultBillingAddress() === $entity->getIdCustomerAddress()) {
            $customer->setDefaultBillingAddress(null);
            $wasDefault = true;
        }
        if ($wasDefault) {
            $customer->save();
        }

        $oldAddressTransfer = $this->entityToAddressTransfer($entity);
        $oldAddressTransfer->setIdCustomerAddress(null);

        $entity->delete();

        return $oldAddressTransfer;
    }

    /**
     * @param AddressTransfer $addressTransfer
     *
     * @throws CountryNotFoundException
     *
     * @return int
     */
    protected function retrieveFkCountry(AddressTransfer $addressTransfer)
    {
        $fkCountry = $addressTransfer->getFkCountry();
        if (empty($fkCountry)) {
            $iso2Code = $addressTransfer->getIso2Code();
            if (empty($iso2Code) === false) {
                $fkCountry = $this->countryFacade->getIdCountryByIso2Code($iso2Code);
            } else {
                $fkCountry = $this->getCustomerCountryId();
            }
        }

        return $fkCountry;
    }

    /**
     * @param AddressTransfer $addressTransfer
     *
     * @throws \Exception
     *
     * @return CustomerTransfer
     */
    public function updateAddressAndCustomerDefaultAddresses(AddressTransfer $addressTransfer)
    {
        $connection = $this->queryContainer->getConnection();
        $connection->beginTransaction();

        try {
            $customerEntity = $this->getCustomerFromAddressTransfer($addressTransfer);

            $addressEntity = $this->updateCustomerAddress($addressTransfer, $customerEntity);

            $this->updateCustomerDefaultAddresses($addressTransfer, $customerEntity, $addressEntity);

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $customerTransfer = $this->entityToCustomerTransfer($customerEntity);
        $customerTransfer->setAddresses($this->getAddresses($customerTransfer));

        return $customerTransfer;
    }

    /**
     * @param AddressTransfer $addressTransfer
     *
     * @throws \Exception
     *
     * @return CustomerTransfer
     */
    public function createAddressAndUpdateCustomerDefaultAddresses(AddressTransfer $addressTransfer)
    {
        $connection = $this->queryContainer->getConnection();
        $connection->beginTransaction();

        try {
            $customerEntity = $this->getCustomerFromAddressTransfer($addressTransfer);

            $addressEntity = $this->createCustomerAddress($addressTransfer, $customerEntity);

            $this->updateCustomerDefaultAddresses($addressTransfer, $customerEntity, $addressEntity);

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $customerTransfer = $this->entityToCustomerTransfer($customerEntity);
        $customerTransfer->setAddresses($this->getAddresses($customerTransfer));

        return $customerTransfer;
    }

    /**
     * @param SpyCustomer $entity
     *
     * @return CustomerTransfer
     */
    protected function entityToCustomerTransfer(SpyCustomer $entity)
    {
        $addressTransfer = new CustomerTransfer();

        return $addressTransfer->fromArray($entity->toArray(), true);
    }

    /**
     * @param AddressTransfer $addressTransfer
     * @param SpyCustomer $customer
     *
     * @return SpyCustomerAddress
     */
    protected function createCustomerAddress(AddressTransfer $addressTransfer, SpyCustomer $customer)
    {
        $addressEntity = new SpyCustomerAddress();
        $addressEntity->fromArray($addressTransfer->toArray());

        $fkCountry = $this->retrieveFkCountry($addressTransfer);
        $addressEntity->setFkCountry($fkCountry);

        $addressEntity->setCustomer($customer);
        $addressEntity->save();

        return $addressEntity;
    }

    /**
     * @param AddressTransfer $addressTransfer
     * @param SpyCustomer $customer
     *
     * @throws AddressNotFoundException
     *
     * @return SpyCustomerAddress
     */
    protected function updateCustomerAddress(AddressTransfer $addressTransfer, SpyCustomer $customer)
    {
        $addressEntity = $this->queryContainer->queryAddressForCustomer($addressTransfer->getIdCustomerAddress(), $customer->getEmail())
            ->findOne();

        if (!$addressEntity) {
            throw new AddressNotFoundException();
        }

        $fkCountry = $this->retrieveFkCountry($addressTransfer);

        $addressEntity->fromArray($addressTransfer->toArray());
        $addressEntity->setCustomer($customer);
        $addressEntity->setFkCountry($fkCountry);
        $addressEntity->save();

        return $addressEntity;
    }

    /**
     * @param AddressTransfer $addressTransfer
     * @param SpyCustomer $customerEntity
     * @param SpyCustomerAddress $entity
     *
     * @return void
     */
    protected function updateCustomerDefaultAddresses(AddressTransfer $addressTransfer, SpyCustomer $customerEntity, SpyCustomerAddress $entity)
    {
        if ($customerEntity->getDefaultBillingAddress() === null || $addressTransfer->getIsDefaultBilling()) {
            $customerEntity->setDefaultBillingAddress($entity->getIdCustomerAddress());
        }

        if ($customerEntity->getDefaultShippingAddress() === null || $addressTransfer->getIsDefaultShipping()) {
            $customerEntity->setDefaultShippingAddress($entity->getIdCustomerAddress());
        }

        $customerEntity->save();
    }

}