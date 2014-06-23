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
        $this->setPageType();
        $this->setBaseUrl();
        $this->setRequestVariables();

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
        $this->route->method = strtoupper($method);

        if ($this->route->method === 'POST') {
            $this->route->action = 'create';

        } elseif ($this->route->method === 'PUT') {
            $this->route->action = 'update';

        } elseif ($this->route->method === 'DELETE') {
            $this->route->action = 'delete';

        } else {
            $this->route->method = 'GET';
            $this->route->action = 'read';
        }

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
            $this->setParameters('task', $this->task_to_action);
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
        $this->removePathSlash();

        $parameters = $this->getParameters();

        if (count($parameters) === 0) {
            return $this;
        }

        $this->route->$route_object_item = $this->getParameterPairs($parameters, $search_for);

        return $this;
    }

    /**
     * Remove Path Slash
     *
     * @return  $this
     * @since   1.0
     */
    protected function removePathSlash()
    {
        if (substr($this->application_path, 0, 1) === '/') {
            $this->application_path = substr($this->application_path, 1, strlen($this->application_path) - 1);
        }

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
        return explode('/', $this->application_path);
    }

    /**
     * Traverse backwards through parameter pairs to find filters
     *
     * @param  array $parameters
     * @param  array $search_for
     *
     * @return array
     * @since  1.0
     */
    protected function getParameterPairs(array $parameters = array(), $search_for = array())
    {
        $route_parameters = array();

        $i = $this->setIndexAtMax($parameters);

        while ($i > 0) {

            $results = $this->getParameterPair($i, $parameters, $search_for, $route_parameters);
            if ($results === false) {
                break;
            }

            $route_parameters = $results;
            $i = $i - 2;
        }

        $this->getPath($i, $parameters);

        return $route_parameters;
    }

    /**
     * Traverse backwards through parameter pairs to find filters
     *
     * @param  integer $i
     * @param  array   $parameters
     * @param  array   $search_for
     * @param  array   $route_parameters
     *
     * @return array
     * @since  1.0
     */
    protected function getParameterPair(
        $i,
        array $parameters = array(),
        array $search_for = array(),
        array $route_parameters = array()
    ) {
        $value  = $this->getNode($i, $parameters, 1);
        $filter = $this->getNode($i, $parameters, 2);

        $parsed = $this->parseParameterPair($filter, $search_for);
        if ($parsed === false) {
            return false;
        }

        $route_parameters[$filter] = $value;

        return $route_parameters;
    }

    /**
     * Set Path
     *
     * @param   integer $i
     * @param   array   $parameters
     *
     * @return  AbstractRequest
     * @since   1.0
     */
    protected function getPath($i, array $parameters = array())
    {
        $path = '';

        while ($i > 0) {

            if ($path === '') {
            } else {
                $path = '/' . $path;
            }

            $path = $this->getNode($i, $parameters, 1) . $path;
            $i = $i - 1;
        }

        $this->route->path = $path;

        return $this;
    }

    /**
     * Get Node
     *
     * @param   integer $i
     * @param   array   $parameters
     * @param   integer $decrement
     *
     * @return  integer
     * @since   1.0
     */
    protected function getNode($i, array $parameters = array(), $decrement = 1)
    {
        $i = $this->decrementIndex($i, $decrement);

        if (isset($parameters[$i]) === true) {
            return $parameters[$i];
        }

        return false;
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
        return count($parameters);
    }

    /**
     * Decrement Index
     *
     * @param   integer $i
     * @param   integer $decrement
     *
     * @return  integer
     * @since   1.0
     */
    protected function decrementIndex($i, $decrement)
    {
        return $i - $decrement;
    }

    /**
     * Parse Parameter Pair for specific values
     *
     * @param   string $filter
     * @param   array  $search_for
     *
     * @return  boolean
     * @since   1.0
     */
    protected function parseParameterPair($filter, array $search_for = array())
    {
        if (in_array($filter, $search_for)) {
            return true;
        }

        return false;
    }

    /**
     * Set Page Type
     *
     * @return  $this
     * @since   1.0
     */
    protected function setPageType()
    {
        if (in_array($this->page_types['new'], $this->filters)) {
            $this->route->page_type = $this->page_types['new'];

        } elseif (in_array($this->page_types['edit'], $this->filters)) {
            $this->route->page_type = $this->page_types['edit'];

        } elseif (in_array($this->page_types['delete'], $this->filters)) {
            $this->route->page_type = $this->page_types['delete'];
        } else {
            $this->route->page_type = $this->page_types['view'];
        }

        return $this;
    }
}
