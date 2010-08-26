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

/**
 * Necessary global variables
 */
global $AppUI;
global $_DATE_TIMEZONE_DATA;
global $w2Pconfig;

require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';

/*
 * Need this to test actions that require permissions.
 */
$AppUI  = new CAppUI;
$_POST['login'] = 'login';
$_REQUEST['login'] = 'sql';
$AppUI->login('admin', 'passwd');

require_once 'PHPUnit/Framework.php';

/**
 * DateTest Class.
 *
 * Class to test the date include
 * @author D. Keith Casey, Jr.
 * @package web2project
 * @subpackage unit_tests
 */
class Date_Test extends PHPUnit_Framework_TestCase
{

    /**
     * Tests constructor without arguments
     */
    public function testConstructorNoDateTimeNoTz()
    {
        $date       = new CDate();
        $datetime   = new DateTime();

        $this->assertType('CDate',                  $date);
        $this->assertEquals($datetime->format('Y'), $date->year);
        $this->assertEquals($datetime->format('m'), $date->month);
        $this->assertEquals($datetime->format('d'), $date->day);
        $this->assertEquals($datetime->format('H'), $date->hour);
        $this->assertEquals($datetime->format('i'), $date->minute);
        $this->assertEquals($datetime->format('s'), $date->second);
        $this->assertEquals(0,                      $date->tz['offset']);
        $this->assertEquals('Greenwich Mean Time',  $date->tz['longname']);
        $this->assertEquals('GMT',                  $date->tz['shortname']);
        $this->assertEquals('British Summer Time',  $date->tz['dstlongname']);
        $this->assertEquals('BST',                  $date->tz['dstshortname']);
        $this->assertEquals('Europe/London',        $date->tz['id']);
        $this->assertTrue($date->tz['hasdst']);
    }

    /**
     * Tests constructor with a datetime, but no timezone
     */
    public function testConstructorDateTimeNoTz()
    {
        $date       = new CDate('2010-08-07 11:00:00');
        $datetime   = new DateTime('2010-08-07 11:00:00');

        $this->assertType('CDate',                  $date);
        $this->assertEquals($datetime->format('Y'), $date->year);
        $this->assertEquals($datetime->format('m'), $date->month);
        $this->assertEquals($datetime->format('d'), $date->day);
        $this->assertEquals($datetime->format('H'), $date->hour);
        $this->assertEquals($datetime->format('i'), $date->minute);
        $this->assertEquals($datetime->format('s'), $date->second);
        $this->assertEquals(0,                      $date->tz['offset']);
        $this->assertEquals('Greenwich Mean Time',  $date->tz['longname']);
        $this->assertEquals('GMT',                  $date->tz['shortname']);
        $this->assertEquals('British Summer Time',  $date->tz['dstlongname']);
        $this->assertEquals('BST',                  $date->tz['dstshortname']);
        $this->assertEquals('Europe/London',        $date->tz['id']);
        $this->assertTrue($date->tz['hasdst']);
    }

    /**
     * Tests constructor with a datetime and timezone
     */
    public function testConstructorDateTimeTz()
    {
        $date       = new CDate('2010-08-07 11:00:00', 'America/Halifax');
        $datetime   = new DateTime('2010-08-07 11:00:00', new DateTimeZone('America/Halifax'));

        $this->assertType('CDate',                      $date);
        $this->assertEquals($datetime->format('Y'),     $date->year);
        $this->assertEquals($datetime->format('m'),     $date->month);
        $this->assertEquals($datetime->format('d'),     $date->day);
        $this->assertEquals($datetime->format('H'),     $date->hour);
        $this->assertEquals($datetime->format('i'),     $date->minute);
        $this->assertEquals($datetime->format('s'),     $date->second);
        $this->assertEquals(-14400000,                  $date->tz['offset']);
        $this->assertEquals('Atlantic Standard Time',   $date->tz['longname']);
        $this->assertEquals('AST',                      $date->tz['shortname']);
        $this->assertEquals('Atlantic Daylight Time',   $date->tz['dstlongname']);
        $this->assertEquals('ADT',                      $date->tz['dstshortname']);
        $this->assertEquals('America/Halifax',          $date->tz['id']);
        $this->assertTrue($date->tz['hasdst']);
    }

