<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CommentBundle\Builder\CommentBuilder;
use Pim\Bundle\CommentBundle\Repository\CommentRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controller for product comments
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCommentController
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var CommentRepositoryInterface */
    protected $commentRepository;

    /** @var SaverInterface */
    protected $commentSaver;

    /** @var CommentBuilder */
    protected $commentBuilder;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param TokenStorageInterface      $tokenStorage
     * @param FormFactoryInterface       $formFactory
     * @param ProductRepositoryInterface $productRepository
     * @param CommentRepositoryInterface $commentRepository
     * @param SaverInterface             $commentSaver
     * @param CommentBuilder             $commentBuilder
     * @param NormalizerInterface        $normalizer
     * @param ValidatorInterface         $validator
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        FormFactoryInterface $formFactory,
        ProductRepositoryInterface $productRepository,
        CommentRepositoryInterface $commentRepository,
        SaverInterface $commentSaver,
        CommentBuilder $commentBuilder,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->formFactory = $formFactory;
        $this->productRepository = $productRepository;
        $this->commentRepository = $commentRepository;
        $this->commentSaver = $commentSaver;
        $this->commentBuilder = $commentBuilder;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
    }

    /**
     * List comments made on a product
     *
     * @param int|string $id
     *
     * @AclAncestor("pim_enrich_product_comment")
     *
     * @return JsonResponse
     */
    public function getAction($id)
    {
        $product = $this->findProductOr404($id);
        $comments = $this->commentRepository->getComments(
            ClassUtils::getClass($product),
            $product->getId()
        );

        return new JsonResponse($this->normalizer->normalize($comments, 'json'));
    }

    /**
     * Create a comment on a product
     *
     * @param Request $request
     * @param string  $id
     *
     * @return JsonResponse
     */
    public function postAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);
        $data = json_decode($request->getContent(), true);
        $comment = $this->commentBuilder->buildComment($product, $this->getUser());
        $form = $this->formFactory->create('pim_comment_comment', $comment, ['csrf_protection' => false]);
        $form->submit($data, false);

        if ($form->isValid()) {
            $this->commentSaver->save($comment);

            return new JsonResponse($this->normalizer->normalize($comment, 'json'));
        }

        $violations = $this->validator->validate($comment);

        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = [
                'message'       => $violation->getMessage(),
                'invalid_value' => $violation->getInvalidValue()
            ];
        }

        return new JsonResponse($errors, 400);
    }

    /**
     * Reply to a product comment
     *
     * @param Request $request
     * @param string  $id
     * @param string  $commentId
     *
     * @return JsonResponse
     */
    public function postReplyAction(Request $request, $id, $commentId)
    {
        $product = $this->findProductOr404($id);

        $data = json_decode($request->getContent(), true);
        $data['parent'] = $commentId;

        $reply = $this->commentBuilder->buildComment($product, $this->getUser());
        $form = $this->formFactory->create(
            'pim_comment_comment',
            $reply,
            ['is_reply' => true, 'csrf_protection' => false]
        );
        $form->submit($data, false);

        if ($form->isValid()) {
            $now = new \DateTime();
            $reply->setCreatedAt($now);
            $reply->setRepliedAt($now);
            $comment = $reply->getParent();
            $comment->setRepliedAt($now);

            $this->commentSaver->save($reply);

            return new JsonResponse($this->normalizer->normalize($reply, 'json'));
        }

        $violations = $this->validator->validate($reply);

        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = [
                'message'       => $violation->getMessage(),
                'invalid_value' => $violation->getInvalidValue()
            ];
        }

        return new JsonResponse($errors, 400);
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param int $id the product id
     *
     * @throws NotFoundHttpException
     *
     * @return ProductInterface
     */
    protected function findProductOr404($id)
    {
        $product = $this->productRepository->findOneByWithValues($id);

        if (!$product) {
            throw new NotFoundHttpException(
                sprintf('Product with id %s could not be found.', (string) $id)
            );
        }

        return $product;
    }

    /**
     * Get the user from the Security Context
     *
     * @return UserInterface|null
     */
    protected function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
}
