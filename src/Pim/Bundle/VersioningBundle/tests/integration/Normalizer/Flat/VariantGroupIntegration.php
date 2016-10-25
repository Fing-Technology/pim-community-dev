<?php

namespace tests\integration\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Test\Integration\TestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupIntegration extends TestCase
{
    public function testVariantGroup()
    {
        $standardVariantGroup = [
            'code'   => 'variantA',
            'type'   => 'VARIANT',
            'axis'   => ['a_simple_select'],
            'values' => [
                'a_text' => [
                    ['locale' => null, 'scope' => null, 'data' => 'A name']
                ]
            ],
            'labels' => []
        ];

        $variantGroup = $this->get('pim_catalog.factory.group')->create();
        $this->get('pim_catalog.updater.variant_group')->update($variantGroup, $standardVariantGroup);

        $flatVariantGroup = $this->get('pim_versioning.serializer')->normalize($variantGroup, 'flat');

        $this->assertSame($flatVariantGroup, [
            'code'   => 'variantA',
            'type'   => 'VARIANT',
            'axes'   => 'a_simple_select',
            'a_text' => 'A name'
        ]);
    }
}
