<?php

declare(strict_types=1);

namespace Tests\Unit\Filter;

use Paraunit\Configuration\PHPUnitConfig;
use Paraunit\Filter\Filter;
use PHPUnit\Util\Xml\Loader;
use SebastianBergmann\FileIterator\Facade;
use Tests\BaseUnitTestCase;

class FilterTest extends BaseUnitTestCase
{
    /** @var string|null */
    private $absoluteConfigBaseDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->absoluteConfigBaseDir = $this->absoluteConfigBaseDir ?? \dirname(__DIR__, 2) . '/Stub/StubbedXMLConfigs' . DIRECTORY_SEPARATOR;
    }

    public function testFilterTestFilesGetsOnlyRequestedTestsuite(): void
    {
        $configFile = $this->absoluteConfigBaseDir . 'stubbed_for_filter_test.xml';
        $configFilePhpUnit = $this->mockPHPUnitConfig($configFile);

        $testSuiteName = 'test_only_requested_testsuite';

        $file1 = $this->absoluteConfigBaseDir . './only/selected/test/suite/OnlyTestSuiteTest.php';
        $file2 = $this->absoluteConfigBaseDir . './other/test/suite/OtherTest.php';

        $fileIterator = $this->prophesize(Facade::class);
        $fileIterator->getFilesAsArray($this->absoluteConfigBaseDir . './only/selected/test/suite/', 'Test.php', '', [])
            ->willReturn([$file1])
            ->shouldBeCalledTimes(1);
        $fileIterator->getFilesAsArray($this->absoluteConfigBaseDir . './other/test/suite/', 'Test.php', '', [])
            ->willReturn([$file2])
            ->shouldNotBeCalled();

        $filter = new Filter($fileIterator->reveal(), $configFilePhpUnit, $testSuiteName);

        $result = $filter->filterTestFiles();

        $this->assertCount(1, $result);
        $this->assertEquals([$file1], $result);
    }

    public function testFilterTestFilesSupportsSuffixAttribute(): void
    {
        $configFile = $this->absoluteConfigBaseDir . 'stubbed_for_suffix_test.xml';
        $configFilePhpUnit = $this->mockPHPUnitConfig($configFile);

        $file1 = $this->absoluteConfigBaseDir . './only/selected/test/suite/OnlyTestSuiteTest.php';
        $file2 = $this->absoluteConfigBaseDir . './other/test/suite/OtherTest.php';

        $fileIterator = $this->prophesize(Facade::class);
        $fileIterator->getFilesAsArray($this->absoluteConfigBaseDir . './only/selected/test/suite/', 'TestSuffix.php', '', [])
            ->willReturn([$file1])
            ->shouldBeCalledTimes(1);
        $fileIterator->getFilesAsArray($this->absoluteConfigBaseDir . './other/test/suite/', 'Test.php', '', [])
            ->willReturn([$file2])
            ->shouldBeCalledTimes(1);

        $filter = new Filter($fileIterator->reveal(), $configFilePhpUnit);

        $result = $filter->filterTestFiles();
        $this->assertEquals([$file1, $file2], $result);
    }

    public function testFilterTestFilesSupportsExcludeNodes(): void
    {
        $configFile = $this->absoluteConfigBaseDir . 'stubbed_for_node_exclude.xml';
        $configFilePhpUnit = $this->mockPHPUnitConfig($configFile);

        $excludeArray1 = [
            '/path/to/exclude1',
            '/path/to/exclude2',
        ];

        $excludeArray2 = [
            '/path/to/exclude3',
            '/path/to/exclude4',
        ];

        $file1 = $this->absoluteConfigBaseDir . './only/selected/test/suite/TestPrefixOneTest.php';
        $file2 = $this->absoluteConfigBaseDir . './other/test/suite/OtherTest.php';

        $fileIterator = $this->prophesize(Facade::class);
        $fileIterator->getFilesAsArray($this->absoluteConfigBaseDir . './only/selected/test/suite/', 'Test.php', 'TestPrefix', $excludeArray1)
            ->willReturn([$file1])
            ->shouldBeCalledTimes(1);
        $fileIterator->getFilesAsArray($this->absoluteConfigBaseDir . './other/test/suite/', 'Test.php', '', $excludeArray2)
            ->willReturn([$file2])
            ->shouldBeCalledTimes(1);

        $filter = new Filter($fileIterator->reveal(), $configFilePhpUnit);

        $result = $filter->filterTestFiles();
        $this->assertEquals([$file1, $file2], $result);
    }

    public function testFilterTestFilesAvoidsDuplicateRuns(): void
    {
        $configFile = $this->absoluteConfigBaseDir . 'stubbed_for_filter_test.xml';
        $configFilePhpUnit = $this->mockPHPUnitConfig($configFile);

        $file = $this->absoluteConfigBaseDir . './only/selected/test/suite/SameFile.php';

        $fileIterator = $this->prophesize(Facade::class);
        $fileIterator->getFilesAsArray($this->absoluteConfigBaseDir . './only/selected/test/suite/', 'Test.php', '', [])
            ->willReturn([$file])
            ->shouldBeCalledTimes(1);
        $fileIterator->getFilesAsArray($this->absoluteConfigBaseDir . './other/test/suite/', 'Test.php', '', [])
            ->willReturn([$file])
            ->shouldBeCalledTimes(1);

        $filter = new Filter($fileIterator->reveal(), $configFilePhpUnit);

        $result = $filter->filterTestFiles();
        $this->assertCount(1, $result);
        $this->assertEquals([$file], $result);
    }

    public function testFilterTestFilesSupportsFileNodes(): void
    {
        $configFile = $this->absoluteConfigBaseDir . 'stubbed_for_node_file.xml';
        $configFilePhpUnit = $this->mockPHPUnitConfig($configFile);

        $file1 = $this->absoluteConfigBaseDir . './only/selected/test/suite/TestPrefixOneTest.php';
        $file2 = $this->absoluteConfigBaseDir . './other/test/suite/OtherTest.php';

        $fileIterator = $this->prophesize(Facade::class);
        $fileIterator->getFilesAsArray($this->absoluteConfigBaseDir . './only/selected/test/suite/', 'Test.php', '', [])
            ->willReturn([$file1])
            ->shouldBeCalledTimes(1);
        $fileIterator->getFilesAsArray($this->absoluteConfigBaseDir . './other/test/suite/', 'Test.php', '', [])
            ->willReturn([$file2])
            ->shouldBeCalledTimes(1);

        $filter = new Filter($fileIterator->reveal(), $configFilePhpUnit);

        $result = $filter->filterTestFiles();
        $this->assertEquals(
            [
                $file1,
                $this->absoluteConfigBaseDir . './this/file.php',
                $this->absoluteConfigBaseDir . './this/file2.php',
                $file2,
            ],
            $result
        );
    }

    public function testFilterTestFilesSupportsCaseInsensitiveStringFiltering(): void
    {
        $configFile = $this->absoluteConfigBaseDir . 'stubbed_for_filter_test.xml';
        $configFilePhpUnit = $this->mockPHPUnitConfig($configFile);

        $file1 = $this->absoluteConfigBaseDir . './only/selected/test/suite/ThisTest.php';
        $file2 = $this->absoluteConfigBaseDir . './only/selected/test/suite/ThisTooTest.php';
        $file3 = $this->absoluteConfigBaseDir . './only/selected/test/suite/NotHereTest.php';
        $file4 = $this->absoluteConfigBaseDir . './other/test/suite/OtherTest.php';

        $fileIterator = $this->prophesize(Facade::class);
        $fileIterator->getFilesAsArray($this->absoluteConfigBaseDir . './only/selected/test/suite/', 'Test.php', '', [])
            ->willReturn([$file1, $file2, $file3])
            ->shouldBeCalledTimes(1);
        $fileIterator->getFilesAsArray($this->absoluteConfigBaseDir . './other/test/suite/', 'Test.php', '', [])
            ->willReturn([$file4])
            ->shouldBeCalledTimes(1);

        $filter = new Filter($fileIterator->reveal(), $configFilePhpUnit, null, 'this');

        $result = $filter->filterTestFiles();

        $this->assertCount(2, $result);
        $this->assertEquals([$file1, $file2], $result);
    }

    /**
     * @throws \Exception
     */
    private function getStubbedXMLConf(string $fileName): \DOMDocument
    {
        $filePath = realpath($fileName);

        if (! $filePath || ! file_exists($filePath)) {
            throw new \RuntimeException('Stub XML config file missing: ' . $fileName);
        }

        return (new Loader())->loadFile($filePath);
    }

    private function mockPHPUnitConfig(string $configFile): PHPUnitConfig
    {
        $this->assertFileExists($configFile, 'Mock not possible, config file to pass does not exist');

        $config = $this->prophesize(PHPUnitConfig::class);
        $config->getFileFullPath()
            ->willReturn($configFile);
        $config->getBaseDirectory()
            ->willReturn(dirname($configFile));

        return $config->reveal();
    }
}
