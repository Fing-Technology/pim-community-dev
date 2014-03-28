<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;

/**
 * Transform sub-part or product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupsTransformer
{
    /**
     * @param array  $result
     * @param string $locale
     * @param string $currentGroupId
     *
     * @return array
     */
    public function transform(array $result, $locale, $currentGroupId)
    {
        $normalizedData = $result[ProductQueryUtility::NORMALIZED_FIELD];

        if ($currentGroupId && isset($result['groups'])) {
            $result['in_group']= in_array($currentGroupId, $result['groups']);
        }

        return $result;
    }
}
