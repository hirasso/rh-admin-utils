<?php

namespace RH\AdminUtils\Tests\Pest;

use RH\AdminUtils\ACFCodeField;
use RH\AdminUtils\ACFRelationshipField;
use RH\AdminUtils\ACFTextField;

class ACFPrepareFieldTest extends IntegrationTestCase
{
    public function test_falsy_fields_pass_through(): void
    {
        remove_all_filters('acf/prepare_field');
        ACFCodeField::init();
        ACFRelationshipField::init();
        ACFTextField::init();

        foreach ([false, null, 0, ''] as $falsy) {
            $this->assertSame($falsy, apply_filters('acf/prepare_field', $falsy));
        }
    }
}
