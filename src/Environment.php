<?php

namespace RH\AdminUtils;

final readonly class Environment
{
    public function __construct(
        public string $name,
        public string $origin
    ) {

    }
}
