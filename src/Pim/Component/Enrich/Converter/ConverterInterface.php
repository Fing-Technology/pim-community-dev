<?php

namespace Pim\Component\Enrich\Converter;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConverterInterface
{
    /**
     * Convert data
     *
     * @param array $data
     *
     * @return array
     */
    public function convert(array $data);
}
