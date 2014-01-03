<?php
/**
 * Abstract Route Handler
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route\Handler;

use CommonApi\Route\RouteInterface;
use CommonApi\Exception\RuntimeException;

/**
 * Abstract Route Handler
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0
 */
abstract class AbstractHandler implements RouteInterface
{
    /**
     * Request Object
     *
     * @var    object
     * @since  1.0
     */
    protected $request;

    /**
     * Parameters
     *
     * @var    object $parameters
     * @since  1.0
     */
    protected $parameters;

    /**
     * Filters
     *
     * @var    array
     * @since  1.0
     */
    protected $filters;

    /**
     * Constructor
     *
     * @param  object $request
     * @param  object $parameters
     * @param  array  $filters
     *
     * @since   1.0
     */
    public function __construct(
        $request,
        $parameters,
        array $filters = array()
    ) {
        $this->request      = $request;
        $this->runtime_data = $parameters;
        $this->filters      = $filters;
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
        if ((int)$this->runtime_data->application->parameters->url_force_ssl == 0) {
            return $this->runtime_data;
        }

        if ((int)$this->request->is_secure == 1) {
            return $this->runtime_data;
        }

        $this->runtime_data->error_code      = 301;
        $this->runtime_data->redirect_to_url = $this->runtime_data->application->parameters->application_home_catalog_id;

        return $this->runtime_data;
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
        $this->runtime_data->route->home = 0;

        if (strlen($this->runtime_data->application->path) == 0
            || trim($this->runtime_data->application->path) == ''
        ) {
            $this->runtime_data->route->catalog_id
                                             = $this->runtime_data->application->parameters->application_home_catalog_id;
            $this->runtime_data->route->home = 1;

            return $this->runtime_data;
        }

        if ($this->runtime_data->application->path == '/') {
            $this->runtime_data->error_code = 301;
            $this->runtime_data->redirect_to_id
                                            = $this->runtime_data->application->parameters->application_home_catalog_id;
            return $this->runtime_data;
        }

        if ($this->runtime_data->application->path == 'index.php'
            || $this->runtime_data->application->path == 'index.php/'
            || $this->runtime_data->application->path == 'index.php?'
            || $this->runtime_data->application->path == '/index.php/'
        ) {
            $this->runtime_data->error_code = 301;
            $this->runtime_data->redirect_to_id
                                            = $this->runtime_data->application->parameters->application_home_catalog_id;
            return $this->runtime_data;
        }

        return $this->runtime_data;
    }

    /**
     * Set Request
     *
     * @return  $this
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0
     */
    public function setRequest()
    {
        $this->setAction();
        $this->setBaseUrl();
        $this->setPath();
        $this->setRequestVariables();

        return $this->runtime_data;
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

        $this->runtime_data->route->action = $action;
        $this->runtime_data->route->method = $method;

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
        $this->runtime_data->route->base_url = $this->runtime_data->application->base_url;

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
        $this->runtime_data->route->path = $this->runtime_data->application->path;

        return $this;
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

        if ($this->runtime_data->route->action == 'read') {
            $this->setReadVariables();
        } else {
            $post_variables = $this->runtime_data->route->post_variable_array;
            $this->setTaskVariables();
        }

        $this->runtime_data->route->post_variable_array = $post_variables;

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
        $urlParts = explode(' / ', $this->runtime_data->route->path);

        if (count($urlParts) == 0) {
            return $this;
        }

        $path        = '';
        $filterArray = '';
        $filter      = '';
        $i           = 0;

        foreach ($urlParts as $slug) {

            if ($filter == '') {
                if (in_array($slug, $this->filters)) {
                    $filter = $slug;
                } else {
                    if (trim($path) == '') {
                    } else {
                        $path .= '/';
                    }
                    $path .= $slug;
                }
            } else {
                if ($filterArray == '') {
                } else {
                    $filterArray .= ';';
                }
                $filterArray .= $filter . ':' . $slug;
                $filter = '';
            }
        }

        $this->runtime_data->route->filters_array = $filterArray;

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
        $urlParts = explode('/', $this->runtime_data->route->path);
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
        $this->runtime_data->route->request_task        = $task;
        $this->runtime_data->route->request_task_values = $action_target;

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
        return $this->runtime_data();
    }

    /**
     *  Redirect page
     *
     * @return  void
     * @since   1.0
     */
    public function setRedirect()
    {
        $this->runtime_data->request_non_route_parameters = '';

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
