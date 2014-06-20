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
abstract class AbstractParameters extends AbstractAdapter implements RouteInterface
{
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
         *       if ($sef === 1) {
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
}
