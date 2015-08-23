<?php
/**
 * Route Factory Method
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 */
namespace Molajo\Factories\Route;

use Exception;
use CommonApi\Exception\RuntimeException;
use CommonApi\IoC\FactoryInterface;
use CommonApi\IoC\FactoryBatchInterface;
use Molajo\IoC\FactoryMethod\Base as FactoryMethodBase;
use stdClass;

/**
 * Route Factory Method
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class RouteFactoryMethod extends FactoryMethodBase implements FactoryInterface, FactoryBatchInterface
{
    /**
     * Rebuild Routes
     *
     * @var    boolean
     * @since  1.0
     */
    protected $rebuild_routes = true;

    /**
     * Constructor
     *
     * @param  $options
     *
     * @since  1.0
     */
    public function __construct(array $options = array())
    {
        $options['product_name']      = basename(__DIR__);
        $options['product_namespace'] = null;

        parent::__construct($options);
    }

    /**
     * Define dependencies or use dependencies automatically defined by base class using Reflection
     *
     * @return  array
     * @since   1.0.0
     */
    public function setDependencies(array $reflection = array())
    {
        $options = array();

        $this->dependencies                 = array();
        $this->dependencies['Request']      = $options;
        $this->dependencies['Resource']     = $options;
        $this->dependencies['Fieldhandler'] = $options;
        $this->dependencies['Runtimedata']  = $options;

        $this->options['route_json']        = $this->base_path . '/Bootstrap/Files/Output/Route.json';

        if (file_exists($this->options['route_json'])) {
            $this->rebuild_routes = false;
        } else {
            $this->rebuild_routes = true;
        }

        return $this->dependencies;
    }

    /**
     * Set Dependency values
     *
     * @param   array $dependency_values (ignored in Service Item Adapter, based in from handler)
     *
     * @return  $this
     * @since   1.0.0
     */
    public function onBeforeInstantiation(array $dependency_values = null)
    {
        parent::onBeforeInstantiation($dependency_values);

        $this->dependencies['Filters'] = $this->getApplicationFilters();
        $this->dependencies['Actions'] = $this->getApplicationActions();

        $this->dependencies['Runtimedata']->reserved          = new stdClass();
        $this->dependencies['Runtimedata']->reserved->filters = $this->dependencies['Filters'];
        $this->dependencies['Runtimedata']->reserved->actions = $this->dependencies['Actions'];

        return $this;
    }

    /**
     * Instantiate Class
     *
     * @return  $this
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function instantiateClass()
    {
        if ($this->rebuild_routes === true) {
            $this->setRouteData();
        }

        $adapter = $this->getAdapter();
        $class   = 'Molajo\\Route\\Controller';

        try {
            $this->product_result = new $class($adapter);

        } catch (Exception $e) {

            throw new RuntimeException('Route: Could not instantiate Adapter');
        }

        return $this;
    }

    /**
     * Logic contained within this method is invoked after the class construction
     *  and can be used for setter logic or other post-construction processing
     *
     * @return  $this
     * @since   1.0.0
     */
    public function onAfterInstantiation()
    {
        $results = $this->product_result->verifySecureProtocol();
        if ((int)$results->error_code > 0) {
            $this->product_result = $results;
            return $this;
        }

        $results = $this->product_result->verifyHome();
        if ((int)$results->error_code > 0) {
            $this->product_result = $results;
            return $this;
        }

        $results = $this->product_result->setRequest();
        if ((int)$results->error_code > 0) {
            $this->product_result = $results;
            return $this;
        }

        $results = $this->product_result->setRoute();
        if ((int)$results->error_code > 0) {
            $this->product_result = $results;
            return $this;
        }

        $this->dependencies['Runtimedata']->page_type = $results->page_type;
        $this->dependencies['Runtimedata']->route     = $this->sortObject($results);

        return $this;
    }

    /**
     * Factory Method Controller requests any Products (other than the current product) to be saved
     *
     * @return  array
     * @since   1.0.0
     */
    public function setContainerEntries()
    {
        $this->set_container_entries['Runtimedata'] = $this->dependencies['Runtimedata'];

        return $this->set_container_entries;
    }

    /**
     * Refresh the Route Data
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function setRouteData()
    {
        $class = 'Molajo\\Route\\Type\\Data';

        try {
            $instance = new $class(
                $this->options['route_json'],
                $this->dependencies['Resource'],
                $this->dependencies['Fieldhandler'],
                $this->dependencies['Runtimedata']
            );

        } catch (Exception $e) {
            throw new RuntimeException('Route: Could not instantiate Handler: ' . $class);
        }

        $instance->setData();

        return $this;
    }

    /**
     * Get the Route Adapter Handler
     *
     * @param   string $adapter_handler
     *
     * @return  object
     * @since   1.0.0
     * @throws  FactoryInterface
     */
    protected function getAdapter()
    {
        if ($this->dependencies['Runtimedata']->application->parameters->url_force_ssl === 1) {
            $this->dependencies['Request']->url_force_ssl = 1;
        } else {
            $this->dependencies['Request']->url_force_ssl = 0;
        }

        $this->dependencies['Request']->application_path
            = $this->dependencies['Runtimedata']->application->path;
        $this->dependencies['Request']->base_url
            = $this->dependencies['Runtimedata']->application->base_url;

        $task_to_action = array();
        $page_types     = array();
        $route_data     = $this->readFile($this->options['route_json']);
        $class          = 'Molajo\\Route\\Controller\\Assign';

        try {
            return new $class(
                $this->dependencies['Request'],
                $this->dependencies['Filters'],
                $this->dependencies['Actions'],
                $task_to_action,
                $page_types,
                $route_data
            );

        } catch (Exception $e) {
            throw new RuntimeException (
                'Route: Could not instantiate Handler: ' . $class
            );
        }
    }

    /**
     * Set Application Filters (For URLs)
     *
     * @return  array
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function getApplicationFilters()
    {
        $f = $this->dependencies['Resource']->get('xml://Molajo//Model//Application//Filters.xml');

        $filters = array();
        foreach ($f->filter as $t) {
            $filters[] = (string)$t['name'];
        }

        return $filters;
    }

    /**
     * Set Application Actions
     *
     * @return  array
     * @since   1.0.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function getApplicationActions()
    {
        $a = $this->dependencies['Resource']->get('xml://Molajo//Model//Application//Actions.xml');

        $create = array();
        $read   = array();
        $update = array();
        $delete = array();

        foreach ($a->action as $a) {

            $temp                = new stdClass();
            $temp->name          = (string)$a['name'];
            $temp->method        = (string)$a['method'];
            $temp->authorisation = (string)$a['authorisation'];
            $temp->controller    = (string)$a['controller'];

            if ($temp->controller === 'create') {
                $create[$temp->name] = $temp;
            } elseif ($temp->controller === 'read') {
                $read[$temp->name] = $temp;
            } elseif ($temp->controller === 'update') {
                $update[$temp->name] = $temp;
            } elseif ($temp->controller === 'delete') {
                $delete[$temp->name] = $temp;
            }
        }

        $actions['create'] = $create;
        $actions['read']   = $read;
        $actions['update'] = $update;
        $actions['delete'] = $delete;

        return $actions;
    }
}