    /**
     * Tests constructor with an invalid datetime
     */
    public function testConstructorInvalidDateTime()
    {
        $date = new CDate('2010-35-35 28:65:85');

        $this->assertType('CDate',                  $date);
        $this->assertEquals(2010,                   $date->year);
        $this->assertEquals(35,                     $date->month);
        $this->assertEquals(35,                     $date->day);
        $this->assertEquals(28,                     $date->hour);
        $this->assertEquals(65,                     $date->minute);
        $this->assertEquals(85,                     $date->second);
        $this->assertEquals(0,                      $date->tz['offset']);
        $this->assertEquals('Greenwich Mean Time',  $date->tz['longname']);
        $this->assertEquals('GMT',                  $date->tz['shortname']);
        $this->assertEquals('British Summer Time',  $date->tz['dstlongname']);
        $this->assertEquals('BST',                  $date->tz['dstshortname']);
        $this->assertEquals('Europe/London',        $date->tz['id']);
        $this->assertEquals('2010-35-35 28:65:85',  $date->getDate()); // WTF?
        $this->assertTrue($date->tz['hasdst']);
    }

    /**
     * Tests constructor with an invalid timezone
     *
     * expectedException PHPUnit_Framework_Error
     */
    public function testConstructorInvalidTimezone()
    {
        $date = new CDate('2010-08-07 22:10:27', 'Halifax');
        $datetime = new DateTime('2010-08-07 22:10:27');

        $this->assertType('CDate',                  $date);
        $this->assertEquals($datetime->format('Y'), $date->year);
        $this->assertEquals($datetime->format('m'), $date->month);
        $this->assertEquals($datetime->format('d'), $date->day);
        $this->assertEquals($datetime->format('H'), $date->hour);
        $this->assertEquals($datetime->format('i'), $date->minute);
        $this->assertEquals($datetime->format('s'), $date->second);
        $this->assertEquals('Halifax',              $date->tz['id']);
        $this->assertFalse(isset($data->tz['offset']));
        $this->assertFalse(isset($data->tz['longname']));
        $this->assertFalse(isset($data->tz['shortname']));
        $this->assertFalse(isset($data->tz['dstlongname']));
        $this->assertFalse(isset($data->tz['dstshortname']));
        $this->assertFalse(isset($data->tz['hasdst']));
    }

