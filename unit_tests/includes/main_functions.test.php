<?php
/**
 * Necessary global variables 
 */
global $db;
global $ADODB_FETCH_MODE;
global $w2p_performance_dbtime;
global $w2p_performance_old_dbqueries;
global $AppUI;

require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';

// Need this to test actions that require permissions.
$AppUI  = new CAppUI;
$_POST['login'] = 'login';
$_REQUEST['login'] = 'sql';
$AppUI->login('admin', 'passwd');

require_once W2P_BASE_DIR . '/includes/session.php';
require_once 'PHPUnit/Framework.php';
/**
 * Main_Functions_Test Class.
 * 
 * Class to test the main_functions include
 * @author D. Keith Casey, Jr. <caseydk@users.sourceforge.net>
 * @package web2project
 * @subpackage unit_tests
 */
class Main_Functions_Test extends PHPUnit_Framework_TestCase 
{
	public function testW2PgetParam()
	{
		$params = array('m' => 'projects', 'a' => 'view', 'v' => '<script>alert</script>', 
				'html' => '<div onclick="doSomething()">asdf</div>', '<script>' => 'Something Nasty');

		$this->assertEquals('projects', w2PgetParam($params, 'm'));

		$this->assertEquals('', w2PgetParam($params, 'NotGonnaBeThere'));

		$this->assertEquals('Some Default', w2PgetParam($params, 'NotGonnaBeThere', 'Some Default'));

		//$this->markTestIncomplete("Currently w2PgetParam redirects for tainted names.. what do we do there?");
		
		//$this->markTestIncomplete("Currently w2PgetParam redirects for tainted values.. what do we do there?");
	}
	
	public function testW2PgetCleanParam()
	{
		$params = array('m' => 'projects', 'a' => 'view', 'v' => '<script>alert</script>', 
				'html' => '<div onclick="doSomething()">asdf</div>', '<script>' => 'Something Nasty');

		$this->assertEquals('projects', w2PgetCleanParam($params, 'm'));

		$this->assertEquals('', w2PgetCleanParam($params, 'NotGonnaBeThere'));

		$this->assertEquals('Some Default', w2PgetCleanParam($params, 'NotGonnaBeThere', 'Some Default'));

		$this->assertEquals($params['v'], w2PgetCleanParam($params, 'v', ''));

		$this->assertEquals($params['html'], w2PgetCleanParam($params, 'html', ''));

		$this->assertEquals($params['<script>'], w2PgetCleanParam($params, '<script>', ''));

		//$this->markTestIncomplete("This function does *nothing* for tainted values and I suspect it should...");
	}

	public function testArrayMerge()
	{
		$array1 = array('a', 'b', 'c', 4 => 'd', 5 => 'e');
		$array2 = array('z', 6 => 'y', 7 => 'x', 4 => 'w', 5 => 'v');
		$newArray = arrayMerge($array1, $array2);

		$this->assertEquals('b', $newArray[1]);		//	Tests no overwrite
		$this->assertEquals('w', $newArray[4]);		//	Tests explicit overwrite
		$this->assertEquals('z', $newArray[0]);		//	Tests conincidental overwrite
	}
	public function testW2PgetConfig()
	{
		global $w2Pconfig;

		$this->assertEquals('web2project.net', w2PgetConfig('site_domain'));
		$this->assertEquals(null, w2PgetConfig('NotGonnaBeThere'));
		$this->assertEquals('Some Default', w2PgetConfig('NotGonnaBeThere', 'Some Default'));
	}

    public function testFilterCurrency()
    {
        $this->assertEquals('123456789', filterCurrency('123456789'));

        $this->assertEquals('1234567.89', filterCurrency('1234567,89'));
        $this->assertEquals('1234567.89', filterCurrency('1.234.567,89'));

        $this->assertEquals('1234567.89', filterCurrency('1234567.89'));
        $this->assertEquals('1234567.89', filterCurrency('1,234,567.89'));
    }

