<?php

namespace Pim\Bundle\VersioningBundle\tests\integration\Normalizer\Flat;

use Test\Integration\TestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIntegration extends TestCase
{
    public function testProduct()
    {
        $standardProduct = [
            'identifier'    => 'foo',
            'family'        => 'familyA',
            'groups'        => ['groupA', 'groupB'],
            'variant_group' => 'variantA',
            'categories'    => ['categoryA1', 'categoryB'],
            'enabled'       => true,
            'values'        => [
                'sku'                                => [
                    ['locale' => null, 'scope' => null, 'data' => 'foo'],
                ],
//                'a_file'                             => [
//                    [
//                        'locale' => null,
//                        'scope'  => null,
//                        'data'   => '4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_fileA.txt',
//                    ],
//                ],
//                'an_image'                           => [
//                    [
//                        'locale' => null,
//                        'scope'  => null,
//                        'data'   => '1/5/7/5/15757827125efa686c1c0f1e7930ca0c528f1c2c_imageA.jpg',
//                    ],
//                ],
                'a_date'                             => [
                    ['locale' => null, 'scope' => null, 'data' => '2016-06-13'],
                ],
                'a_metric'                           => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['data' => '987654321987.1234', 'unit' => 'KILOWATT'],
                    ],
                ],
                'a_metric_without_decimal' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['data' => 98, 'unit' => 'CENTIMETER'],
                    ],
                ],
                'a_metric_without_decimal_negative' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['data' => -20, 'unit' => 'CELSIUS'],
                    ],
                ],
                'a_metric_negative'        => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => ['data' => '-20.5000', 'unit' => 'CELSIUS'],
                    ],
                ],
                'a_multi_select'                     => [
                    ['locale' => null, 'scope' => null, 'data' => ['optionA', 'optionB']],
                ],
                'a_number_float'                     => [
                    ['locale' => null, 'scope' => null, 'data' => '12.5678'],
                ],
                'a_number_float_negative'            => [
                    ['locale' => null, 'scope' => null, 'data' => '-99.8732'],
                ],
                'a_number_integer'                   => [
                    ['locale' => null, 'scope' => null, 'data' => 42]
                ],
                'a_number_integer_negative' => [
                    ['locale' => null, 'scope' => null, 'data' => -42]
                ],
                'a_price'                            => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            ['data' => '45.00', 'currency' => 'USD'],
                            ['data' => '56.53', 'currency' => 'EUR']
                        ],
                    ],
                ],
                'a_price_without_decimal'            => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            ['data' => -45, 'currency' => 'USD'],
                            ['data' => 56, 'currency' => 'EUR']
                        ],
                    ],
                ],
                'a_simple_select'                    => [
                    ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                ],
                'a_text'                             => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'this is a text',
                    ],
                ],
                'a_text_area'                        => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => 'this is a very very very very very long  text',
                    ],
                ],
                'a_yes_no'                           => [
                    ['locale' => null, 'scope' => null, 'data' => true],
                ],
