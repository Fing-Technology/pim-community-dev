<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Updates a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateProductCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $updatesExample = [
            [
                'type' => 'set_data',
                'field' => 'name',
                'data' => 'My name'
            ],
            [
                'type'        => 'copy_data',
                'from_field'  => 'description',
                'from_scope'  => 'ecommerce',
                'from_locale' => 'en_US',
                'to_field'    => 'description',
                'to_scope'    => 'mobile',
                'to_locale'   => 'en_US'
            ],
            [
                'type'  => 'add_data',
                'field' => 'categories',
                'data' => ['tshirt']
            ],
        ];

        $this
            ->setName('pim:product:update')
            ->setDescription('Update a product')
            ->addArgument(
                'identifier',
                InputArgument::REQUIRED,
                'The product identifier (sku by default)'
            )
            ->addArgument(
                'json_updates',
                InputArgument::REQUIRED,
                sprintf("The product updates in json, for instance, '%s'", json_encode($updatesExample))
            )
            ->addArgument(
                'username',
                InputArgument::OPTIONAL,
                sprintf('The author of updated product')
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifier = $input->getArgument('identifier');
        $product = $this->getProduct($identifier);
        if (false === $product) {
            $output->writeln(sprintf('<error>product with identifier "%s" not found<error>', $identifier));

            return;
        }

        if ($input->hasArgument('username') && '' != $username = $input->getArgument('username')) {
            if (!$this->createToken($output, $username) || !$this->isGranted($output, $username, $identifier)) {
                return;
            }
        }

        $updates = json_decode($input->getArgument('json_updates'), true);
        $this->update($product, $updates);

        $violations = $this->validate($product);
        foreach ($violations as $violation) {
            $output->writeln(sprintf("<error>%s<error>", $violation->getMessage()));
        }
        if (0 !== $violations->count()) {
            $output->writeln(sprintf('<error>product "%s" is not valid<error>', $identifier));

            return;
        }

        $this->save($product);
        $this->removeToken();
        $output->writeln(sprintf('<info>product "%s" has been updated<info>', $identifier));
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface
     */
    protected function getProduct($identifier)
    {
        $repository = $this->getContainer()->get('pim_catalog.repository.product');
        $product    = $repository->findOneByIdentifier($identifier);

        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param array            $updates
     *
     * @return bool
     */
    protected function update(ProductInterface $product, array $updates)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['type']);
        $resolver->setAllowedValues(['type' => ['set_data', 'copy_data', 'add_data', 'remove_data']]);
        $resolver->setOptional(
            [
                'field',
                'data',
                'locale',
                'scope',
                'from_field',
                'to_field',
                'from_locale',
                'to_locale',
                'from_scope',
                'to_scope'
            ]
        );
        $resolver->setDefaults(
            [
                'locale'      => null,
                'scope'       => null,
                'from_locale' => null,
                'to_locale'   => null,
                'from_scope'  => null,
                'to_scope'    => null
            ]
        );

        foreach ($updates as $update) {
            $update = $resolver->resolve($update);

            switch ($update['type']) {
                case 'set_data':
                    $this->applySetData($product, $update);
                    break;
                case 'copy_data':
                    $this->applyCopyData($product, $update);
                    break;
                case 'add_data':
                    $this->applyAddData($product, $update);
                    break;
                case 'remove_data':
                    $this->applyRemoveData($product, $update);
                    break;
            }
        }
    }

    /**
     * @param ProductInterface $product
     * @param array            $update
     */
    protected function applySetData(ProductInterface $product, array $update)
    {
        $updater = $this->getUpdater();
        $updater->setData(
            $product,
            $update['field'],
            $update['data'],
            ['locale' => $update['locale'], 'scope' => $update['scope']]
        );
    }

    /**
     * @param ProductInterface $product
     * @param array            $update
     */
    protected function applyCopyData(ProductInterface $product, array $update)
    {
        $updater = $this->getUpdater();
        $updater->copyData(
            $product,
            $product,
            $update['from_field'],
            $update['to_field'],
            [
                'from_locale' => $update['from_locale'],
                'to_locale' => $update['to_locale'],
                'from_scope' => $update['from_scope'],
                'to_scope' => $update['to_scope']
            ]
        );
    }

    /**
     * @param ProductInterface $product
     * @param array            $update
     */
    protected function applyAddData(ProductInterface $product, array $update)
    {
        $updater = $this->getUpdater();
        $updater->addData(
            $product,
            $update['field'],
            $update['data'],
            ['locale' => $update['locale'], 'scope' => $update['scope']]
        );
    }

    /**
     * @param ProductInterface $product
     * @param array            $update
     */
    protected function applyRemoveData(ProductInterface $product, array $update)
    {
        $updater = $this->getUpdater();
        $updater->removeData(
            $product,
            $update['field'],
            $update['data'],
            ['locale' => $update['locale'], 'scope' => $update['scope']]
        );
    }

    /**
     * @return ProductUpdaterInterface
     */
    protected function getUpdater()
    {
        return $this->getContainer()->get('pim_catalog.updater.product');
    }

    /**
     * @return \Symfony\Component\Security\Core\SecurityContextInterface;
     */
    protected function getSecurityContext()
    {
        return $this->getContainer()->get('security.context');
    }

    /**
     * @return \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    public function getSecurityFacade()
    {
        return $this->getContainer()->get('oro_security.security_facade');
    }

    /**
     * @param ProductInterface $product
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    protected function validate(ProductInterface $product)
    {
        $validator = $this->getContainer()->get('pim_validator');
        $errors = $validator->validate($product);

        return $errors;
    }

    /**
     * @param ProductInterface $product
     */
    protected function save(ProductInterface $product)
    {
        $saver = $this->getContainer()->get('pim_catalog.saver.product');
        $saver->save($product);
    }

    /**
     * Create a security token from the given username
     *
     * @param OutputInterface  $output
     * @param string           $username
     *
     * @param boolean
     */
    protected function createToken(OutputInterface $output, $username)
    {
        $userManager = $this->getContainer()->get('oro_user.manager');
        $user = $userManager->findUserByUsername($username);

        if (null === $user) {
            $output->writeln(sprintf('<error>Username "%s" is unknown<error>', $username));

            return false;
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->getSecurityContext()->setToken($token);

        return true;
    }

    /**
     * Returns true if user is allowed to edit product
     *
     * @param OutputInterface  $output
     * @param string           $username
     * @param string           $identifier
     *
     * @return boolean
     */
    protected function isGranted(OutputInterface $output, $username, $identifier)
    {
        $isGranted = $this->getSecurityFacade()->isGranted('pim_enrich_product_edit_attributes');

        if (!$isGranted) {
            $this->removeToken();

            $output->writeln(sprintf(
                '<error>User "%s" is not allowed to edit product "%s"<error>',
                $username,
                $identifier
            ));
        }

        return $isGranted;
    }

    /**
     * Remove user token
     */
    protected function removeToken()
    {
        $this->getSecurityContext()->setToken(null);
    }
}
