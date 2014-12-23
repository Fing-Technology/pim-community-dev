<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater\Copier;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Prophecy\Argument;

class MultiSelectValueCopierSpec extends ObjectBehavior
{
    function let(ProductBuilder $builder, AttributeValidatorHelper $attributeValidatorHelper)
    {
        $this->beConstructedWith($builder, $attributeValidatorHelper, ['pim_catalog_multiselect']);
    }

    function it_is_a_copier()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\Copier\CopierInterface');
    }

    function it_supports_multi_select_attributes(
        AttributeInterface $fromTextAttribute,
        AttributeInterface $fromTextareaAttribute,
        AttributeInterface $fromIdentifierAttribute,
        AttributeInterface $toTextareaAttribute,
        AttributeInterface $fromMultiSelectAttribute,
        AttributeInterface $toMultiSelectAttribute
    ) {
        $fromMultiSelectAttribute->getAttributeType()->willReturn('pim_catalog_multiselect');
        $toMultiSelectAttribute->getAttributeType()->willReturn('pim_catalog_multiselect');
        $this->supports($fromMultiSelectAttribute, $toMultiSelectAttribute)->shouldReturn(true);

        $fromTextareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $toTextareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supports($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $fromIdentifierAttribute->getAttributeType()->willReturn('pim_catalog_identifier');
        $toTextareaAttribute->getAttributeType()->willReturn('pim_catalog_text');
        $this->supports($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $fromMultiSelectAttribute->getAttributeType()->willReturn('pim_catalog_number');
        $toTextareaAttribute->getAttributeType()->willReturn('pim_catalog_textarea');
        $this->supports($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $this->supports($fromTextAttribute, $toMultiSelectAttribute)->shouldReturn(false);
        $this->supports($fromMultiSelectAttribute, $toTextareaAttribute)->shouldReturn(false);
    }

    function it_copies_multi_select_value_to_a_product_value(
        $builder,
        $attributeValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductInterface $product4,
        ProductValue $fromProductValue,
        ProductValue $toProductValue,
        AttributeOption $attributeOption
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');

        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attributeValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attributeValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $fromProductValue->getOptions()->willReturn([$attributeOption])->shouldBeCalled(3);

        $toProductValue->getOptions()->willReturn([$attributeOption]);
        $toProductValue->removeOption($attributeOption)->shouldBeCalled();
        $toProductValue->addOption($attributeOption)->shouldBeCalled();

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product1->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $product2->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $product3->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product3->getValue('toAttributeCode', $toLocale, $toScope)->willReturn(null);

        $product4->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromProductValue);
        $product4->getValue('toAttributeCode', $toLocale, $toScope)->willReturn($toProductValue);

        $builder->addProductValue($product3, $toAttribute, $toLocale, $toScope)->shouldBeCalledTimes(1)->willReturn($toProductValue);

        $products = [$product1, $product2, $product3, $product4];

        $this->copyValue($products, $fromAttribute, $toAttribute, $fromLocale, $toLocale, $fromScope, $toScope);
    }
}
