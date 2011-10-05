<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing Date functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. Please see the LICENSE file in root of site
 * for further details
 *
 * @category    Date
 * @package     web2project
 * @subpackage  unit_tests
 * @author      D. Keith Casey, Jr.
 * @copyright   2007-2010 The web2Project Development Team <w2p-developers@web2project.net>
 * @link        http://www.web2project.net
 */

require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';

require_once 'PHPUnit/Framework.php';

/**
 * BaseTest Class.
 *
 * Class to test the base controller
 * @author Trevor Morse
 * @package web2project
 * @subpackage unit_tests
 */
class Base_Test extends PHPUnit_Framework_TestCase
{

    /**
     * An AppUI object for validation
     *
     * @param CAppUI
     * @access private
     */
    private $appUI;

    /**
     * An w2p_Controllers_Base class for use in tests
     *
     * @param w2P_Controllers_Base
     * @access protected
     */
    protected $obj = null;

    /**
     * Create an AppUI before running tests
     */
    protected function setUp()
    {
        parent::setUp();
        $this->appUI = new w2p_Core_CAppUI();
        $_POST['login'] = 'login';
        $_REQUEST['login'] = 'sql';
        $this->appUI->login('admin', 'passwd');
    }

    /**
     * Tests that a new base controller object has the proper attributes
     */
    public function testNewBaseAttributes()
    {
        $this->obj = new w2p_Controllers_Base(new CLink(), false, 'prefix', '/success', '/failure');

        $this->assertInstanceOf('w2p_Controllers_Base',     $this->obj);
        $this->assertObjectHasAttribute('delete',           $this->obj);
        $this->assertObjectHasAttribute('successPath',      $this->obj);
        $this->assertObjectHasAttribute('errorPath',        $this->obj);
        $this->assertObjectHasAttribute('accessDeniedPath', $this->obj);
        $this->assertObjectHasAttribute('object',           $this->obj);
        $this->assertObjectHasAttribute('success',          $this->obj);
        $this->assertObjectHasAttribute('resultPath',       $this->obj);
        $this->assertObjectHasAttribute('resultMessage',    $this->obj);
        $this->assertInstanceOf('CLink',                    $this->obj->object);
    }

    /**
     * Tests that a new base controller objects attributes have the proper values
     */
    public function testNewBaseAttributesValues()
    {
        $this->obj = new w2p_Controllers_Base(new CLink(), false, 'prefix', '/success', '/failure');

        $this->assertInstanceOf('w2p_Controllers_Base',                              $this->obj);
        $this->assertAttributeEquals(false, 'delete',                                $this->obj);
        $this->assertAttributeEquals('prefix', 'prefix',                             $this->obj);
        $this->assertAttributeEquals('/success', 'successPath',                      $this->obj);
        $this->assertAttributeEquals('/failure', 'errorPath',                        $this->obj);
        $this->assertAttributeEquals('m=public&a=access_denied', 'accessDeniedPath', $this->obj);
        $this->assertEquals(false,                                                   $this->obj->success);
        $this->assertEquals('',                                                      $this->obj->resultPath);
        $this->assertEquals('',                                                      $this->obj->resultMessage);
        $this->assertInstanceOf('CLink',                                             $this->obj->object);
    }

    /**
     * Tests setting the access denied path
     */
    public function testSetAccessDeniedPath()
    {
        $this->obj = new w2p_Controllers_Base(new CLink(), false, 'prefix', '/success', '/failure');

        $this->obj->setAccessDeniedPath('/somepath');

        $this->assertAttributeEquals('/somepath', 'accessDeniedPath', $this->obj);
    }

    /**
     * Tests process when bind of object fails
     */
    public function testProcessInvalidBind()
    {
        $this->obj = new w2p_Controllers_Base(new CLink(), false, 'prefix', '/success', '/failure');

        $testAppUI = $this->obj->process($this->appUI, array(0));

        $this->assertEquals('/failure', $this->obj->resultPath);
        $this->assertEquals('CLink::store-check failed - link name is not set<br />CLink::store-check failed - link url is not set<br />CLink::store-check failed - link owner is not set', $testAppUI->msg);
    }
}
