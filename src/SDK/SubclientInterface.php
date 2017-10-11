<?php
namespace CFX\SDK;

interface SubclientInterface {
    /**
     * new -- Get a new instance of the Resource class represented by this client
     *
     * @return \KS\JsonApi\BaseResourceInterface
     */
    public function create();

    /**
     * newCollection -- Get a new collection for handling many resources of this type
     *
     * @return \KS\JsonApi\ResourceCollectionInterface
     */
    public function newCollection();

    /**
     * save -- Send the given resource to the API for saving (either by POST or PATCH)
     *
     * @param \KS\JsonApi\BaseResource $r The resource to save
     * @return \KS\JsonApi\BaseResource
     */
    public function save(\KS\JsonApi\BaseResource $r);

    /**
     * get -- Get resources, optionally filtered by a query
     *
     * @param string $query An optional query with which to filter resources.
     * @return \KS\JsonApi\BaseResourceInterface|\KS\JsonApi\ResourceCollectionInterface The resource or resource collection returned
     * by the query. If the query includes an ID, then a single resource is returned (or exception thrown). If it doesn't include an
     * id, then an empty collection may be returned if there are no results.
     *
     * @throws \CFX\ResourceNotFoundException
     */
    public function get($q=null);

    /**
     * delete -- Delete a resource
     *
     * If the resources requested for deletion does not exist, no exception is thrown, since the end goal of the operation is that the
     * resource no longer be in the database.
     *
     * @param \KS\JsonApi\BaseResourceInterface|id The resource or resource id to delete
     * @return void
     */
    public function delete($r);
}


