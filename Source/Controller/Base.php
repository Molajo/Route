<?php
/**
 * Route Builder Base
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route\Controller;

use CommonApi\Route\RouteInterface;
use stdClass;

/**
 * Route Builder Base
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
abstract class Base implements RouteInterface
{
    /**
     * Request Object
     *
     * @var    object
     * @since  1.0
     */
    protected $request;

    /**
     * Force SSL Indicator
     *
     * @var    integer
     * @since  1.0
     */
    protected $url_force_ssl;

    /**
     * Application Path
     *
     * @var    string
     * @since  1.0
     */
    protected $application_path;

    /**
     * Base URL
     *
     * @var    string
     * @since  1.0
     */
    protected $base_url;

    /**
     * Task to Action
     *
     * @var    array
     * @since  1.0
     */
    protected $task_to_action;

    /**
     * Filters
     *
     * @var    array
     * @since  1.0
     */
    protected $filters;

    /**
     * Actions
     *
     * @var    array
     * @since  1.0
     */
    protected $actions;

    /**
     * Valid Actions
     *
     * @var    array
     * @since  1.0
     */
    protected $valid_actions;

    /**
     * Route
     *
     * @var    object
     * @since  1.0
     */
    protected $route;

    /**
     * Page Types
     *
     * @var    array
     * @since  1.0
     */
    protected $page_types
        = array(
            'new'    => 'new',
            'edit'   => 'edit',
            'delete' => 'delete',
            'view'   => 'view'
        );

    /**
     * Home Route
     *
     * @var    object
     * @since  1.0
     */
    protected $home_route = null;

    /**
     * Page Type Routes
     *
     * @var    object
     * @since  1.0
     */
    protected $page_type_routes = null;

    /**
     * Resource Routes
     *
     * @var    object
     * @since  1.0
     */
    protected $resource_routes = null;

    /**
     * Constructor
     *
     * @param   object $request
     * @param   array  $filters
     * @param   array  $actions
     * @param   array  $task_to_action
     * @param   array  $page_types
     * @param   array  $routes
     *
     * @since   1.0.0
     */
    public function __construct(
        $request,
        array $filters = array(),
        array $actions = array(),
        array $task_to_action = array(),
        array $page_types = array(),
        array $routes = array()
    ) {
        $this->request        = $request;
        $this->filters        = $filters;
        $this->actions        = $actions;
        $this->task_to_action = $task_to_action;

        if ($page_types === array()) {
        } else {
            $this->page_types = $page_types;
        }

        $this->setClassProperties();
        $this->initialiseRoute();

        $this->home_route       = $routes['home'];
        $this->page_type_routes = $routes['page_types'];
        $this->resource_routes  = $routes['resources'];
    }

    /**
     * Set class properties for several values within $request object
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function setClassProperties()
    {
        $properties = array(
            'url_force_ssl',
            'application_path',
            'base_url'
        );

        foreach ($properties as $property_name) {
            $this->setClassProperty($property_name);
        }

        return $this;
    }

    /**
     * Initialise Single Property
     *
     * @param string $property_name
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function setClassProperty($property_name)
    {
        $this->$property_name = $this->request->$property_name;
        unset($this->request->$property_name);

        return $this;
    }

    /**
     * Initialise Route Object
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function initialiseRoute()
    {
        $this->route                      = new stdClass();
        $this->route->route_found         = null;
        $this->route->error_code          = 0;
        $this->route->redirect_to_url     = null;
        $this->route->home                = 0;
        $this->route->action              = '';
        $this->route->special_action      = '';
        $this->route->method              = '';
        $this->route->base_url            = '';
        $this->route->path                = null;
        $this->route->post_variable_array = array();
        $this->route->filters             = array();
        $this->route->model_name          = '';
        $this->route->model_type          = '';
        $this->route->model_registry_name = '';
        $this->route->page_type           = '';

        return $this;
    }

    /**
     * Set Route Values
     *
     * @param   object
     *
     * @return  object
     * @since   1.0.0
     */
    protected function setRouteValues($object)
    {
        $this->route->route_found = 1;
        $this->route->home        = $object->home;
        $this->route->page_type   = $object->page_type;
        $this->route->model_type  = $object->model_type;
        $this->route->model_name  = $object->model_name;

        return $this;
    }
}
