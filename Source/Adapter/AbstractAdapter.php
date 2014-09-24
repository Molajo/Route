<?php
/**
 * Abstract Route Adapter
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route\Adapter;

use CommonApi\Route\RouteInterface;
use stdClass;

/**
 * Abstract Route Adapter
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
abstract class AbstractAdapter implements RouteInterface
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
     * Application Home Catalog ID
     *
     * @var    integer
     * @since  1.0
     */
    protected $application_home_catalog_id;

    /**
     * Application Path
     *
     * @var    string
     * @since  1.0
     */
    protected $application_path;

    /**
     * Application ID
     *
     * @var    integer
     * @since  1.0
     */
    protected $application_id;

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
     * Constructor
     *
     * @param   object $request
     * @param   array  $filters
     * @param   array  $task_to_action
     * @param   array  $page_types
     *
     * @since   1.0
     */
    public function __construct(
        $request,
        array $filters = array(),
        array $task_to_action = array(),
        array $page_types = array()
    ) {
        $this->request        = $request;
        $this->filters        = $filters;
        $this->task_to_action = $task_to_action;

        if ($page_types === array()) {
        } else {
            $this->page_types = $page_types;
        }

        $this->setClassProperties();
        $this->initialiseRoute();
    }

    /**
     * Set class properties for several values within $request object
     *
     * @return  $this
     * @since   1.0
     */
    protected function setClassProperties()
    {
        $properties = array(
            'url_force_ssl',
            'application_home_catalog_id',
            'application_path',
            'application_id',
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
     * @return  $this
     * @since   1.0
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
     * @since   1.0
     */
    protected function initialiseRoute()
    {
        $this->route                      = new stdClass();
        $this->route->route_found         = null;
        $this->route->error_code          = 0;
        $this->route->redirect_to_url     = null;
        $this->route->redirect_to_id      = null;
        $this->route->home                = 0;
        $this->route->catalog_id          = 0;
        $this->route->action              = '';
        $this->route->method              = '';
        $this->route->base_url            = '';
        $this->route->path                = '';
        $this->route->post_variable_array = array();
        $this->route->filters             = array();
        $this->route->model_name          = '';
        $this->route->model_type          = '';
        $this->route->model_registry_name = '';
        $this->route->page_type           = '';

        return $this;
    }

    /**
     * Determine if secure protocol required and in use
     *
     * @return  object
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0
     */
    public function verifySecureProtocol()
    {
        if ((int)$this->url_force_ssl === 0) {
            return $this->route;
        }

        if ((int)$this->request->secure === 1) {
            return $this->route;
        }

        $this->route->error_code      = 301;
        $this->route->redirect_to_id  = $this->application_home_catalog_id;

        return $this->route;
    }

    /**
     * Set Route
     *
     * @return  object
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0
     */
    public function setRoute()
    {
        return $this->route;
    }
}
