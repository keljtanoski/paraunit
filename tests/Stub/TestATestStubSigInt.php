<?php

declare(strict_types=1);

namespace Tests\Stub;

class TestATestStubSigInt extends BrokenTestBase implements BrokenTestInterface
{
    public function testBrokenTest(): void
    {
        $chunkFileName = __DIR__ . DIRECTORY_SEPARATOR . 'phpunit_for_sigint_stubs_0.xml';

        $this->assertFileExists($chunkFileName);

        posix_kill(posix_getppid(), SIGINT);

        // give parent process 5s to react
        for ($i = 1; $i <= 100; ++$i) {
            if (! file_exists($chunkFileName)) {
                break;
            }
            usleep(50000);
        }

        $this->assertFileDoesNotExist($chunkFileName);
    }
}