//                'a_localizable_image'                => [
//                    [
//                        'locale' => 'en_US',
//                        'scope'  => null,
//                        'data'   => '6/2/e/3/62e376e75300d27bfec78878db4d30ff1490bc53_imageB_en_US.jpg',
//                    ],
//                    [
//                        'locale' => 'fr_FR',
//                        'scope'  => null,
//                        'data'   => '0/f/5/0/0f5058de76f68446bb6b2371f19cd2234b245c00_imageB_fr_FR.jpg',
//                    ],
//                ],
                'a_scopable_price'                   => [
                    [
                        'locale' => null,
                        'scope'  => 'ecommerce',
                        'data'   => [
                            ['data' => '15.00', 'currency' => 'EUR'],
                            ['data' => '20.00', 'currency' => 'USD'],
                        ],
                    ],
                    [
                        'locale' => null,
                        'scope'  => 'tablet',
                        'data'   => [
                            ['data' => '17.00', 'currency' => 'EUR'],
                            ['data' => '24.00', 'currency' => 'USD'],
                        ],
                    ],
                ],
                'a_localized_and_scopable_text_area' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => 'a text area for ecommerce in English',
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => 'tablet',
                        'data'   => 'a text area for tablets in English'
                    ],
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'tablet',
                        'data'   => 'une zone de texte pour les tablettes en français',
                    ],
                ],
            ],
            'created'       => '2016-06-14T13:12:50+02:00',
            'updated'       => '2016-06-14T13:12:50+02:00',
            'associations'  => [
                'PACK'   => ['groups' => [], 'products' => ['bar', 'baz']],
                'UPSELL' => ['groups' => ['groupA'], 'products' => []],
                'X_SELL' => ['groups' => ['groupB'], 'products' => ['bar']],
            ],
        ];

        $saver = $this->get('pim_catalog.saver.product');
        $saver->save($this->get('pim_catalog.builder.product')->createProduct('bar'));
        $saver->save($this->get('pim_catalog.builder.product')->createProduct('baz'));

        $product = $this->get('pim_catalog.builder.product')->createProduct('foo');
        $this->get('pim_catalog.updater.product')->update($product, $standardProduct);

        $flatProduct = $this->get('pim_versioning.serializer')->normalize($product, 'flat');

        $this->assertSame($flatProduct, [
            'family'                                             => 'familyA',
            'groups'                                             => 'groupA,groupB',
            'variant_group'                                      => 'variantA',
            'categories'                                         => 'categoryA1,categoryB',
            'enabled'                                            => true,
            'PACK-groups'                                        => '',
            'PACK-products'                                      => 'bar,baz',
            'SUBSTITUTION-groups'                                => '',
            'SUBSTITUTION-products'                              => '',
            'UPSELL-groups'                                      => 'groupA',
            'UPSELL-products'                                    => '',
            'X_SELL-groups'                                      => 'groupB',
            'X_SELL-products'                                    => 'bar',
//            'a_file'                                             => '4/d/e/b/4deb535f0979dea59cf34661e22336459a56bed3_fileA.txt',
//            'an_image'                                           => '1/5/7/5/15757827125efa686c1c0f1e7930ca0c528f1c2c_imageA.jpg',
            'a_date'                                             => '2016-06-13',
            'a_localized_and_scopable_text_area-en_US-ecommerce' => 'a text area for ecommerce in English',
            'a_localized_and_scopable_text_area-en_US-tablet'    => 'a text area for tablets in English',
            'a_localized_and_scopable_text_area-fr_FR-tablet'    => 'une zone de texte pour les tablettes en français',
            'a_metric'                                           => '987654321987.1234',
            'a_metric-unit'                                      => 'KILOWATT',
            'a_metric_negative'                                  => '-20.5000',
            'a_metric_negative-unit'                             => 'CELSIUS',
            'a_metric_without_decimal'                           => 98,
            'a_metric_without_decimal-unit'                      => 'CENTIMETER',
            'a_metric_without_decimal_negative'                  => -20,
            'a_metric_without_decimal_negative-unit'             => 'CELSIUS',
            'a_multi_select'                                     => 'optionA,optionB',
            'a_number_float'                                     => '12.5678',
            'a_number_float_negative'                            => '-99.8732',
            'a_number_integer'                                   => 42,
            'a_number_integer_negative'                          => -42,
            'a_price-EUR'                                        => '56.53',
            'a_price-USD'                                        => '45.00',
            'a_price_without_decimal-EUR'                        => 56,
            'a_price_without_decimal-USD'                        => -45,
            'a_scopable_price-EUR-ecommerce'                     => '15.00',
            'a_scopable_price-EUR-tablet'                        => '17.00',
            'a_scopable_price-USD-ecommerce'                     => '20.00',
            'a_scopable_price-USD-tablet'                        => '24.00',
            'a_simple_select'                                    => 'optionB',
            'a_text'                                             => 'A name',
            'a_text_area'                                        => 'this is a very very very very very long  text',
            'a_yes_no'                                           => true,
            ////            'a_localizable_image-en_US'                          => '6/2/e/3/62e376e75300d27bfec78878db4d30ff1490bc53_imageB_en_US.jpg',
            ////            'a_localizable_image-fr_FR'                          => '0/f/5/0/0f5058de76f68446bb6b2371f19cd2234b245c00_imageB_fr_FR.jpg',
            'sku'                                                => 'foo',
        ]);
    }
}