	public function testConvert2days()
	{		
		$hours = 1;		
		$this->assertEquals(0.125, convert2days($hours, 0));

		$hoursIndicator = 1;
		$hours = 8;
		$this->assertEquals(1, convert2days($hours, $hoursIndicator));

		$dayIndicator = 24;
		$days = 1;
		$this->assertEquals(1, convert2days($days, $dayIndicator));
	}
  
  public function test__autoload()
  {
    $this->assertTrue(class_exists('CProject'));
    $search = new smartsearch();
    $this->assertTrue(class_exists('smartsearch'));
  }


  /**
   * Tests the proper creation of a link
   */
  public function test_w2p_url()
  {
    $target = '<a href="http://web2project.net" target="_new">http://web2project.net</a>';
    $linkText = w2p_url('http://web2project.net');
    $this->assertEquals($target, $linkText);

    $target = '';
    $linkText = w2p_url('');
    $this->assertEquals($target, $linkText);

    $target = '<a href="http://web2project.net" target="_new">web2project</a>';
    $linkText = w2p_url('http://web2project.net', 'web2project');
    $this->assertEquals($target, $linkText);
  }
  public function test_w2p_check_url()
  {
    $this->assertTrue(w2p_check_url('http://web2project.net'));
    $this->assertTrue(w2p_check_url('http://bugs.web2project.net'));
    $this->assertTrue(w2p_check_url('web2project.net'));
    $this->assertTrue(w2p_check_url('http://forums.web2project.net/'));
    $this->assertTrue(w2p_check_url('http://wiki.web2project.net/'));
    $this->assertTrue(w2p_check_url('http://wiki.web2project.net/index.php?title=Main_Page'));
    $this->assertTrue(w2p_check_url('wiki.web2project.net/index.php?title=Category:Frequently_Asked_Questions'));

    //$this->assertFalse(w2p_check_url('httpweb2project.net'));
    //$this->assertFalse(w2p_check_url('http://web2project'));
    //$this->assertFalse(w2p_check_url('http://.net'));
  }

  /**
   * Tests the proper creation of an email link
   */
  public function test_w2p_email()
  {
    $target = '<a href="mailto:test@test.com">test@test.com</a>';
    $linkText = w2p_email('test@test.com');
    $this->assertEquals($target, $linkText);

    $target = '';
    $linkText = w2p_email('');
    $this->assertEquals($target, $linkText);

    $target = '<a href="mailto:test@test.com">web2project</a>';
    $linkText = w2p_email('test@test.com', 'web2project');
    $this->assertEquals($target, $linkText);
  }
  public function test_w2p_check_email()
  {
    $this->assertTrue(w2p_check_email('tests@web2project.net'));
    $this->assertTrue(w2p_check_email('tests@bugs.web2project.net'));

    $this->assertFalse(w2p_check_email('@web2project.net'));
    $this->assertFalse(w2p_check_email('testsweb2project.net'));
    $this->assertFalse(w2p_check_email('tests@web2project'));
    $this->assertFalse(w2p_check_email('tests@'));
    $this->assertFalse(w2p_check_email('tests@.net'));
  }

  /**
   * Tests the proper creation of an email link
   */
  public function test_w2p_textarea()
  {
    $target = '';
    $linkText = w2p_textarea('');
    $this->assertEquals($target, $linkText);

    $target = 'Have you seen this - <a href="http://web2project.net" target="_blank">http://web2project.net</a> ?';
    $linkText = w2p_textarea('Have you seen this - http://web2project.net ?');
    $this->assertEquals($target, $linkText);

    $target = '<a href="http://web2project.net" target="_blank">http://web2project.net</a> is a fork of <a href="http://dotproject.net" target="_blank">http://dotproject.net</a>';
    $linkText = w2p_textarea('http://web2project.net is a fork of http://dotproject.net');
    $this->assertEquals($target, $linkText);

    $target = '<a href="http://web2project.net" target="_blank">http://web2project.net</a> is a great site';
    $linkText = w2p_textarea('http://web2project.net is a great site');
    $this->assertEquals($target, $linkText);

    $target = 'Please check out <a href="http://web2project.net" target="_blank">http://web2project.net</a>';
    $linkText = w2p_textarea('Please check out http://web2project.net');
    $this->assertEquals($target, $linkText);
  }
}