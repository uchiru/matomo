<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Category;

use Piwik\Category\CategoryList;
use Piwik\Category\Category;

/**
 * @group Category
 * @group CategoryList
 * @group CategoryListTest
 */
class CategoryListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CategoryList
     */
    private $categoryList;

    public function setUp(): void
    {
        $this->categoryList = new CategoryList();
    }

    public function testGetCategoriesIsEmptyByDefault()
    {
        $this->assertSame(array(), $this->categoryList->getCategories());
    }

    public function testAddCategoryShouldAddCategoryAndGetCategoriesShouldBeIndexedById()
    {
        $category = $this->addCategory('myTest');

        $this->assertSame(array('myTest' => $category), $this->categoryList->getCategories());
    }

    public function testAddCategoryShouldThrowExceptionIfAddingSameCategoryIdTwice()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Category myTest already exists');

        $this->addCategory('myTest');
        $this->addCategory('myTest');
    }

    public function testHasCategoryShouldDetectIfCategoryExists()
    {
        $this->assertFalse($this->categoryList->hasCategory('myTest'));

        $this->addCategory('myTest');

        $this->assertTrue($this->categoryList->hasCategory('myTest'));

        $this->assertFalse($this->categoryList->hasCategory('myTest2'));
        $this->assertFalse($this->categoryList->hasCategory('General_Visits'));
    }

    public function testGetCategoryShouldReturnExistingCategoryIfPossible()
    {
        $this->assertNull($this->categoryList->getCategory('myTest'));

        $category = $this->addCategory('myTest');

        $this->assertSame($category, $this->categoryList->getCategory('myTest'));

        $this->assertNull($this->categoryList->getCategory('myTest2'));
        $this->assertNull($this->categoryList->getCategory('General_Visits'));
    }

    private function addCategory($id)
    {
        $category = $this->createCategory($id);
        $this->categoryList->addCategory($category);

        return $category;
    }

    private function createCategory($categoryId)
    {
        $config = new Category();
        $config->setId($categoryId);

        return $config;
    }
}
