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

class ConsumerController extends FOSRestController
{
     /**
   * Lists all Users.
   * @Rest\Get(path = "/api/users")
   *
   * @return Response
   */
  public function getUsersAction()
  {
    $repository = $this->getDoctrine()->getRepository(Consumer::class);
    $user = $this->get('security.token_storage')->getToken()->getUser()->getUsername();
    $consumer = $repository->findBy(['clientName' =>  $user ]);

     if (!$consumer) {
          return new View("there are no users for the moment..", Response::HTTP_NOT_FOUND);
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
   */
  public function getUserAction($id)
  {
    $repository = $this->getDoctrine()->getRepository(Consumer::class);
    $consumer = $repository->find($id);

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