<?php

namespace Tests\Concerns;

use Laravel\Passport\ClientRepository;
use RuntimeException;

trait CreatesPassportClientsForTests
{
    protected function setUpPassportClients(): void
    {
        $this->createPersonalAccessClientIfMissing();
    }

    private function createPersonalAccessClientIfMissing(): void
    {
        $clientRepository = app(ClientRepository::class);

        try {
            $clientRepository->personalAccessClient();
        } catch (RuntimeException) {
            $clientRepository->createPersonalAccessClient(
                null,
                'Personal Access Client',
                config('app.url')
            );
        }
    }
}
