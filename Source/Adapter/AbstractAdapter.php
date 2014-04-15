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
     * @var    int
     * @since  1.0
     */
    protected $url_force_ssl;

    /**
     * Application Home Catalog ID
     *
     * @var    int
     * @since  1.0
     */
    protected $application_home_catalog_id;

    /**
     * Application Path
     *
     * @var    int
     * @since  1.0
     */
    protected $application_path;

    /**
     * Application ID
     *
     * @var    int
     * @since  1.0
     */
    protected $application_id;

    /**
     * Base URL
     *
     * @var    int
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
     * Constructor
     *
     * @param   object $request
     * @param   int    $url_force_ssl
     * @param   int    $application_home_catalog_id
     * @param   string $application_path
     * @param   int    $application_id
     * @param   string $base_url
     * @param   array  $task_to_action
     * @param   array  $filters
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
        array $filters = array()
    ) {
        $this->request                     = $request;
        $this->url_force_ssl               = $url_force_ssl;
        $this->application_home_catalog_id = $application_home_catalog_id;
        $this->application_path            = $application_path;
        $this->application_id              = $application_id;
        $this->base_url                    = $base_url;
        $this->task_to_action              = $task_to_action;
        $this->filters                     = $filters;

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
        if ((int)$this->url_force_ssl == 0) {
            return $this->route;
        }

        if ((int)$this->request->is_secure == 1) {
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
        if (strlen($this->application_path) == 0
            || trim($this->application_path) == ''
        ) {
            $this->route->catalog_id = $this->application_home_catalog_id;
            $this->route->home       = 1;

            return $this->route;
        }

        if ($this->application_path == '/') {
            $this->route->error_code     = 301;
            $this->route->redirect_to_id = $this->application_home_catalog_id;

            return $this->route;
        }

        if ($this->application_path == 'index.php'
            || $this->application_path == 'index.php/'
            || $this->application_path == 'index.php?'
            || $this->application_path == '/index.php/'
        ) {
            $this->route->error_code     = 301;
            $this->route->redirect_to_id = $this->application_home_catalog_id;

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
        $this->setPath();

        return $this->route;
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

        if ($method == 'POST') {
            $action = 'create';
        } elseif ($method == 'PUT') {
            $action = 'update';
        } elseif ($method == 'DELETE') {
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
     * Set Path
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
        $post_variables = array();

        if ($this->route->action == 'read') {
            $this->setReadVariables();
        } else {
            $this->setTaskVariables();
        }

        return $this;
    }

    /**
     * Retrieve non-route values for SEF URLs:
     *
     * @return  $this
     * @since   1.0
     */
    protected function setReadVariables()
    {
        $urlParts = explode('/', $this->application_path);

        if (count($this->request->query) > 0 && is_array($this->request->query)) {
            foreach ($this->request->query as $parameter) {
                $urlParts[] = $parameter;
            }
        }

        if (count($urlParts) == 0) {
            return $this;
        }

        $path        = '';
        $filterArray = '';
        $filter      = '';
        $i           = 0;

        $route_parameters = array();

        if (count($urlParts) > 0 && is_array($urlParts)) {

            $i    = count($urlParts) - 1;
            $done = false;

            while ($done === false) {

                $test = $urlParts[$i];

                $parsed = explode('=', $test);

                if (in_array($parsed[0], $this->filters)) {
                    $route_parameters[] = $test;
                } else {
                    $done = true;
                }

                $i = $i - 1;
                if ($i < 0) {
                    $done = true;
                }
            }
        }

        $this->route->filters_array = $route_parameters;

        if (in_array('new', $route_parameters)) {
            $this->page_type = 'new';

        } elseif (in_array('edit', $route_parameters)) {
            $this->page_type = 'edit';

        } elseif (in_array('delete', $route_parameters)) {
            $this->page_type = 'delete';
        }

        return $this;
    }

    /**
     * For non-read actions, retrieve task and values
     *
     * @return  $this
     * @since   1.0
     */
    protected function setTaskVariables()
    {
        if (count($this->request->parameters) > 0) {
            foreach ($this->request->parameters as $parameter) {
                $urlParts[] = $parameter;
            }
        }

        if (count($urlParts) == 0) {
            return $this;
        }

        $tasks = $this->runtime_data->permission_tasks;

        $path          = '';
        $task          = '';
        $action_target = '';

        foreach ($urlParts as $slug) {
            if ($task == '') {
                if (in_array($slug, $tasks)) {
                    $task = $slug;
                } else {
                    if (trim($path) == '') {
                    } else {
                        $path .= ' / ';
                    }
                    $path .= $slug;
                }
            } else {
                $action_target = $slug;
                break;
            }
        }

        /** Map Action Verb (Tag, Favorite, etc.) to Permission Action (Update, Delete, etc.) */
        $this->route->request_task        = $task;
        $this->route->request_task_values = $action_target;

        return $this;
    }

    /**
     * Set Path
     *
     * @return  $this
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0
     */
    protected function setPath()
    {
        $path = $this->application_path;

        if (substr($path, 0, 1) == '/') {
            $path = substr($path, 1, strlen($path) - 1);
        }

        $remove = $this->route->filters_array;

        if (is_array($remove) && count($remove) > 0) {
            foreach ($remove as $item) {
                $found = strrpos($path, '/' . $item);
                $path  = substr($path, 0, $found);
            }
        }

        $this->route->path = $path;

        return $this;
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
        return $this->route();
    }

    /**
     *  Redirect page
     *
     * @return  void
     * @since   1.0
     */
    public function setRedirect()
    {
        /**
         * @todo test with non-sef URLs
         * $sef = $this->runtime_data->configuration_sef_url', 1);
         *       if ($sef == 1) {
         *
         * $this->getResourceSEF();
         *
         * } else {
         *
         * $this->getResourceExtensionParameters();
         *
         * }
         */

        return;
    }
}
