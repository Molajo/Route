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
     * @param   array                   $filters
     * @param   array                   $task_to_action
     * @param   array                   $page_types
     * @param   ReadControllerInterface $resource_query
     *
     * @since   1.0
     */
    public function __construct(
        $request,
        array $filters = array(),
        array $task_to_action = array(),
        array $page_types = array(),
        ReadControllerInterface $resource_query = null
    ) {
        parent::__construct(
            $request,
            $filters,
            $task_to_action,
            $page_types
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
        $this->buildRouteQuery();

        $item = $this->runRouteQuery();

        if (count($item) === 0 || $item === false) {
            return $this->setRouteNotFound();
        }

        if ((int)$this->route->redirect_to_id > 0) {
            return $this->setRouteRedirect($item);
        }

        $this->setRouteData($item);

        return $this->route;
    }

    /**
     * Execute Route Query
     *
     * @return  $this
     * @since   1.0
     */
    public function runRouteQuery()
    {
        try {
            $item = $this->resource_query->getData();

        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }

        return $item;
    }

    /**
     * Set Route Query
     *
     * @return  $this
     * @since   1.0
     */
    public function buildRouteQuery()
    {
        $this->resource_query->setModelRegistry('use_special_joins', 1);
        $this->resource_query->setModelRegistry('process_events', 0);
        $this->resource_query->setModelRegistry('query_object', 'item');

        $this->buildRouteQueryWhereClause('sef_request', '=', 'string', $this->route->path);
        $this->buildRouteQueryWhereClause('page_type', '<>', 'string', 'link');
        $this->buildRouteQueryWhereClause('enabled', '=', 'integer', 1);
        $this->buildRouteQueryWhereClause('application_id', '=', 'integer', $this->application_id);

        return $this;
    }

    /**
     * Set Route Query Where Clause
     *
     * @param string $column_name
     * @param string $comparison_operator
     * @param string $filter
     *
     * @return  $this
     * @since   1.0
     */
    public function buildRouteQueryWhereClause(
        $column_name,
        $comparison_operator,
        $filter,
        $compare_to
     ) {
        $this->resource_query->where(
            'column',
            $this->resource_query->getModelRegistry('primary_prefix', 'a') . '.' . $column_name,
            $comparison_operator,
            $filter,
            $compare_to
        );

        return $this;
    }

    /**
     * Set Route Not Found
     *
     * @return  object
     * @since   1.0
     */
    public function setRouteNotFound()
    {
        $this->route->route_found = 0;

        return $this->route;
    }

    /**
     * Set Route Redirect
     *
     * @param   Database $item
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
     * @param   Database $item
     *
     * @return  $this
     * @since   1.0
     */
    public function setRouteData($item)
    {
        $this->route->model_registry = $this->resource_query->getModelRegistry('*');

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
