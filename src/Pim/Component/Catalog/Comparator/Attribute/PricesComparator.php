<?php

namespace Pim\Component\Catalog\Comparator\Attribute;

use Pim\Component\Catalog\Comparator\ComparatorInterface;

/**
 * Comparator which calculate change set for prices
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricesComparator implements ComparatorInterface
{
    /** @var array */
    protected $types;

    /**
     * @param array $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return in_array($type, $this->types);
    }

    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals)
    {
        $default = ['locale' => null, 'scope' => null, 'data' => []];
        $originals = array_merge($default, $originals);

        $originalPrices = [];
        foreach ($originals['data'] as $price) {
            if (null !== $price['data']) {
                $originalPrices[$price['currency']] = $price['data'];
                if (is_numeric($price['data'])) {
                    $originalPrices[$price['currency']] = number_format($price['data'], 4);
                }
            }
        }

        $dataPrices = [];
        foreach ($data['data'] as $price) {
            if (null !== $price['data']) {
                $dataPrices[$price['currency']] = $price['data'];
                if (is_numeric($price['data'])) {
                    $dataPrices[$price['currency']] = number_format($price['data'], 4);
                }
            }
        }

        if ($dataPrices !== $originalPrices) {
            return $data;
        }

        return null;
    }
}
