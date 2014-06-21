<?php
/**
 * Route Test
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route;

use stdClass;
use CommonApi\Controller\ReadControllerInterface;
use Molajo\Route\Adapter\Database;

use Molajo\Route\Driver;

/**
 * Route Test
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2014 Amy Stephen. All rights reserved.
 * @since      1.0.0
 */
class RouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Object
     */
    protected $route;

    /**
     * Get the Route Adapter Handler
     */
    protected function getAdapter(
        $url_force_ssl = 0,
        $is_secure = 1
    ) {
        $request               = new stdClass();
        $request->method       = 'GET';
        $request->content_type = "text/html";
        $request->base_url     = "http://site2/";
        $request->url          = "http://site2/admin/articles";
        $request->scheme       = "http://";
        $request->secure       = $is_secure;
        $request->user         = '';
        $request->password     = '';
        $request->userinfo     = '';
        $request->host         = "site2";
        $request->port         = "80";
        $request->authority    = "site2/";
        $request->path         = "admin/articles";
        $request->query        = "";
        $request->parameters   = array();

        $application_home_catalog_id = 1072;
        $application_path            = 'articles';
        $application_id              = 2;
        $base_url                    = 'http://site2/admin/';

        $filters   = array();
        $filters[] = 'author';
        $filters[] = 'category';
        $filters[] = 'tag';
        $filters[] = 'date';
        $filters[] = 'theme';
        $filters[] = 'page';
        $filters[] = 'edit';
        $filters[] = 'delete';

        $task_to_action              = array();
        $task_to_action['order']     = 'view';
        $task_to_action['publish']   = 'update';
        $task_to_action['unpublish'] = 'update';
        $task_to_action['delete']    = 'delete';

        $page_types = array(
            'new'    => 'new',
            'edit'   => 'edit',
            'delete' => 'delete'
        );

        $resource_query = new MockReadController();

        return new Database(
            $request,
            $url_force_ssl,
            $application_home_catalog_id,
            $application_path,
            $application_id,
            $base_url,
            $filters,
            $task_to_action,
            $page_types,
            $resource_query
        );
    }

    /**
     * Initialises Adapter
     */
    protected function getRouteDriver($adapter)
    {
        return new Driver($adapter);
    }

    /**
     * verifySecureProtocol -- Not SSL
     *
     * @return  $this
     * @since   1.0
     */
    public function testVerifySecureProtocolNotSSL()
    {
        $adapter     = $this->getAdapter($url_force_ssl = 0, $is_secure = 1);
        $instance = $this->getRouteDriver($adapter);

        $route = $instance->verifySecureProtocol();

        $this->assertEquals($route->route_found, null);
        $this->assertEquals($route->error_code, 0);
        $this->assertEquals($route->redirect_to_url, null);
        $this->assertEquals($route->home, 0);
        $this->assertEquals($route->catalog_id, 0);
        $this->assertEquals($route->action, '');
        $this->assertEquals($route->method, '');
        $this->assertEquals($route->base_url, '');
        $this->assertEquals($route->path, '');
        $this->assertEquals($route->filters_array, array());
        $this->assertEquals($route->post_variable_array, array());
        $this->assertEquals($route->request_task, '');
        $this->assertEquals($route->request_task_values, array());
        $this->assertEquals($route->model_name, '');
        $this->assertEquals($route->model_type, '');
        $this->assertEquals($route->model_registry_name, '');
        $this->assertEquals($route->page_type, '');

        return $this;
    }

    /**
     * verifySecureProtocol -- SSL
     *
     * @return  $this
     * @since   1.0
     */
    public function testVerifySecureProtocolSSL()
    {
        $adapter     = $this->getAdapter($url_force_ssl = 0, $is_secure = 1);
        $instance = $this->getRouteDriver($adapter);

        $route = $instance->verifySecureProtocol();

        $this->assertEquals($route->route_found, null);
        $this->assertEquals($route->error_code, 0);
        $this->assertEquals($route->redirect_to_url, null);
        $this->assertEquals($route->home, 0);
        $this->assertEquals($route->catalog_id, 0);
        $this->assertEquals($route->action, '');
        $this->assertEquals($route->method, '');
        $this->assertEquals($route->base_url, '');
        $this->assertEquals($route->path, '');
        $this->assertEquals($route->filters_array, array());
        $this->assertEquals($route->post_variable_array, array());
        $this->assertEquals($route->request_task, '');
        $this->assertEquals($route->request_task_values, array());
        $this->assertEquals($route->model_name, '');
        $this->assertEquals($route->model_type, '');
        $this->assertEquals($route->model_registry_name, '');
        $this->assertEquals($route->page_type, '');

        return $this;
    }

    /**
     * verifySecureProtocol -- Error
     *
     * @return  $this
     * @since   1.0
     */
    public function testVerifySecureProtocolError()
    {
        $adapter     = $this->getAdapter($url_force_ssl = 1, $is_secure = 0);
        $instance = $this->getRouteDriver($adapter);

        $route = $instance->verifySecureProtocol();

        $this->assertEquals($route->route_found, null);
        $this->assertEquals($route->error_code, 301);
        $this->assertEquals($route->redirect_to_url, 1072);
        $this->assertEquals($route->home, 0);
        $this->assertEquals($route->catalog_id, 0);
        $this->assertEquals($route->action, '');
        $this->assertEquals($route->method, '');
        $this->assertEquals($route->base_url, '');
        $this->assertEquals($route->path, '');
        $this->assertEquals($route->filters_array, array());
        $this->assertEquals($route->post_variable_array, array());
        $this->assertEquals($route->request_task, '');
        $this->assertEquals($route->request_task_values, array());
        $this->assertEquals($route->model_name, '');
        $this->assertEquals($route->model_type, '');
        $this->assertEquals($route->model_registry_name, '');
        $this->assertEquals($route->page_type, '');

        return $this;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
}

use Molajo\Query;

class MockReadController implements ReadControllerInterface
{
    /**
     * Method to get data from model
     *
     * @return  mixed
     * @since   1.0
     */
    public function getData()
    {

    }
}