    /**
     * Tests compare function when days are greater and don't convert timezone
     */
    public function testCompareDayGreaterNoConvertTz()
    {
        $date1 = new CDate('2010-08-07 00:00:00');
        $date2 = new CDate('2010-08-06 00:00:00');

        $this->assertEquals(1, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when days are the same, hours are greater
     * and don't convert timezone
     */
    public function testCompareHourGreaterNoConvertTz()
    {
        $date1 = new CDate('2010-08-07 02:00:00');
        $date2 = new CDate('2010-08-07 01:00:00');

        $this->assertEquals(1, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when days and hours are the same, minutes are
     * greater and don't convert timezone
     */
    public function testCompareMinuteGreaterNotConvertTz()
    {
        $date1 = new CDate('2010-08-07 01:01:00');
        $date2 = new CDate('2010-08-07 01:00:00');

        $this->assertEquals(1, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when days, hours and minutes are the same,
     * seconds are greater and don't convert timezone
     */
    public function testCompareSecondGreaterNoConvertTz()
    {
        $date1 = new CDate('2010-08-07 01:01:01');
        $date2 = new CDate('2010-08-07 01:01:00');

        $this->assertEquals(1, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when days are lesser and don't convert timezone
     */
    public function testCompareDayLesserNoConvertTz()
    {
        $date1 = new CDate('2010-08-06 00:00:00');
        $date2 = new CDate('2010-08-07 00:00:00');

        $this->assertEquals(-1, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when days are the same, hours are lesser
     * and don't convert timezone
     */
    public function testCompareHourLesserNoConvertTz()
    {
        $date1 = new CDate('2010-08-07 01:00:00');
        $date2 = new CDate('2010-08-07 02:00:00');

        $this->assertEquals(-1, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when days and hours are the same, minutes are
     * lesser and don't convert timezone
     */
    public function testCompareMinuteLesserNotConvertTz()
    {
        $date1 = new CDate('2010-08-07 01:00:00');
        $date2 = new CDate('2010-08-07 01:01:00');

        $this->assertEquals(-1, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when days, hours and minutes are the same,
     * seconds are lesser and don't convert timezone
     */
    public function testCompareSecondLesserNoConvertTz()
    {
        $date1 = new CDate('2010-08-07 01:01:00');
        $date2 = new CDate('2010-08-07 01:01:01');

        $this->assertEquals(-1, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when both dates are equal, don't convert timezones
     */
    public function testCompareEqualNoConvertTz()
    {
        $date1 = new CDate('2010-08-07 00:00:00');
        $date2 = new CDate('2010-08-07 00:00:00');

        $this->assertEquals(0, $date1->compare($date1, $date2));
    }

    /**
     * Tests compare function when days are greater and convert timezone
     */
    public function testCompareDayGreaterConvertTz()
    {
        $date1 = new CDate('2010-08-07 00:00:00', 'America/Halifax');
        $date2 = new CDate('2010-08-06 00:00:00', 'America/Chicago');

        $this->assertEquals(1, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests compare function when days are the same, hours are greater
     * and convert timezone
     */
    public function testCompareHourGreaterConvertTz()
    {
        $date1 = new CDate('2010-08-07 00:00:00', 'America/Halifax');
        $date2 = new CDate('2010-08-06 21:00:00', 'America/Chicago');

        $this->assertEquals(1, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests compare function when days and hours are the same, minutes are
     * greater and convert timezone
     */
    public function testCompareMinuteGreaterConvertTz()
    {
        $date1 = new CDate('2010-08-07 01:01:00', 'America/Halifax');
        $date2 = new CDate('2010-08-06 23:00:00', 'America/Chicago');

        $this->assertEquals(1, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests compare function when days, hours and minutes are the same,
     * seconds are greater and convert timezone
     */
    public function testCompareSecondGreaterConvertTz()
    {
        $date1 = new CDate('2010-08-07 01:01:01');
        $date2 = new CDate('2010-08-06 23:01:00');

        $this->assertEquals(1, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests compare function when days are lesser and convert timezone
     */
    public function testCompareDayLesserConvertTz()
    {
        $date1 = new CDate('2010-08-06 00:00:00', 'America/Halifax');
        $date2 = new CDate('2010-08-07 00:00:00', 'America/Chicago');

        $this->assertEquals(-1, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests compare function when days are the same, hours are lesser
     * and convert timezone
     */
    public function testCompareHourLesserConvertTz()
    {
        $date1 = new CDate('2010-08-07 01:00:00', 'America/Halifax');
        $date2 = new CDate('2010-08-07 02:00:00', 'America/Chicago');

        $this->assertEquals(-1, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests compare function when days and hours are the same, minutes are
     * lesser and convert timezone
     */
    public function testCompareMinuteLesserConvertTz()
    {
        $date1 = new CDate('2010-08-07 01:00:00', 'America/Halifax');
        $date2 = new CDate('2010-08-06 23:01:00', 'America/Chicago');

        $this->assertEquals(-1, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests compare function when days, hours and minutes are the same,
     * seconds are lesser and convert timezone
     */
    public function testCompareSecondLesserConvertTz()
    {
        $date1 = new CDate('2010-08-07 01:01:00', 'America/Halifax');
        $date2 = new CDate('2010-08-06 23:01:01', 'America/Chicago');

        $this->assertEquals(-1, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests compare function when both dates are equal, convert timezones
     */
    public function testCompareEqualConvertTz()
    {
        $date1 = new CDate('2010-08-07 00:00:00', 'America/Halifax');
        $date2 = new CDate('2010-08-06 22:00:00', 'America/Chicago');

        $this->assertEquals(0, $date1->compare($date1, $date2, true));
    }

    /**
     * Tests addDays function with a full positive day
     */
    public function testAddDaysPositiveFullDay()
    {
        $date = new CDate('2010-08-08 00:00:00');
        $date->addDays(3);

        $this->assertEquals('2010-08-11 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDays function with a full negative day
     */
    public function testAddDaysNegativeFullDay()
    {
        $date = new CDate('2010-08-08 00:00:00');
        $date->addDays(-3);

        $this->assertEquals('2010-08-05 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDays function with partial positive day
     */
    public function testAddDaysPositivePartialDay()
    {
        $date = new CDate('2010-08-08 00:00:00');
        $date->addDays(2.5);

        $this->assertEquals('2010-08-10 12:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Test addDays function with partial negative day
     */
    public function testAddDaysNegativePartialDay()
    {
        $date = new CDate('2010-08-08 00:00:00');
        $date->addDays(-2.5);

        $this->assertEquals('2010-08-05 12:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDays function with partial positive day spanning over midnight
     */
    public function testAddDaysPostivePartialDayAcrossDay()
    {
        $date = new CDate('2010-08-08 14:00:00');
        $date->addDays(2.5);

        $this->assertEquals('2010-08-11 02:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDays function when the days being added spans the end of a month
     */
    public function testAddDaysAcrossMonth()
    {
        $date = new CDate('2010-08-31 00:00:00');
        $date->addDays(2);

        $this->assertEquals('2010-09-02 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addDays function when the days being added spans the end of a year
     */
    public function testAddDaysAcrossYear()
    {
        $date = new CDate('2010-12-31 00:00:00');
        $date->addDays(2);

        $this->assertEquals('2011-01-02 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addMonths function with a positive full month
     */
    public function testAddMonthsPositiveFullMonth()
    {
        $date = new CDate('2010-08-08 00:00:00');
        $date->addMonths(2);

        $this->assertEquals('2010-10-08 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addMonths function with a negative full month
     */
    public function testAddMonthsNegativeFullMonth()
    {
        $date = new CDate('2010-08-08 00:00:00');
        $date->addMonths(-2);

        $this->assertEquals('2010-06-08 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addMonths function with a positive partial month
     */
    public function testAddMonthsPositivePartialMonth()
    {
        $date = new CDate('2010-08-08 00:00:00');
        $date->addMonths(2.5);

        $this->assertEquals('2010-10-08 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addMonths function with a negative partial month
     */
    public function testAddMonthsNegativePartialMonth()
    {
        $date = new CDate('2010-08-08 00:00:00');
        $date->addMonths(-2.5);

        $this->assertEquals('2010-06-08 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addMonths when the number of months spans a year
     */
    public function testAddMonthsPositiveAcrossYear()
    {
        $date = new CDate('2010-12-01 00:00:00');
        $date->addMonths(1);

        $this->assertEquals('2011-01-01 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests addMonths when the number of months spans a year
     */
    public function testAddMonthsNegativeAcrossYear()
    {
        $date = new CDate('2010-01-01 00:00:00');
        $date->addMonths(-1);

        $this->assertEquals('2009-12-01 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests dateDiff when not passing an object
     */
    public function testDateDiffNotObject()
    {
        $date = new CDate('2010-08-11 00:00:00');

        $this->assertFalse($date->dateDiff(1));
    }

    /**
     * Tests dateDiff when the date being compared against is in the future and
     * is a full day
     */
    public function testDateDiffFutureFullDay()
    {
       $date        = new CDate('2010-08-11 00:00:00');
       $date_diff   = $date->dateDiff(new CDate('2010-08-13 00:00:00'));

       $this->assertEquals(2, $date_diff);
    }

    /**
     * Tests dateDiff when the date being compared against is in the past and
     * is a full day
     */
    public function testDateDiffPastFullDay()
    {
        $date       = new CDate('2010-08-11 00:00:00');
        $date_diff  = $date->dateDiff(new CDate('2010-08-07 00:00:00'));

        $this->assertEquals(4, $date_diff);
    }

    /**
     * Tests dateDiff when teh date being compared against is in the future and
     * is a partial day
     */
    public function testDateDiffFuturePartialDay()
    {
        $date       = new CDate('2010-08-11 00:00:00');
        $date_diff  = $date->dateDiff(new CDate('2010-08-13 12:00:00'));

        $this->assertEquals(2, $date_diff);
    }

    /**
     * Tests dateDiff when the date being compared against is in the past and is
     * a partial day
     */
    public function testDateDiffPastPartialDay()
    {
        $date       = new CDate('2010-08-11 00:00:00');
        $date_diff  = $date->dateDiff(new CDate('2010-08-07 06:00:00'));

        $this->assertEquals(4, $date_diff);
    }

    /**
     * Tests setTime when hour is set and minute and second are not
     */
    public function testSetTimeHourNoMinuteNoSecond()
    {
        $date = new CDate('2010-08-11 00:00:00');
        $date->setTime(12);

        $this->assertEquals('2010-08-11 12:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests setTime when hour and minute is set and second is not
     */
    public function testSetTimeHourMinuteNoSecond()
    {
        $date = new CDate('2010-08-11 00:00:00');
        $date->setTime(12, 12);

        $this->assertEquals('2010-08-11 12:12:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests setTime when hour, minute and second are set
     */
    public function testSetTimeHourMinuteSecond()
    {
        $date = new CDate('2010-08-11 00:00:00');
        $date->setTime(12, 12, 12);

        $this->assertEquals('2010-08-11 12:12:12', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests setTime with invalid hour
     */
    public function testSetTimeInvalidHour()
    {
        $date = new CDate('2010-08-11 00:00:00');
        $date->setTime(25);

        $this->assertEquals('2010-08-11 00:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests setTime with invalid minute
     */
    public function testSetTimeInvalidMinute()
    {
        $date = new CDate('2010-08-11 00:00:00');
        $date->setTime(12, 61);

        $this->assertEquals('2010-08-11 12:00:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests setTime with invalid second
     */
    public function testSetTimeInvalidSecond()
    {
        $date = new CDate('2010-08-11 00:00:00');
        $date->setTime(12, 12, 61);

        $this->assertEquals('2010-08-11 12:12:00', $date->getDate(DATE_FORMAT_ISO));
    }

    /**
     * Tests isWorkingDay with a proper working day
     */
    public function testIsWorkingDayYes()
    {
        global $w2Pconfig;

        // Save old working days, and set our own for testing
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $w2Pconfig['cal_working_days']  = '1,2,3,4,5';

        $date = new CDate('2010-08-11 00:00:00');

        $this->assertTrue($date->isWorkingDay());

        // Restore old working days
        $w2Pconfig['cal_working_days'] = $old_working_days;
    }

    /**
     * Tests isWorkingDay with a non working day
     */
    public function testIsWorkingDayNo()
    {
        global $w2Pconfig;

        // Save old working day, and set our own for testing
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $w2Pconfig['cal_working_days']  = '1,2,3,4,5';

        $date = new CDate('2010-08-08 00:00:00');

        $this->assertFalse($date->isWorkingDay());

        // Restore old working days
        $w2Pconfig['cal_working_days'] = $old_working_days;
    }

    /**
     * Tests isWorkingDay with a proper working day, and cal_working_days
     * is null
     */
    public function testIsWorkingDayNullWorkingDaysYes()
    {
        global $w2Pconfig;

        // Save old working day, and set our won for testing
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $w2Pconfig['cal_working_days']  = null;

        $date = new CDate('2010-08-10 00:00:00');

        $this->assertTrue($date->isWorkingDay());

        // Restore old working days
        $w2Pconfig['cal_working_days'] = $old_working_days;
    }

    /**
     * Tests isWorkingDay with a non working day, and call_working_days
     * is null
     */
    public function testIsWorkingDayNullWorkingDaysNo()
    {
        global $w2Pconfig;

        // Save old working day, and set our won for testing
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $w2Pconfig['cal_working_days']  = null;

        $date = new CDate('2010-08-07 00:00:00');

        $this->assertFalse($date->isWorkingDay());

        // Restore old working days
        $w2Pconfig['cal_working_days'] = $old_working_days;

    }

    /**
     * Tests getAMPM when it is AM
     */
    public function testGetAMPMAM()
    {
        $date = new CDate('2010-08-19 10:00:00');

        $this->assertEquals('am', $date->getAMPM());
    }

    /**
     * Tests getAMPM when it is PM
     */
    public function testGetAMPMPM()
    {
        $date = new CDate('2010-08-19 13:00:00');

        $this->assertEquals('pm', $date->getAMPM());
    }

    /**
     * Tests next_working_day when not a working day, and not preserving
     * hours
     */
    public function testNextWorkingDayNotWorkingDayNoPreserveHours()
    {
        global $w2Pconfig;

        // Save old working days, day start and end
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $old_cal_day_start              = $w2Pconfig['cal_day_start'];
        $old_cal_day_end                = $w2Pconfig['cal_day_end'];
        $w2Pconfig['cal_working_days']  = '1,2,3,4,5';
        $w2Pconfig['cal_day_start']     = 9;
        $w2Pconfig['cal_day_end']       = 17;

        $date = new CDate('2010-08-07 00:00:00');
        $date->next_working_day();

        $this->assertEquals('2010-08-09 09:00:00', $date->getDate(DATE_FORMAT_ISO));

        // Restore old working days, day start and end
        $w2Pconfig['cal_working_days']  = $old_working_days;
        $w2Pconfig['cal_day_start']     = $old_cal_day_start;
        $w2Pconfig['cal_day_end']       = $old_cal_day_end;
    }

    /**
     * Tests next_working_day when not a working day and preserving hours
     */
    public function testNextWorkingDayNotWorkingDayPreserveHours()
    {
        global $w2Pconfig;

        // Save old working days, day start and end
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $old_cal_day_start              = $w2Pconfig['cal_day_start'];
        $old_cal_day_end                = $w2Pconfig['cal_day_end'];
        $w2Pconfig['cal_working_days']  = '1,2,3,4,5';
        $w2Pconfig['cal_day_start']     = 9;
        $w2Pconfig['cal_day_end']       = 17;

        $date = new CDate('2010-08-07 10:00:00');
        $date->next_working_day(true);

        $this->assertEquals('2010-08-09 10:00:00', $date->getDate(DATE_FORMAT_ISO));

        // Restore old working days, day start and end
        $w2Pconfig['cal_working_days']  = $old_working_days;
        $w2Pconfig['cal_day_start']     = $old_cal_day_start;
        $w2Pconfig['cal_day_end']       = $old_cal_day_end;
    }

    /**
     * Tests next_working_day when its past end of working day and not
     * preserving hours
     */
    public function testNextWorkingDayPastEndOfDayNoPreserveHours()
    {
        global $w2Pconfig;

        // Save old working days, day start and end
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $old_cal_day_start              = $w2Pconfig['cal_day_start'];
        $old_cal_day_end                = $w2Pconfig['cal_day_end'];
        $w2Pconfig['cal_working_days']  = '1,2,3,4,5';
        $w2Pconfig['cal_day_start']     = 9;
        $w2Pconfig['cal_day_end']       = 17;

        $date = new CDate('2010-08-24 18:00:00');
        $date->next_working_day();

        $this->assertEquals('2010-08-25 09:00:00', $date->getDate(DATE_FORMAT_ISO));

        // Restore old working days, day start and end
        $w2Pconfig['cal_working_days']  = $old_working_days;
        $w2Pconfig['cal_day_start']     = $old_cal_day_start;
        $w2Pconfig['cal_day_end']       = $old_cal_day_end;
    }

    /**
     * Tests next_working_day when its past end of working day and preserving
     * hours
     */
    public function testNextWorkingDayPastEndOfDayPreserveHours()
    {
        global $w2Pconfig;

        // Save old working days, day start and end
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $old_cal_day_start              = $w2Pconfig['cal_day_start'];
        $old_cal_day_end                = $w2Pconfig['cal_day_end'];
        $w2Pconfig['cal_working_days']  = '1,2,3,4,5';
        $w2Pconfig['cal_day_start']     = 9;
        $w2Pconfig['cal_day_end']       = 17;

        $date = new CDate('2010-08-24 18:00:00');
        $date->next_working_day(true);

        $this->assertEquals('2010-08-25 18:00:00', $date->getDate(DATE_FORMAT_ISO));

        // Restore old working days, day start and end
        $w2Pconfig['cal_working_days']  = $old_working_days;
        $w2Pconfig['cal_day_start']     = $old_cal_day_start;
        $w2Pconfig['cal_day_end']       = $old_cal_day_end;
    }

    /**
     * Tests next_working_day when its exactly the end of working day and not
     * preserving hours
     */
    public function testNextWorkingDayEndOfDayNoPreserveHours()
    {
        global $w2Pconfig;

        // Save old working days, day start and end
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $old_cal_day_start              = $w2Pconfig['cal_day_start'];
        $old_cal_day_end                = $w2Pconfig['cal_day_end'];
        $w2Pconfig['cal_working_days']  = '1,2,3,4,5';
        $w2Pconfig['cal_day_start']     = 9;
        $w2Pconfig['cal_day_end']       = 17;

        $date = new CDate('2010-08-24 17:00:00');
        $date->next_working_day();

        $this->assertEquals('2010-08-25 09:00:00', $date->getDate(DATE_FORMAT_ISO));

        // Restore old working days, day start and end
        $w2Pconfig['cal_working_days']  = $old_working_days;
        $w2Pconfig['cal_day_start']     = $old_cal_day_start;
        $w2Pconfig['cal_day_end']       = $old_cal_day_end;
    }

    /**
     * Tests next_working_day when it is a working day
     */
    public function testNextWorkingDayIsWorkingDay()
    {
        global $w2Pconfig;

        // Save old working days, day start and end
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $old_cal_day_start              = $w2Pconfig['cal_day_start'];
        $old_cal_day_end                = $w2Pconfig['cal_day_end'];
        $w2Pconfig['cal_working_days']  = '1,2,3,4,5';
        $w2Pconfig['cal_day_start']     = 9;
        $w2Pconfig['cal_day_end']       = 17;

        $date = new CDate('2010-08-24 13:00:00');
        $date->next_working_day();

        $this->assertEquals('2010-08-24 13:00:00', $date->getDate(DATE_FORMAT_ISO));

        // Restore old working days, day start and end
        $w2Pconfig['cal_working_days']  = $old_working_days;
        $w2Pconfig['cal_day_start']     = $old_cal_day_start;
        $w2Pconfig['cal_day_end']       = $old_cal_day_end;
    }

    /**
     * Tests prev_working_day when not a working day and not preserving hours
     */
    public function testPrevWorkingDayNotWorkingDayNoPreserveHours()
    {
        global $w2Pconfig;

        // Save old working days, day start and end
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $old_cal_day_start              = $w2Pconfig['cal_day_start'];
        $old_cal_day_end                = $w2Pconfig['cal_day_end'];
        $w2Pconfig['cal_working_days']  = '1,2,3,4,5';
        $w2Pconfig['cal_day_start']     = 9;
        $w2Pconfig['cal_day_end']       = 17;

        $date = new CDate('2010-08-07 00:00:00');
        $date->prev_working_day();

        $this->assertEquals('2010-08-06 17:00:00', $date->getDate(DATE_FORMAT_ISO));

        // Restore old working days, day start and end
        $w2Pconfig['cal_working_days']  = $old_working_days;
        $w2Pconfig['cal_day_start']     = $old_cal_day_start;
        $w2Pconfig['cal_day_end']       = $old_cal_day_end;
    }

    /**
     * Tests prev_working_day when not a working day and preserving hours
     */
    public function testPrevWorkingDayNotWorkingDayPreserveHours()
    {
        global $w2Pconfig;

        // Save old working days, day start and end
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $old_cal_day_start              = $w2Pconfig['cal_day_start'];
        $old_cal_day_end                = $w2Pconfig['cal_day_end'];
        $w2Pconfig['cal_working_days']  = '1,2,3,4,5';
        $w2Pconfig['cal_day_start']     = 9;
        $w2Pconfig['cal_day_end']       = 17;

        $date = new CDate('2010-08-07 00:00:00');
        $date->prev_working_day(true);

        $this->assertEquals('2010-08-06 00:00:00', $date->getDate(DATE_FORMAT_ISO));

        // Restore old working days, day start and end
        $w2Pconfig['cal_working_days']  = $old_working_days;
        $w2Pconfig['cal_day_start']     = $old_cal_day_start;
        $w2Pconfig['cal_day_end']       = $old_cal_day_end;
    }

    /**
     * Tests prev_working_day when before start of day and not preserving hours
     */
    public function testPrevWorkingDayBeforeStartOfDayNoPreserveHours()
    {
        global $w2Pconfig;

        // Save old working days, day start and end
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $old_cal_day_start              = $w2Pconfig['cal_day_start'];
        $old_cal_day_end                = $w2Pconfig['cal_day_end'];
        $w2Pconfig['cal_working_days']  = '1,2,3,4,5';
        $w2Pconfig['cal_day_start']     = 9;
        $w2Pconfig['cal_day_end']       = 17;

        $date = new CDate('2010-08-07 00:00:00');
        $date->prev_working_day();

        $this->assertEquals('2010-08-06 17:00:00', $date->getDate(DATE_FORMAT_ISO));

        // Restore old working days, day start and end
        $w2Pconfig['cal_working_days']  = $old_working_days;
        $w2Pconfig['cal_day_start']     = $old_cal_day_start;
        $w2Pconfig['cal_day_end']       = $old_cal_day_end;
    }

    /**
     * Tests prev_working_day when before start of day and preserving hours
     */
    public function testPrevWorkingDayBeforeStartOfDayPreserveHours()
    {
        global $w2Pconfig;

        // Save old working days, day start and end
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $old_cal_day_start              = $w2Pconfig['cal_day_start'];
        $old_cal_day_end                = $w2Pconfig['cal_day_end'];
        $w2Pconfig['cal_working_days']  = '1,2,3,4,5';
        $w2Pconfig['cal_day_start']     = 9;
        $w2Pconfig['cal_day_end']       = 17;

        $date = new CDate('2010-08-07 00:00:00');
        $date->prev_working_day(true);

        $this->assertEquals('2010-08-06 00:00:00', $date->getDate(DATE_FORMAT_ISO));

        // Restore old working days, day start and end
        $w2Pconfig['cal_working_days']  = $old_working_days;
        $w2Pconfig['cal_day_start']     = $old_cal_day_start;
        $w2Pconfig['cal_day_end']       = $old_cal_day_end;
    }

    /**
     * Tests prev_working_day when it is the start of day and not preserving hours
     */
    public function testPrevWorkingDayStartOfDayNoPreserveHours()
    {
        global $w2Pconfig;

        // Save old working days, day start and end
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $old_cal_day_start              = $w2Pconfig['cal_day_start'];
        $old_cal_day_end                = $w2Pconfig['cal_day_end'];
        $w2Pconfig['cal_working_days']  = '1,2,3,4,5';
        $w2Pconfig['cal_day_start']     = 9;
        $w2Pconfig['cal_day_end']       = 17;

        $date = new CDate('2010-08-07 09:00:00');
        $date->prev_working_day();

        $this->assertEquals('2010-08-06 17:00:00', $date->getDate(DATE_FORMAT_ISO));

        // Restore old working days, day start and end
        $w2Pconfig['cal_working_days']  = $old_working_days;
        $w2Pconfig['cal_day_start']     = $old_cal_day_start;
        $w2Pconfig['cal_day_end']       = $old_cal_day_end;
    }

    /**
     * Tests prev_working_day when it is the start of day and preserving hours
     */
    public function testPrevWorkingDayStartOfDayPreserveHours()
    {
        global $w2Pconfig;

        // Save old working days, day start and end
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $old_cal_day_start              = $w2Pconfig['cal_day_start'];
        $old_cal_day_end                = $w2Pconfig['cal_day_end'];
        $w2Pconfig['cal_working_days']  = '1,2,3,4,5';
        $w2Pconfig['cal_day_start']     = 9;
        $w2Pconfig['cal_day_end']       = 17;

        $date = new CDate('2010-08-07 09:00:00');
        $date->prev_working_day(true);

        $this->assertEquals('2010-08-06 09:00:00', $date->getDate(DATE_FORMAT_ISO));

        // Restore old working days, day start and end
        $w2Pconfig['cal_working_days']  = $old_working_days;
        $w2Pconfig['cal_day_start']     = $old_cal_day_start;
        $w2Pconfig['cal_day_end']       = $old_cal_day_end;
    }

    /**
     * Tests prev_working_day when it is a working day
     */
    public function testPrevWorkingDayIsWorkingDay()
    {
        global $w2Pconfig;

        // Save old working days, day start and end
        $old_working_days               = $w2Pconfig['cal_working_days'];
        $old_cal_day_start              = $w2Pconfig['cal_day_start'];
        $old_cal_day_end                = $w2Pconfig['cal_day_end'];
        $w2Pconfig['cal_working_days']  = '1,2,3,4,5';
        $w2Pconfig['cal_day_start']     = 9;
        $w2Pconfig['cal_day_end']       = 17;

        $date = new CDate('2010-08-24 13:00:00');
        $date->prev_working_day();

        $this->assertEquals('2010-08-24 13:00:00', $date->getDate(DATE_FORMAT_ISO));

        // Restore old working days, day start and end
        $w2Pconfig['cal_working_days']  = $old_working_days;
        $w2Pconfig['cal_day_start']     = $old_cal_day_start;
        $w2Pconfig['cal_day_end']       = $old_cal_day_end;
    }

    /**
     * Tests converting between timezones
     */
	public function testConvertTZ()
	{
		$myDate1 = new CDate('', 'US/Eastern');
		$this->assertEquals($myDate1, new CDate('', 'US/Eastern'));

		$myDate2 = new CDate('', 'CST');
		$myDate2->convertTZ('EST');

		//This tweaks the test data in case the +1 is across the day change.
		$tmpHour = ($myDate1->hour+1 >=24) ? $myDate1->hour+1-24 : $myDate1->hour+1;
		$this->assertEquals($tmpHour, $myDate2->hour);
		$this->assertEquals($myDate1->minute, $myDate2->minute);

		$myDate2->convertTZ('PST');
		$tmpHour = ($myDate1->hour-2 < 0) ? $myDate1->hour-2+24 : $myDate1->hour-2;
		$this->assertEquals($tmpHour, $myDate2->hour);
	}
}
