<?php
namespace CFX\SDK\Exchange;

class Client extends \CFX\Persistence\Rest\AbstractDataContext implements ClientInterface {
    protected static $apiName = 'exchange';
    protected static $apiVersion = '0';

    protected function instantiateDatasource($name) {
        if ($name == 'assets') return new AssetsClient($this);
        if ($name == 'bankAccounts') return new \CFX\Persistence\Rest\GenericDatasource($this, "bankAccounts", "\\CFX\\Brokerage\\BankAccount");
        if ($name == 'orders') return new OrdersClient($this);
        if ($name == 'fundsTransfers') return new FundsTransfersClient($this);
        if ($name == "fundingSources") return new \CFX\Persistence\Rest\GenericDatasource($this, "fundingSources", "\\CFX\\Brokerage\\FundingSource");
        if ($name == "legalEntities") return new \CFX\Persistence\Rest\GenericDatasource($this, "legalEntities", "\\CFX\\Brokerage\\LegalEntity");
        if ($name == "walletAccounts") return new \CFX\Persistence\Rest\GenericDatasource($this, "walletAccounts", "\\CFX\\Brokerage\\WalletAccounts");

        throw new \CFX\Persistence\UnknownDatasourceException("Programmer: Don't know how to handle datasources of type `$name`. If you'd like to handle this, you should either add this datasource to the `instantiateClient` method in this class or create a derivative class and add it there.");
    }

    protected function composeUri($endpoint) {
        return $this->getBaseUri()."/v{$this->getApiVersion()}$endpoint";
    }

    protected function processResponse($r)
    {
        try {
            return parent::processResponse($r);

        // We need to make some exceptions for known responses, since the exchange server
        // doesn't actually work right
        } catch (\RuntimeException $e) {
            // Assets
            if (strpos(strtolower($e->getMessage()), "invalid asset symbol") !== false) {
                throw new \CFX\Persistence\ResourceNotFoundException(
                    "The resource you're looking for wasn't found in our system"
                );

            // Anything else, we just rethrow
            } else {
                throw $e;
            }
        }
    }
}

