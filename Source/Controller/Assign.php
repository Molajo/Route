<?php
/**
 * Assign Route
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route\Controller;

use CommonApi\Route\RouteInterface;

/**
 * Assign Route
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class Assign extends Request implements RouteInterface
{
    /**
     * For Route, retrieve Catalog Item, either for the SEF URL or the Catalog ID
     *
     * @return  object
     * @since   1.0.0
     */
    public function setRoute()
    {
        if ($this->route->home === 1) {
            return $this->setRouteHome();
        }

        if ($this->route->special_action === '') {
            $this->setRoutePageTypes($this->route->path);
        }

        if ($this->route->route_found === 1) {
            return $this->route;
        }

        $this->setRouteResource($this->route->path);

        if ($this->route->route_found === 1) {
            return $this->route;
        }

        return $this->route;
    }

    /**
     * Set Home Route Information
     *
     * @return  object
     * @since   1.0.0
     */
    protected function setRouteHome()
    {
        $object = clone $this->home_route;

        $this->setRouteValues($object);

        return $this->route;
    }

    /**
     * Check for Page Type Route
     *
     * @param   string
     *
     * @return  object
     * @since   1.0.0
     */
    protected function setRoutePageTypes($path)
    {
        if (isset($this->page_type_routes->$path)) {

            $object = clone $this->page_type_routes->$path;

            $this->setRouteValues($object);
        }

        return $this->route;
    }

    /**
     * Check for Page Type Route
     *
     * @param   string
     *
     * @return  object
     * @since   1.0.0
     */
    protected function setRouteResource($path)
    {
        /** List */
        if (isset($this->resource_routes->$path)) {

            $object = clone $this->resource_routes->$path;

            if ($this->route->special_action === 'new') {
                $object->page_type = 'New';
            } else {
                $object->page_type = 'List';
            }

            return $this->setRouteValues($object);
        }

        /** Remove Extra Slash */
        if (strrpos($path, '/') > 0) {
            $path = substr($path, 0, strrpos($path, '/'));
        }

        /** Item */
        if (isset($this->resource_routes->$path)) {

            $object = clone $this->resource_routes->$path;

            if ($this->route->special_action === 'edit') {
                $object->page_type = 'Edit';

            } elseif ($this->route->special_action === 'delete') {
                $object->page_type = 'Delete';

            } elseif ($this->route->special_action === 'update') {
                $object->page_type = 'Update';

            } else {
                $object->page_type = 'Item';
            }

            return $this->setRouteValues($object);
        }

        /** 404 */
        $object = clone $this->home_route;

        return $this->set404($object);
    }

    /**
     * Set Route Values
     *
     * @param   object
     *
     * @return  object
     * @since   1.0.0
     */
    protected function set404($object)
    {
        $this->route->route_found = 0;
        $this->route->error_code  = 404;
        $this->route->home        = $object->home;
        $this->route->page_type   = $object->page_type;
        $this->route->model_type  = $object->model_type;
        $this->route->model_name  = $object->model_name;

        return $this->route;
    }
}
