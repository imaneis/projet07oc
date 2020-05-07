<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use App\Entity\Consumer;
use App\Entity\User;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use App\Form\ConsumerFormType;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Contracts\Cache\CallbackInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Doctrine\Common\Persistence\ObjectRepository;
use Psr\Cache\CacheItemInterface;


class ConsumerController extends FOSRestController
{
     /**
   * Lists all Users.
   * @Rest\Get(path = "/api/users")
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
  public function getUsersAction()
  {
    $repository = $this->getDoctrine()->getRepository(Consumer::class);
    $user = $this->get('security.token_storage')->getToken()->getUser()->getUsername();
    $consumer = $repository->findBy(['clientName' =>  $user ]);
     if (!$consumer) {
          return new View("you dont have any user linked to your account for the moment..", Response::HTTP_NOT_FOUND);
     }

    $view = View::create();
    $context = new Context();
    $context->setGroups(['list']);
    $view->setContext($context);
    $view->setData($consumer);
    return $this->handleView($view);

  }

   /**
   * Lists a User.
   * @Rest\Get(path = "/api/user/{id}",name = "user_details", requirements={"id"="\d+"})
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
    $cache = new FilesystemAdapter();
    $consumer = $cache->get('consumer-'.$id, new ConsumerCacheCallable($repository, $id));

     if (!$consumer) {
          return new View("this user does not exisit..", Response::HTTP_NOT_FOUND);
     }

     $clientId = $this->get('security.token_storage')->getToken()->getUser()->getId();

     if ($consumer->getClientId() !== $clientId) {
          return new View("you can't view the details of this user..", Response::HTTP_NOT_FOUND);
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
   * @Rest\Post(path = "/api/user")
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
     *     description="New user create successfully"
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
        return new View("user not found", Response::HTTP_NOT_FOUND);
      }
      else {
        $em->remove($consumer);
        $em->flush();
      }
        return new View("The user was deleted successfully", Response::HTTP_OK);

    }

}


class ConsumerCacheCallable implements CallbackInterface
{
    private $repository;
    private $id;

    function __construct(ObjectRepository $repository, int $id)
    {
        $this->repository = $repository;
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(CacheItemInterface $item, bool &$save)
    {
        return $this->repository->find($this->id);
    }
}