<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by hirasso on 22-December-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace RH\AdminUtils\Symfony\Component\VarDumper\Caster;

use RH\AdminUtils\Symfony\Component\VarDumper\Cloner\Stub;

/**
 * Represents an enumeration of values.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class EnumStub extends Stub
{
    public bool $dumpKeys = true;

    public function __construct(array $values, bool $dumpKeys = true)
    {
        $this->value = $values;
        $this->dumpKeys = $dumpKeys;
    }
}
