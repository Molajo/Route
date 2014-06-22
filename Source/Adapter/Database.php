<?php
/**
 * Database Handler for Route
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route\Adapter;

use Exception;
use CommonApi\Exception\RuntimeException;
use CommonApi\Route\RouteInterface;
use CommonApi\Controller\ReadControllerInterface;

/**
 * Database Handler for Route
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class Database extends AbstractRequest implements RouteInterface
{
    /**
     * Resource Query
     *
     * @var    object  Molajo\Controller\ReadController
     * @since  1.0
     */
    protected $resource_query = null;

    /**
     * Constructor
     *
     * @param   object                  $request
     * @param   int                     $url_force_ssl
     * @param   int                     $application_home_catalog_id
     * @param   string                  $application_path
     * @param   int                     $application_id
     * @param   string                  $base_url
     * @param   array                   $filters
     * @param   array                   $task_to_action
     * @param   array                   $page_types
     * @param   ReadControllerInterface $resource_query
     *
     * @since   1.0
     */
    public function __construct(
        $request,
        $url_force_ssl,
        $application_home_catalog_id,
        $application_path,
        $application_id,
        $base_url,
        array $filters = array(),
        array $task_to_action = array(),
        array $page_types = array(),
        ReadControllerInterface $resource_query
    ) {
        parent::__construct(
            $request,
            $url_force_ssl,
            $application_home_catalog_id,
            $application_path,
            $application_id,
            $base_url,
            $task_to_action,
            $filters,
            $page_types,
            $resource_query
        );

        $this->resource_query = $resource_query;
    }

    /**
     * For Route, retrieve Catalog Item, either for the SEF URL or the Catalog ID
     *
     * @return  object
     * @since   1.0
     */
    public function setRoute()
    {
        $this->setRouteQuery();

        try {
            $item = $this->resource_query->getData();

        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }

        $this->route->model_registry = $this->resource_query->getModelRegistry('*');

        if (count($item) === 0 || $item === false) {
            $this->route->route_found = 0;

            return $this->route;
        }

        if ((int)$item->redirect_to_id > 0) {
            return $this->setRouteRedirect($item);
        }

        $this->setRouteData($item);

        return $this->route;
    }

    /**
     * Set Route Query
     *
     * @return  $this
     * @since   1.0
     */
    public function setRouteQuery()
    {
        $this->resource_query->setModelRegistry('use_special_joins', 1);
        $this->resource_query->setModelRegistry('process_events', 0);
        $this->resource_query->setModelRegistry('query_object', 'item');

        $this->resource_query->where(
            'column',
            $this->resource_query->getModelRegistry('primary_prefix', 'a') . '.' . 'sef_request',
            '=',
            'string',
            $this->route->path
        );

        $this->resource_query->where(
            'column',
            $this->resource_query->getModelRegistry('primary_prefix', 'a') . '.' . 'page_type',
            '<>',
            'string',
            'link'
        );

        $this->resource_query->where(
            'column',
            $this->resource_query->getModelRegistry('primary_prefix', 'a') . '.' . 'enabled',
            '=',
            'integer',
            1
        );

        $this->resource_query->where(
            'column',
            $this->resource_query->getModelRegistry('primary_prefix', 'a') . '.' . 'application_id',
            '=',
            'integer',
            2
//todo: fix application issue
        );

        return $this;
    }

    /**
     * Set Route Redirect
     *
     * @param   object $item
     *
     * @return  $this
     * @since   1.0
     */
    public function setRouteRedirect($item)
    {
        $this->route->redirect_to_id = (int)$item->redirect_to_id;

        return $this;
    }

    /**
     * Set Route Data
     *
     * @param   object $item
     *
     * @return  $this
     * @since   1.0
     */
    public function setRouteData($item)
    {
        $this->route->route_found = 1;
        $this->route->home        = 0;

        foreach (\get_object_vars($item) as $key => $value) {

            $this->route->$key = $value;

            if ($key === 'b_model_name') {
                $this->route->model_name          = ucfirst(strtolower($item->b_model_name));
                $this->route->model_type          = ucfirst(strtolower($item->b_model_type));
                $this->route->model_registry_name = $this->route->model_name . $this->route->model_type;
            }
        }

        $this->route->catalog_id = $this->route->id;

        return $this;
    }
}
