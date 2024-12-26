<?php

declare (strict_types=1);
namespace RH\AdminUtils\Snicco\Component\BetterWPCLI\CommandLoader;

use RH\AdminUtils\Snicco\Component\BetterWPCLI\Command;
use RH\AdminUtils\Snicco\Component\BetterWPCLI\Exception\CommandNotFound;
/**
 * @template T as Command
 */
interface CommandLoader
{
    /**
     * @param class-string<T> $command_class
     *
     * @throws CommandNotFound
     *
     * @return T
     */
    public function get(string $command_class): Command;
    /**
     * @return class-string<T>[]
     */
    public function commands(): array;
}
