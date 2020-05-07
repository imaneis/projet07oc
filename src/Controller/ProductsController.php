<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use App\Entity\Products;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;


class ProductsController extends FOSRestController
{
    /**
   * Lists all Products.
   * @Rest\Get(path = "/api/products",name = "list_products")
   *
   * @return Response
   */
  public function getProductsAction()
  {
    $repository = $this->getDoctrine()->getRepository(Products::class);
    $products = $repository->findall();

     if (!$products) {
          return new View("there are no products for the moment..", Response::HTTP_NOT_FOUND);
     }

    $view = View::create();
    $context = new Context();
    $context->setGroups(['list']);
    $view->setContext($context);
    $view->setData($products);
    return $this->handleView($view);

  }

  /**
   * Lists a Product.
   * @Rest\Get(path = "/api/product/{id}",name = "product_details", requirements={"id"="\d+"})
   *
   * @return Response
   */
  public function getProductAction($id)
  {
    $repository = $this->getDoctrine()->getRepository(Products::class);
    $product = $repository->find($id);

     if (!$product) {
          return new View("this product does not exisit..", Response::HTTP_NOT_FOUND);
     }

    return $this->handleView($this->view($product));
  }

}