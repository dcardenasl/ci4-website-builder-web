<?php

declare(strict_types=1);

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Tests\Support\Libraries\DeterministicDomainAdapter;

abstract class HermeticFeatureTestCase extends CIUnitTestCase
{
    protected DeterministicDomainAdapter $domainAdapter;

    protected function setUp(): void
    {
        parent::setUp();

        Services::reset(true);
        $this->domainAdapter = new DeterministicDomainAdapter();
        Services::injectMock('webApiClient', $this->domainAdapter);
    }

    protected function tearDown(): void
    {
        Services::reset(true);

        parent::tearDown();
    }
}
