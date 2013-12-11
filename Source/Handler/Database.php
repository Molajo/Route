<?php
/**
 * Database Handler for Route
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route\Handler;

use Exception;
use Molajo\Controller\ReadController;
use CommonApi\Route\RouteInterface;
use CommonApi\Exception\RuntimeException;

/**
 * Database Handler for Route
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 * @since      1.0
 */
class Database extends AbstractHandler implements RouteInterface
{
    /**
     * Resource Query
     *
     * @var    object  Molajo\Controller\ReadController
     * @since  1.0
     */
    protected $resource_query = null;

    /**
     * Constructor
     *
     * @param  object         $request
     * @param  object         $parameters
     * @param  array          $filters
     * @param  ReadController $resource_query
     *
     * @since   1.0
     */
    public function __construct(
        $request,
        $parameters,
        array $filters = array(),
        ReadController $resource_query
    ) {
        parent::__construct(
            $request,
            $parameters,
            $filters
        );

        $this->resource_query = $resource_query;
    }

    /**
     * For Route, retrieve Catalog Item, either for the SEF URL or the Catalog ID
     *
     * 404 Error when no Catalog Item is found
     *
     * @return  object
     * @since   1.0
     * @throws  \CommonApi\Exception\RuntimeException
     */
    public function setRoute()
    {
        /* test 1: Application 2, Site 1

            Retrieve Catalog ID: 831 using Source ID: 1 and Catalog Type ID: 1000

                     $catalog_id = 0;
                     $url_sef_request = '';
                     $source_id = 1;
                     $catalog_type_id = 1000;
        */

        /* test 2: Application 2, Site 1

            Retrieve Catalog ID: 1075 using $url_sef_request = 'articles'

                $catalog_id = 0;
                $url_sef_request = 'articles';
                $source_id = 0;
                $catalog_type_id = 0;
        */

        /* test 3: Application 2, Site 1

            Retrieve Item: for Catalog ID 1075

                $catalog_id = 1075;
                $url_sef_request = '';
                $source_id = 0;
                $catalog_type_id = 0;
         */
        $this->resource_query->setModelRegistry('use_special_joins', 1);
        $this->resource_query->setModelRegistry('process_events', 0);
        $this->resource_query->setModelRegistry('query_object', 'item');

        $prefix = $this->resource_query->getModelRegistry('primary_prefix', 'a');
        $key    = $this->resource_query->getModelRegistry('primary_key', 'id');

        $this->resource_query->model->query->where(
            $this->resource_query->model->database->qn($prefix)
            . ' . '
            . $this->resource_query->model->database->qn('sef_request')
            . ' = '
            . $this->resource_query->model->database->q($this->runtime_data->application->path)
        );

        /** Extension Join */

        /** Standard Query Values */
        $this->resource_query->model->query->where(
            $this->resource_query->model->database->qn($prefix)
            . ' . '
            . $this->resource_query->model->database->qn('application_id')
            . ' = '
            . $this->resource_query->model->database->q($this->runtime_data->application->id)
        );

        $this->resource_query->model->query->where(
            $this->resource_query->model->database->qn($prefix)
            . ' . '
            . $this->resource_query->model->database->qn('page_type')
            . ' <> '
            . $this->resource_query->model->database->q($this->runtime_data->reference_data->page_type_link)
        );

        $this->resource_query->model->query->where(
            $this->resource_query->model->database->qn($prefix)
            . ' . '
            . $this->resource_query->model->database->qn('enabled')
            . ' = 1 '
        );

        /** Run the Query */
        try {
            $item = $this->resource_query->getData();

        } catch (Exception $e) {
            throw new RuntimeException ($e->getMessage());
        }

        $this->runtime_data->route->model_registry = $this->resource_query->getModelRegistry('*');

        /** 404 */
        if (count($item) == 0 || $item === false) {
            $this->runtime_data->route->route_found = 0;

            return $this->runtime_data;
        }

        /** Redirect */
        if ((int)$item->redirect_to_id == 0) {
        } else {
            $this->runtime_data->redirect_to_id = (int)$item->redirect_to_id;

            return $this->runtime_data;
        }

        /** Found */
        $this->runtime_data->route->route_found = 1;
        $this->runtime_data->route->home        = 0;

        foreach (\get_object_vars($item) as $key => $value) {

            $this->runtime_data->route->$key = $value;

            if ($key == 'b_model_name') {
                $this->runtime_data->route->model_name = ucfirst(strtolower($item->b_model_name));
                $this->runtime_data->route->model_type = ucfirst(strtolower($item->b_model_type));
                $this->runtime_data->route->model_registry_name
                                                     = $this->runtime_data->route->model_name . $this->runtime_data->route->model_type;
            }
        }

        $this->runtime_data->route->catalog_id = $this->runtime_data->route->id;

        return $this->runtime_data;
    }
}