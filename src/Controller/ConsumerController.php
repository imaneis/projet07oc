<?php

namespace App\Controller;

use App\Repository\ConsumerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use App\Entity\Consumer;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use App\Form\ConsumerFormType;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;


class ConsumerController extends FOSRestController
{

    const LIMIT_DEFAULT = 3;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ConsumerRepository
     */
    private $ConsumerRepository;

    /** @var SerializerInterface */
    private $serializer;

    /**
     *  constructor.
     * @param EntityManagerInterface $entityManager
     * @param ConsumerRepository $ConsumerRepository
     * @param SerializerInterface $serializer
     */
    public function __construct(EntityManagerInterface $entityManager, ConsumerRepository $ConsumerRepository, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->ConsumerRepository = $ConsumerRepository;
        $this->serializer = $serializer;
    }


    public function paginationInfo($page, $limit, $nbPages = null)
    {
        $paginationInfo = [];

        if ($page !== 0) {
            $paginationInfo['page'] = $page;
        }

        if ($page < 0) {
            $paginationInfo['page'] = $page + 1;
        }

         if ($page + 1 > $nbPages) {
            $paginationInfo['page'] = $page - 1;
        }

        if ($limit !== self::LIMIT_DEFAULT) {
            $paginationInfo['limit'] = $limit;
        }

        return $paginationInfo;
    }

     /**
   * Lists all Users.
   * @Rest\Get(path = "/api/users", name = "list_users")
   *
   * @return Response
   * @SWG\Tag(name="consumers")
    * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token required to access resources"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Get the user list with success",
    @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Consumer::class, groups={"list"}))
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Need a valid token to access this request"
     * )
   */
  public function getUsersAction(Request $request)
  {
      $page = intval($request->query->get('page', null));
      $limit = intval($request->query->get('limit', self::LIMIT_DEFAULT));

      $user = $this->get('security.token_storage')->getToken()->getUser()->getUsername();

      $consumer = $this->ConsumerRepository->findAllPaginated($page, $limit, $user);

      $nbPages = ceil(count($consumer) / $limit);

     if ($nbPages == 0) {
          return new View("you dont have any user linked to your account for the moment..", Response::HTTP_NOT_FOUND);
     }

      return $this->json([
          'Users' => $consumer,
          '_link' => [
              "self" => [
                  "href" => $this->generateUrl('list_users', $this->paginationInfo($page, $limit, $nbPages), UrlGeneratorInterface::ABSOLUTE_URL)
              ],
              "first" => [
                  "href" => $this->generateUrl('list_users', $this->paginationInfo(0, $limit, $nbPages), UrlGeneratorInterface::ABSOLUTE_URL)
              ],
              "prev" => [
                  "href" => $this->generateUrl('list_users', $this->paginationInfo($page - 1, $limit, $nbPages), UrlGeneratorInterface::ABSOLUTE_URL)
              ],
              "next" => [
                  "href" => $this->generateUrl('list_users', $this->paginationInfo($page + 1, $limit, $nbPages), UrlGeneratorInterface::ABSOLUTE_URL)
              ],
              "last" => [
                  "href" => $this->generateUrl('list_users', $this->paginationInfo($nbPages - 1, $limit, $nbPages), UrlGeneratorInterface::ABSOLUTE_URL)
              ]
          ]
      ], 200, []);

  }

   /**
   * Lists a User.
   * @Rest\Get(path = "/api/user/{id}", name = "user_details", requirements={"id"="\d+"})
   *
   * @return Response
   * @SWG\Tag(name="consumers")
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token required to access resources"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="The unique user identifier",
     *     required=true
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Get the detail of a user with success",
      @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Consumer::class, groups={"detail"}))
     *     )
     * )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Need a valide token to access this request"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No user found"
     * )
   */
  public function getUserAction($id)
  {
    $repository = $this->getDoctrine()->getRepository(Consumer::class);
    $consumer = $repository->find($id);

     if (!$consumer) {
          return new View("This user does not exist", Response::HTTP_NOT_FOUND);
     }

     $clientId = $this->get('security.token_storage')->getToken()->getUser()->getId();

     if ($consumer->getClientId() !== $clientId) {
          return new View("You can't view the details of this user", Response::HTTP_NOT_FOUND);
     }

    $view = View::create();
    $context = new Context();
    $context->setGroups(['detail']);
    $view->setContext($context);
    $view->setData($consumer);
    return $this->handleView($view);
  }

  /**
   * Create a User.
   * @Rest\Post(path = "/api/user", name = "create_user")
   *
   * @return Response
   * @SWG\Tag(name="consumers")
   * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token required to create resources"
     * )
     * @SWG\Parameter(
     *     name="fullname",
     *     in="body",
     *     description="user fullname",
     *     required=true,
     *     @SWG\Schema(type="string")
     * )
     * @SWG\Parameter(
     *     name="age",
     *     in="body",
     *     description="the user age",
     *     required=true,
     *     @SWG\Schema(type="integer")
     * )
     * @SWG\Parameter(
     *     name="city",
     *     in="body",
     *     description="the user city",
     *     required=true,
     *     @SWG\Schema(type="string")
     * )
     * @SWG\Response(
     *     response=201,
     *     description="New user created successfully"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Invalid json message received"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Need a valid token to access this request"
     * )
   */
  public function postUserAction(Request $request)
  {
    $consumer = new Consumer();
    $form = $this->createForm(ConsumerFormType::class, $consumer);
    $data = json_decode($request->getContent(), true);
    $form->submit($data);
    if ($form->isSubmitted() && $form->isValid()) {
       $clientName = $this->get('security.token_storage')->getToken()->getUser()->getUsername();
        $consumer->setClientName($clientName);
        $clientId = $this->get('security.token_storage')->getToken()->getUser()->getId();
        $consumer->setClientId($clientId);
        $consumer->setAddedOn(new \DateTime());
      $em = $this->getDoctrine()->getManager();
      $em->persist($consumer);
      $em->flush();
      return $this->handleView($this->view(['status' => 'The user was added to the database'], Response::HTTP_CREATED));
    }
    return $this->handleView($this->view($form->getErrors()));
  }

   /**
   * Delete a User.
   * @Rest\Delete(path = "/api/delete/{id}", name = "delete_user")
   *
   * @return Response
   * @SWG\Tag(name="consumers")
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token required to delete resources"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="The unique user identifier",
     *     required=true
     * )
     * @SWG\Response(
     *     response=200,
     *     description="user deleted successfully"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Need a valide token to access this request"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No user found"
     * )
   */
    public function deleteUserAction($id)
    {
      $em = $this->getDoctrine()->getManager();
      $consumer = $this->getDoctrine()->getRepository(Consumer::class)->find($id);
      if (empty($consumer)) {
        return new View("This user does not exist", Response::HTTP_NOT_FOUND);
      }
      else {
        $em->remove($consumer);
        $em->flush();
      }
        return new View("The user was deleted successfully", Response::HTTP_OK);

    }

}