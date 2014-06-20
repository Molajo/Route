<?php
/**
 * Abstract Route Adapter
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route\Adapter;

use CommonApi\Exception\RuntimeException;
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
     * Page Type
     *
     * @var    string
     * @since  1.0
     */
    protected $page_type = null;

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
            'delete' => 'delete'
        );

    /**
     * Constructor
     *
     * @param   object  $request
     * @param   integer $url_force_ssl
     * @param   integer $application_home_catalog_id
     * @param   string  $application_path
     * @param   integer $application_id
     * @param   string  $base_url
     * @param   array   $task_to_action
     * @param   array   $filters
     * @param   array   $page_types
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
        array $task_to_action = array(),
        array $filters = array(),
        array $page_types = array()
    ) {
        $this->request                     = $request;
        $this->url_force_ssl               = $url_force_ssl;
        $this->application_home_catalog_id = $application_home_catalog_id;
        $this->application_path            = $application_path;
        $this->application_id              = $application_id;
        $this->base_url                    = $base_url;
        $this->task_to_action              = $task_to_action;
        $this->filters                     = $filters;

        if ($page_types === array()) {
        } else {
            $this->page_types = $page_types;
        }

        $this->initialiseRoute();
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
        $this->route->home                = 0;
        $this->route->catalog_id          = 0;
        $this->route->action              = '';
        $this->route->method              = '';
        $this->route->base_url            = '';
        $this->route->path                = '';
        $this->route->filters_array       = array();
        $this->route->post_variable_array = array();
        $this->route->request_task        = '';
        $this->route->request_task_values = array();
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

        if ((int)$this->request->is_secure === 1) {
            return $this->route;
        }

        $this->route->error_code      = 301;
        $this->route->redirect_to_url = $this->application_home_catalog_id;

        return $this->route;
    }

    /**
     * Determine if request is for home page
     *
     * @return  object
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0
     */
    public function verifyHome()
    {
        if ($this->verifyHomeEmptyPath() === true) {
            return $this->route;
        }

        if ($this->verifyHomeSlash() === true) {
            return $this->route;
        }

        if ($this->verifyHomeIndex() === true) {
            return $this->route;
        }

        return $this->route;
    }

    /**
     * Set Request
     *
     * @return  object
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0
     */
    public function setRequest()
    {
        $this->setAction();
        $this->setBaseUrl();
        $this->setRequestVariables();

        $this->route->path = $this->setPath($this->filters);

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

    /**
     * Home: application path
     *
     * @return  boolean
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0
     */
    protected function verifyHomeEmptyPath()
    {
        if (strlen($this->application_path) === 0
            || trim($this->application_path) === ''
        ) {
            $this->route->catalog_id = $this->application_home_catalog_id;
            $this->route->home       = 1;

            return true;
        }

        return false;
    }

    /**
     * Home: slash (redirect)
     *
     * @return  boolean
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0
     */
    protected function verifyHomeSlash()
    {
        if ($this->application_path === '/') {
            $this->route->error_code     = 301;
            $this->route->redirect_to_id = $this->application_home_catalog_id;

            return true;
        }

        return false;
    }

    /**
     * Home: index.php (redirect)
     *
     * @return  object
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0
     */
    public function verifyHomeIndex()
    {
        if ($this->application_path === 'index.php'
            || $this->application_path === 'index.php/'
            || $this->application_path === 'index.php?'
            || $this->application_path === '/index.php/'
        ) {
            $this->route->error_code     = 301;
            $this->route->redirect_to_id = $this->application_home_catalog_id;

            return true;
        }

        return false;
    }

    /**
     * Set Action from HTTP Method
     *
     * @return  $this
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0
     */
    protected function setAction()
    {
        $method = $this->request->method;
        $method = strtoupper($method);

        if ($method === 'POST') {
            $action = 'create';
        } elseif ($method === 'PUT') {
            $action = 'update';
        } elseif ($method === 'DELETE') {
            $action = 'delete';
        } else {
            $method = 'GET';
            $action = 'read';
        }

        $this->route->action = $action;
        $this->route->method = $method;

        return $this;
    }

    /**
     * Set Base Url
     *
     * @return  $this
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0
     */
    protected function setBaseUrl()
    {
        $this->route->base_url = $this->base_url;

        return $this->route;
    }

    /**
     * Set Request Variables
     *
     * @return  $this
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0
     */
    protected function setRequestVariables()
    {
        if ($this->route->action === 'read') {
            $this->setParameters('filters', $this->filters);
        } else {
            $this->setParameters('task', $this->runtime_data->permission_tasks);
        }

        return $this;
    }
}
