<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Storage\Backend;

use Piwik\Settings\Storage\Backend\SitesTable;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Settings
 * @group Backend
 * @group Storage
 */
class SitesTableTest extends IntegrationTestCase
{
    /**
     * @var SitesTable
     */
    private $backendSite1;

    /**
     * @var SitesTable
     */
    private $backendSite2;

    public function setUp(): void
    {
        parent::setUp();

        $idSite1 = Fixture::createWebsite('2014-01-01 00:01:02');
        $idSite2 = Fixture::createWebsite('2014-01-01 00:01:02');

        $this->backendSite1 = $this->createSettings($idSite1);
        $this->backendSite2 = $this->createSettings($idSite2);
    }

    private function createSettings($idSite)
    {
        return new SitesTable($idSite);
    }

    public function testConstructShouldThrowAnExceptionIfPluginNameIsEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No idSite given');

        $this->createSettings(0);
    }

    public function testLoadShouldHaveValuesByDefaultForExistingSites()
    {
        $this->assertFieldsLoaded(array('idsite' => '1'), $this->backendSite1);
        $this->assertFieldsLoaded(array('idsite' => '2'), $this->backendSite2);
    }

    public function testLoadShouldThrowExceptionIfSiteDoesNotExist()
    {
        $this->expectException(\Piwik\Exception\UnexpectedWebsiteFoundException::class);

        $this->createSettings($idSite = 999)->load();
    }

    public function testGetStorageIdShouldIncludePluginNameAndLogin()
    {
        $this->assertSame('SitesTable_1', $this->backendSite1->getStorageId());
        $this->assertSame('SitesTable_2', $this->backendSite2->getStorageId());
    }

    public function testSaveShouldOnlySaveForSpecificIdSite()
    {
        $value1 = array('ecommerce' => '1', 'sitesearch' => '0');
        $this->backendSite1->save($value1);

        $value2 = array('ecommerce' => '0', 'sitesearch' => '1');
        $this->backendSite2->save($value2);

        $this->assertFieldsLoaded($value1, $this->backendSite1);
        $this->assertFieldsLoaded($value2, $this->backendSite2);
    }

    public function testDeleteShouldNotDeleteAnything()
    {
        $value = $this->saveValueForAllBackends();

        $this->backendSite1->delete();
        $this->backendSite2->delete();

        $this->assertFieldsLoaded($value, $this->backendSite1);
        $this->assertFieldsLoaded($value, $this->backendSite2);
    }

    public function testSaveDuplicateValuesShouldBeOverwritten()
    {
        $value = array('ecommerce' => '0', 'sitesearch' => '1');

        $this->backendSite1->save($value);
        $this->assertFieldsLoaded($value, $this->backendSite1);

        $value = array('ecommerce' => '1', 'sitesearch' => '0');
        $this->backendSite1->save($value);
        $this->assertFieldsLoaded($value, $this->backendSite1);
    }

    public function testSaveShouldNotRemoveAnyExistingUrlsWhenNoUrlsGiven()
    {
        $value = array('ecommerce' => '0', 'sitesearch' => '1');

        $this->backendSite1->save($value);

        $value = array('urls' => array('http://piwik.net'));
        $this->assertFieldsLoaded($value, $this->backendSite1);
    }

    public function testSaveShouldBeAbleToHandleBooleanValues()
    {
        $value = array('ecommerce' => true, 'sitesearch' => false);
        $this->backendSite1->save($value);

        $value = array('ecommerce' => '1', 'sitesearch' => '0');
        $this->assertFieldsLoaded($value, $this->backendSite1);
    }

    public function testSaveNotSetValuesShouldRemain()
    {
        $value = array('ecommerce' => '0', 'sitesearch' => '1');
        $this->backendSite1->save($value);
        $this->assertFieldsLoaded($value, $this->backendSite1);

        // make sure name is still set
        $this->assertFieldsLoaded(array('name' => 'Piwik test'), $this->backendSite1);
    }

    public function testSaveLoadShouldBeAbleToSaveAndLoadArrayValues()
    {
        $value1 = array(
            'sitesearch_keyword_parameters' => array('val', 'val7', 'val5'),
            'excluded_parameters' => array('val4', 'val17', 'val45'),
        );

        $this->backendSite1->save($value1);
        $this->assertFieldsLoaded($value1, $this->backendSite1);
    }

    public function testSaveSaveMainUrlAndUrlsCorrectlyManyUrls()
    {
        $urls = array('piwik.org', 'demo.piwik.org', 'test.piwik.org');
        $value = array('urls' => $urls);
        $this->backendSite1->save($value);

        $value = array('main_url' => 'piwik.org', 'urls' => $urls);
        $this->assertFieldsLoaded($value, $this->backendSite1);
    }

    public function testSaveSaveMainUrlAndUrlsCorrectlyOnlyOneUrlGiven()
    {
        $urls = array('piwik.org');
        $value = array('urls' => $urls);
        $this->backendSite1->save($value);

        $value = array('main_url' => 'piwik.org', 'urls' => $urls);
        $this->assertFieldsLoaded($value, $this->backendSite1);
    }

    private function assertFieldsLoaded($expectedValues, SitesTable $backend)
    {
        $loaded = $backend->load();
        foreach ($expectedValues as $key => $value) {
            $this->assertEquals($loaded[$key], $value);
        }
    }

    private function saveValueForAllBackends()
    {
        $value = array('ecommerce' => '1', 'sitesearch' => '0');

        foreach (array($this->backendSite1, $this->backendSite2) as $backend) {
            $backend->save($value);
            $this->assertFieldsLoaded($value, $backend);
        }

        return $value;
    }
}
