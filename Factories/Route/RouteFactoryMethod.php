<?php
/**
 * Route Factory Method
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Factories\Route;

use Exception;
use CommonApi\Exception\RuntimeException;
use CommonApi\IoC\FactoryInterface;
use CommonApi\IoC\FactoryBatchInterface;
use Molajo\IoC\FactoryMethod\Base as FactoryMethodBase;

/**
 * Route Factory Method
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class RouteFactoryMethod extends FactoryMethodBase implements FactoryInterface, FactoryBatchInterface
{
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
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function setDependencies(array $reflection = array())
    {
        $options = array();

        $this->dependencies                  = array();
        $this->dependencies['Resource']      = $options;
        $this->dependencies['Request']       = $options;
        $this->dependencies['Runtimedata']   = $options;

        return $this->dependencies;
    }

    /**
     * Set Dependency values
     *
     * @param   array $dependency_values (ignored in Service Item Adapter, based in from handler)
     *
     * @return  $this
     * @since   1.0
     */
    public function onBeforeInstantiation(array $dependency_values = null)
    {
        parent::onBeforeInstantiation($dependency_values);

        $this->dependencies['Filters'] = $this->getApplicationFilters();

        return $this;
    }

    /**
     * Instantiate Class
     *
     * @return  $this
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function instantiateClass()
    {
        $adapter = $this->getAdapter();

        $class = 'Molajo\\Route\\Driver';

        try {
            $this->product_result = new $class($adapter);
        } catch (Exception $e) {

            throw new RuntimeException
            ('Route: Could not instantiate Adapter');
        }
        return $this;
    }

    /**
     * Logic contained within this method is invoked after the class construction
     *  and can be used for setter logic or other post-construction processing
     *
     * @return  $this
     * @since   1.0
     */
    public function onAfterInstantiation()
    {
        $results = $this->product_result->verifySecureProtocol();
        if (isset($results->error_code) && (int)$results->error_code > 0) {
            $this->product_result = $results;
            return $this;
        }

        $results = $this->product_result->verifyHome();
        if (isset($results->error_code) && (int)$results->error_code > 0) {
            $this->product_result = $results;
            return $this;
        }

        $results = $this->product_result->setRequest();
        if (isset($results->error_code) && (int)$results->error_code > 0) {
            $this->product_result = $results;
            return $this;
        }

        $results = $this->product_result->setRoute();
        if (isset($results->error_code) && (int)$results->error_code > 0) {
            $this->product_result = $results;
            return $this;
        }

        $this->dependencies['Runtimedata']->page_type = $results->page_type;
        $this->dependencies['Runtimedata']->route     = $this->sortObject($results);

        $this->product_result = $results;

        return $this;
    }

    /**
     * Factory Method Controller requests any Products (other than the current product) to be saved
     *
     * @return  array
     * @since   1.0
     */
    public function setContainerEntries()
    {
        $this->set_container_entries['Runtimedata'] = $this->dependencies['Runtimedata'];

        return $this->set_container_entries;
    }

    /**
     * Get the Route Adapter Handler
     *
     * @param   string $adapter_handler
     *
     * @return  object
     * @since   1.0
     * @throws  FactoryInterface
     */
    protected function getAdapter()
    {
        $url_force_ssl
            = $this->dependencies['Runtimedata']->application->parameters->url_force_ssl;
        $application_home_catalog_id
            = $this->dependencies['Runtimedata']->application->parameters->application_home_catalog_id;
        $application_path
            = $this->dependencies['Runtimedata']->application->path;
        $application_id
            = $this->dependencies['Runtimedata']->application->id;
        $base_url
            = $this->dependencies['Runtimedata']->application->base_url;
        $task_to_action
            = $this->dependencies['Runtimedata']->reference_data->task_to_action;

        $query = $this->dependencies['Resource']->get(
            'query:///Molajo//Model//Datasource//Catalog.xml',
            array('runtime_data' => $this->dependencies['Runtimedata'])
        );

        $class = 'Molajo\\Route\\Adapter\\Database';

        try {
            return new $class(
                $this->dependencies['Request'],
                $url_force_ssl,
                $application_home_catalog_id,
                $application_path,
                $application_id,
                $base_url,
                $task_to_action,
                $this->dependencies['Filters'],
                $query
            );

        } catch (Exception $e) {
            throw new RuntimeException
            ('Route: Could not instantiate Handler: ' . $class);
        }
    }

    /**
     * Set Application Filters (For URLs)
     *
     * @return  array
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function getApplicationFilters()
    {
        $f = $this->dependencies['Resource']->get('xml:///Molajo//Model//Application//Filters.xml');

        $filters = array();
        foreach ($f->filter as $t) {
            $filters[] = (string)$t['name'];
        }

        return $filters;
    }
}
