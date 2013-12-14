<?php
/**
 * Route Test
 *
 * @package    Molajo
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 */
namespace Molajo\Route\Test;

/**
 * Route Test
 *
 * @author     Amy Stephen
 * @license    http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright  2013 Amy Stephen. All rights reserved.
 * @since      1.0
 */
class RouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Object
     */
    protected $route_model;

    /**
     * Initialises Adapter
     */
    protected function setUp()
    {

        return;
    }

    /**
     * Connect to the Cache
     *
     * @param   array $options
     *
     * @return  $this
     * @since   1.0
     */
    public function testMock()
    {
        $one = 1;
        $this->assertEquals($one, 1);

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
