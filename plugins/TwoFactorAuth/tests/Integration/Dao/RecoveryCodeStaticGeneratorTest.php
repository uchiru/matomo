<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth\tests\Integration\Dao;

use Piwik\Plugins\TwoFactorAuth\Dao\RecoveryCodeStaticGenerator;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group TwoFactorAuth
 * @group RecoveryCodeStaticGenerator
 * @group Plugins
 */
class RecoveryCodeStaticGeneratorTest extends IntegrationTestCase
{
    /**
     * @var RecoveryCodeStaticGenerator
     */
    private $generator;

    public function setUp(): void
    {
        parent::setUp();

        $this->generator = new RecoveryCodeStaticGenerator();
    }

    public function testGeneratorCodeLength()
    {
        $this->assertSame(16, mb_strlen($this->generator->generateCode()));
    }

    public function testGeneratorCodeAlwaysDifferent()
    {
        $this->assertNotEquals($this->generator->generateCode(), $this->generator->generateCode());
    }

    public function testGeneratorCodeIncreases()
    {
        $this->assertSame('1100000000000000', $this->generator->generateCode());
        $this->assertSame('1200000000000000', $this->generator->generateCode());
    }
}
