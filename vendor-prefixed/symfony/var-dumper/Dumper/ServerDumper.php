<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by hirasso on 25-December-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace RH\AdminUtils\Symfony\Component\VarDumper\Dumper;

use RH\AdminUtils\Symfony\Component\VarDumper\Cloner\Data;
use RH\AdminUtils\Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;
use RH\AdminUtils\Symfony\Component\VarDumper\Server\Connection;

/**
 * ServerDumper forwards serialized Data clones to a server.
 *
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
class ServerDumper implements DataDumperInterface
{
    private Connection $connection;
    private ?DataDumperInterface $wrappedDumper;

    /**
     * @param string                     $host             The server host
     * @param DataDumperInterface|null   $wrappedDumper    A wrapped instance used whenever we failed contacting the server
     * @param ContextProviderInterface[] $contextProviders Context providers indexed by context name
     */
    public function __construct(string $host, ?DataDumperInterface $wrappedDumper = null, array $contextProviders = [])
    {
        $this->connection = new Connection($host, $contextProviders);
        $this->wrappedDumper = $wrappedDumper;
    }

    public function getContextProviders(): array
    {
        return $this->connection->getContextProviders();
    }

    public function dump(Data $data): ?string
    {
        if (!$this->connection->write($data) && $this->wrappedDumper) {
            return $this->wrappedDumper->dump($data);
        }

        return null;
    }
}
