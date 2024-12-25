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

namespace RH\AdminUtils\Symfony\Component\VarDumper\Caster;

use RH\AdminUtils\Symfony\Component\VarDumper\Cloner\Stub;

/**
 * Represents any arbitrary value.
 *
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 */
class ScalarStub extends Stub
{
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }
}
