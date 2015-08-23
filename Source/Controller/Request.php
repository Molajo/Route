<?php
/**
 * Route Builder Request
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route\Controller;

use CommonApi\Route\RouteInterface;

/**
 * Route Builder Request
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
abstract class Request extends Secure implements RouteInterface
{
    /**
     * Segments
     *
     * @var    array
     * @since  1.0
     */
    protected $segments;

    /**
     * Set Request
     *
     * @return  object
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0.0
     */
    public function setRequest()
    {
        $this->setAction();

        $this->setBaseUrl();

        $this->setSegments();

        if (count($this->segments) === 0) {
        } else {
            $this->setRequestAction();
        }

        if ($this->route->path === null) {
            $this->setRequestVariables();
        }

        $this->setPageType();

        return $this->route;
    }

    /**
     * Set Action from HTTP Method
     *
     * @return  $this
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0.0
     */
    protected function setAction()
    {
        $method              = $this->request->method;
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

        $this->route->special_action = '';

        $this->valid_actions = $this->actions[$this->route->action];

        return $this;
    }

    /**
     * Set Base Url
     *
     * @return  $this
     * @throws  \CommonApi\Exception\RuntimeException
     * @since   1.0.0
     */
    protected function setBaseUrl()
    {
        $this->route->base_url = $this->base_url;

        return $this->route;
    }

    /**
     * Extract Segments from URL
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function setSegments()
    {
        $this->removePathSlash();

        $this->segments = explode('/', $this->application_path);

        return $this;
    }

    /**
     * Remove Path Slash
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function removePathSlash()
    {
        if (substr($this->application_path, 0, 1) === '/') {
            $this->application_path = substr($this->application_path, 1, strlen($this->application_path) - 1);
        }

        return $this;
    }

    /**
     * Traverse backwards through parameter pairs to find filters
     *
     * @param  array $search_for
     *
     * @return $this
     * @since  1.0
     */
    protected function setRequestAction()
    {
        $i = $this->setIndexAtMax();

        $special_action = $this->segments[$i];

        if (isset($this->valid_actions[$special_action])) {
        } else {
            return $this;
        }

        $this->route->special_action = $special_action;

        unset($this->segments[$i]);

        $this->setPath();

        return $this;
    }

    /**
     * Set Request Variables
     *
     * @return  $this
     * @since   1.0.0
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
     * @since   1.0.0
     */
    protected function setParameters($route_object_item, array $search_for = array())
    {
        $this->route->$route_object_item = $this->getParameterPairs($search_for);

        return $this;
    }

    /**
     * Traverse backwards through parameter pairs to find filters
     *
     * @param  array $search_for
     *
     * @return array
     * @since  1.0
     */
    protected function getParameterPairs($search_for = array())
    {
        $route_parameters = array();

        $i = $this->setIndexAtMax();

        while ($i > 0) {

            $results = $this->getParameterPair($i, $search_for, $route_parameters);
            if ($results === false) {
                break;
            }

            $route_parameters = $results;
            $i                = $i - 2;
        }

        $this->setPath();

        return $route_parameters;
    }

    /**
     * Traverse backwards through parameter pairs to find filters
     *
     * @param  integer $i
     * @param  array   $search_for
     * @param  array   $route_parameters
     *
     * @return array
     * @since  1.0
     */
    protected function getParameterPair(
        $i,
        array $search_for = array(),
        array $route_parameters = array()
    ) {
        $value  = $this->getNode($i, 0);
        $filter = $this->getNode($i, 1);

        if (in_array($filter, $search_for)) {
        } else {
            return false;
        }

        $route_parameters[$filter] = $value;

        $this->unsetNode($i, 0);
        $this->unsetNode($i, 1);

        return $route_parameters;
    }

    /**
     * Get Node
     *
     * @param   integer $i
     * @param   integer $decrement
     *
     * @return  integer
     * @since   1.0.0
     */
    protected function getNode($i, $decrement = 1)
    {
        $i = $this->decrementIndex($i, $decrement);

        if (isset($this->segments[$i]) === true) {
            return $this->segments[$i];
        }

        return false;
    }

    /**
     * Get Node
     *
     * @param   integer $i
     * @param   integer $decrement
     *
     * @return  integer
     * @since   1.0.0
     */
    protected function unsetNode($i, $decrement = 1)
    {
        $i = $this->decrementIndex($i, $decrement);

        if (isset($this->segments[$i]) === true) {
            unset($this->segments[$i]);
        }

        return false;
    }

    /**
     * Set Index at Max
     *
     * @return  integer
     * @since   1.0.0
     */
    protected function setIndexAtMax()
    {
        return count($this->segments) - 1;
    }

    /**
     * Decrement Index
     *
     * @param   integer $i
     * @param   integer $decrement
     *
     * @return  integer
     * @since   1.0.0
     */
    protected function decrementIndex($i, $decrement)
    {
        return $i - $decrement;
    }

    /**
     * Set Path
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function setPath()
    {
        $path = '';

        foreach ($this->segments as $segment) {
            if ($path === '') {
            } else {
                $path .= '/';
            }

            $path .= $segment;
        }

        $this->route->path = $path;

        return $this;
    }

    /**
     * Set Page Type
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function setPageType()
    {
        if (substr($this->route->path, strlen($this->route->path) - 4, 4) === '/new') {
            $this->route->page_type = $this->page_types['new'];

        } elseif ($this->route->special_action === 'edit') {
            $this->route->page_type = $this->page_types['edit'];

        } elseif ($this->route->special_action === 'delete') {
            $this->route->page_type = $this->page_types['delete'];

        } else {
            $this->route->page_type = $this->page_types['view'];
        }

        return $this;
    }
}
