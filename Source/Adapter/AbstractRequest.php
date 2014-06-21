<?php
/**
 * Request
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route\Adapter;

use CommonApi\Route\RouteInterface;

/**
 * Request
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
abstract class AbstractRequest extends AbstractVerifyHome implements RouteInterface
{
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

    /**
     * Retrieve Parameters from URL
     *
     * @param   string $route_object_item
     * @param   array  $search_for
     *
     * @return  $this
     * @since   1.0
     */
    protected function setParameters($route_object_item, array $search_for = array())
    {
        $parameters = $this->getParameters();
        if (count($parameters) === 0) {
            return $this;
        }

        $this->route->$route_object_item = $this->getParameterPairs($parameters, $search_for);

        $this->setPageType();

        return $this;
    }

    /**
     * Extract Parameter Pairs from URL
     *
     * @return  array
     * @since   1.0
     */
    protected function getParameters()
    {
        $parameters = explode('/', $this->application_path);

        if (count($this->request->query) > 0) {
            foreach ($this->request->query as $parameter) {
                $parameters[] = $parameter;
            }
        }

        return $parameters;
    }

    /**
     * Traverse backwards through parameter pairs to find filters
     *
     * @param  array $parameters
     *
     * @return array
     * @since  1.0
     */
    protected function getParameterPairs(array $parameters = array(), $search_for = array())
    {
        $route_parameters = array();

        $i = $this->setIndexAtMax($parameters);

        while ($i > 0) {
            $parsed = $this->parseParameterPair($parameters[$i], $search_for);

            if ($parsed === array()) {
                $i = -1;
            } else {
                $route_parameters[] = $parsed;
            }

            $i = $this->decrementIndex($i);
        }

        return $route_parameters;
    }

    /**
     * Set Index at Max
     *
     * @param   array $parameters
     *
     * @return  integer
     * @since   1.0
     */
    protected function setIndexAtMax(array $parameters = array())
    {
        return count($parameters) - 1;
    }

    /**
     * Decrement Index
     *
     * @param   integer $i
     *
     * @return  integer
     * @since   1.0
     */
    protected function decrementIndex($i)
    {
        return $i - 1;
    }

    /**
     * Parse Parameter Pair for specific values
     *
     * @param   array $pair
     *
     * @return  array
     * @since   1.0
     */
    protected function parseParameterPair(array $pair = array(), array $search_for = array())
    {
        $parsed = explode('=', $pair);

        if (in_array($parsed[0], $search_for)) {
            return $pair;
        }

        return array();
    }

    /**
     * Set Page Type
     *
     * @return  $this
     * @since   1.0
     */
    protected function setPageType()
    {
        if (in_array($this->page_type['new'], $this->route->filters_array)) {
            $this->page_type = $this->page_type['new'];

        } elseif (in_array($this->page_type['edit'], $this->route->filters_array)) {
            $this->page_type = $this->page_type['edit'];

        } elseif (in_array($this->page_type['delete'], $this->route->filters_array)) {
            $this->page_type = $this->page_type['delete'];
        }

        return $this;
    }

    // // // //

    /**
     * For non-read actions, retrieve task and values
     *
     * @return  $this
     * @since   1.0
     */
    protected function setTaskParameters(array $parameters = array(), $search_for = array())
    {
        $route_parameters = array();

        $i = $this->setIndexAtMax($parameters);


        $path          = '';
        $task          = '';
        $action_target = '';

        foreach ($parameters as $slug) {
            if ($task === '') {
                if (in_array($slug, $search_for)) {
                    $task = $slug;
                } else {
                    if (trim($path) === '') {
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
     * @param   array $remove
     *
     * @return  string
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0
     */
    protected function setPath(array $remove = array())
    {
        $path = $this->removePathSlash($this->application_path);

        return $this->removeUrlNodes($path, $remove);
    }

    /**
     * Remove Path Slash
     *
     * @param   string $path
     *
     * @return  $path
     * @since   1.0
     */
    protected function removePathSlash($path)
    {
        if (substr($path, 0, 1) === '/') {
            $path = substr($path, 1, strlen($path) - 1);
        }

        return $path;
    }

    /**
     * Decrement Index
     *
     * @param   string $path
     * @param   array  $remove
     *
     * @return  integer
     * @since   1.0
     */
    protected function removeUrlNodes($path, array $remove = array())
    {
        foreach ($remove as $item) {
            $found = strrpos($path, '/' . $item);
            $path  = substr($path, 0, $found);
        }

        return $path;
    }
}
