<?php
/**
 * Builds Route Static Data
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route\Type;

use CommonApi\Fieldhandler\FieldhandlerInterface;
use CommonApi\Query\QueryInterface;
use stdClass;

/**
 * Builds Route Static Data
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014-2015 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
final class Data
{
    /**
     * Route Filename
     *
     * @var    string
     * @since  1.0
     */
    protected $route_filename = null;

    /**
     * Routes Array
     *
     * @var    array
     * @since  1.0
     */
    protected $routes = array();

    /**
     * Query Usage Trait
     *
     * @var     object  CommonApi\Query\QueryUsageTrait
     * @since   1.0.0
     */
    use \CommonApi\Query\QueryUsageTrait;

    /**
     * Constructor
     *
     * @param  string                $route_filename
     * @param  QueryInterface        $resource
     * @param  FieldhandlerInterface $fieldhandler
     * @param  array                 $runtime_data
     *
     * @since  1.0
     */
    public function __construct(
        $route_filename,
        QueryInterface $resource,
        FieldhandlerInterface $fieldhandler,
        array $runtime_data
    ) {
        $this->route_filename = $route_filename;
        $this->resource       = $resource;
        $this->fieldhandler   = $fieldhandler;
        $this->runtime_data   = $runtime_data;
        $this->routes         = array();
    }

    /**
     * Collect Route Data and store in file
     *
     * @return  $this
     * @since   1.0.0
     */
    public function setData()
    {
        $this->setHome();
        $this->setPageTypes();
        $this->setResources();

        file_put_contents($this->route_filename, json_encode($this->routes, JSON_PRETTY_PRINT));

        return $this;
    }

    /**
     * Home Route
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function setHome()
    {
        $this->setBaseQuery();

        $this->setQueryControllerDefaults(
            $process_events = 0,
            $query_object = 'item',
            $get_customfields = 0,
            $use_special_joins = 1,
            $use_pagination = 0,
            $check_view_level_access = 0,
            $get_item_children = 0
        );

        $prefix = $this->query->getModelRegistry('primary_prefix', 'a');
        $id     = $prefix . '.' . 'application_id';
        $this->query->where('column', $id, '=', 'integer', (int)$this->runtime_data->application->id);
        $this->query->where('column', 'a.sef_request', '=', 'string', '');

        $item = $this->runQuery();

        $this->routes['home'] = $this->setRouteData($item, 1);

        return $this;
    }

    /**
     * Routes by Page Type
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function setPageTypes()
    {
        $this->setBaseQuery();
        $this->setQueryControllerDefaults(0, 'distinct', 0, 1);

        $prefix = $this->query->getModelRegistry('primary_prefix', 'a');

        $this->query->select($prefix . '.sef_request', 'sef_request', null, 'string');
        $this->query->select($prefix . '.page_type', 'page_type', null, 'string');
        $this->query->select('b.model_type', 'model_type', null, 'string');
        $this->query->select('b.model_name', 'model_name', null, 'string');

        $this->query->where('column', $prefix . '.sef_request', '<>', 'string', '');
        $this->query->where('column', $prefix . '.page_type', '<>', 'string', 'item');
        $this->query->where('column', $prefix . '.page_type', '<>', 'string', 'new');
        $this->query->where('column', $prefix . '.page_type', '<>', 'string', 'list');
        $this->query->where('column', 'b.model_name', '=', 'string', 'Menuitems');

        $this->query->orderBy($prefix . '.sef_request', 'ASC');
        $this->query->orderBy($prefix . '.page_type', 'ASC');

        $list = $this->runQuery();

        $page_types = array();
        foreach ($list as $item) {
            $item->home                     = 0;
            $page_types[$item->sef_request] = $this->sortObject($item);
        }

        ksort($page_types);
        $this->routes['page_types'] = $page_types;

        return $this;
    }

    /**
     * Route by Resources
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function setResources()
    {
        $this->setBaseQuery();

        $this->setQueryControllerDefaults(0, 'distinct', 0, 1);

        $prefix = $this->query->getModelRegistry('primary_prefix', 'a');

        $this->query->select('b.model_name', 'model_name', null, 'string');
        $this->query->select('b.model_type', 'model_type', null, 'string');
        $this->query->select('b.alias', 'sef_request', null, 'string');

        $this->query->from('#__catalog_types', 'b');

        $this->query->where('column', $prefix . '.sef_request', '<>', 'string', '');
        $this->query->where('column', $prefix . '.page_type', '=', 'string', 'item');

        $this->query->orderBy('b.alias', 'ASC');

        $list = $this->runQuery();

        $resources = array();
        foreach ($list as $item) {
            $item->home                    = 0;
            $resources[$item->sef_request] = $this->sortObject($item);
        }

        ksort($resources);
        $this->routes['resources'] = $resources;

        return $this;
    }

    /**
     * Set Route Query
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function setBaseQuery()
    {
        $this->setQueryController('Molajo//Model//Datasource//Catalog.xml');

        $prefix = $this->query->getModelRegistry('primary_prefix', 'a');

        $this->query->where('column', $prefix . '.' . 'enabled', '=', 'integer', 1);
        $this->query->where('column', $prefix . '.' . 'page_type', '<>', 'string', 'link');
        $this->query->where('column', $prefix . '.' . 'redirect_to_id', '=', 'integer', 0);

        return $this;
    }

    /**
     * Set Route Data
     *
     * @param   object  $item
     * @param   integer $home
     *
     * @return  $this
     * @since   1.0.0
     */
    protected function setRouteData($item, $home)
    {
        $route       = new stdClass();
        $route->home = $home;

        foreach (\get_object_vars($item) as $key => $value) {

            if (substr($key, 0, 2) === 'b_') {
                if (in_array($key, array('b_model_type', 'b_model_name'))) {
                    $new_key = substr($key, 2, 9999);
                } else {
                    $new_key = false;
                }
            } else {
                if (in_array($key, array('sef_request', 'page_type'))) {
                    $new_key = $key;
                } else {
                    $new_key = false;
                }
            }

            if ($new_key === false) {
            } else {
                $route->$new_key = $value;
            }
        }

        return $this->sortObject($route);
    }
}
