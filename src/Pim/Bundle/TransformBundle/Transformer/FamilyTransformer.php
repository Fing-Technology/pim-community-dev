<?php

namespace Pim\Bundle\TransformBundle\Transformer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Builder\FamilyBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\Guesser\GuesserInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Family transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyTransformer extends NestedEntityTransformer
{
    /** @var FamilyBuilderInterface */
    protected $familyBuilder;

    /** @var string */
    protected $requirementClass;

    /**
     * Constructor
     *
     * @param RegistryInterface              $doctrine
     * @param PropertyAccessorInterface      $propertyAccessor
     * @param GuesserInterface               $guesser
     * @param ColumnInfoTransformerInterface $colInfoTransformer
     * @param EntityTransformerInterface     $transformerRegistry
     * @param FamilyBuilderInterface         $familyBuilder
     * @param string                         $requirementClass
     */
    public function __construct(
        ManagerRegistry $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $colInfoTransformer,
        EntityTransformerInterface $transformerRegistry,
        FamilyBuilderInterface $familyBuilder,
        $requirementClass
    ) {
        parent::__construct($doctrine, $propertyAccessor, $guesser, $colInfoTransformer, $transformerRegistry);
        $this->familyBuilder = $familyBuilder;
        $this->requirementClass = $requirementClass;
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($class, array $data)
    {
        return $this->familyBuilder->createFamily();
    }

    /**
     * {@inheritdoc}
     */
    protected function setProperties($class, $entity, array $data)
    {
        if (isset($data['requirements'])) {
            $requirementsData = $data['requirements'];
            unset($data['requirements']);
        }

        parent::setProperties($class, $entity, $data);

        if (isset($requirementsData)) {
            $this->setRequirements($class, $entity, $requirementsData);
        }
    }

    /**
     * Sets the requirements
     *
     * @param string          $class
     * @param FamilyInterface $family
     * @param array           $requirementsData
     */
    protected function setRequirements($class, FamilyInterface $family, array $requirementsData)
    {
        foreach ($requirementsData as $channelCode => $attributeCodes) {
            $this->setChannelRequirements($class, $family, $channelCode, $attributeCodes);
        }
    }

    /**
     * Sets the requirements for a channel
     *
     * @param string          $class
     * @param FamilyInterface $family
     * @param string          $channelCode
     * @param array           $attributeCodes
     */
    protected function setChannelRequirements($class, FamilyInterface $family, $channelCode, $attributeCodes)
    {
        foreach ($attributeCodes as $attributeCode) {
            $data = array(
                'attribute' => $attributeCode,
                'channel'   => $channelCode,
                'required'  => true
            );
            $requirement = $this->transformNestedEntity($class, 'requirements', $this->requirementClass, $data);

            if ($requirement->getAttribute() === null) {
                throw new \Exception(
                    sprintf(
                        'The attribute "%s" used as requirement in family "%s" is not known',
                        $attributeCode,
                        $family->getCode()
                    )
                );
            }

            $family->addAttributeRequirement($requirement);
        }
    }
}
