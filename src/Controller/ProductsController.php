<?php

namespace App\Controller;

use Doctrine\Common\Persistence\ObjectRepository;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use App\Entity\Products;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Contracts\Cache\CallbackInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


class ProductsController extends FOSRestController
{


    /**
   * Lists all Products.
   * @Rest\Get(path = "/api/products",name = "list_products")
   *
   * @return Response
     *
     * @SWG\Tag(name="products")
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token required to access resources"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Get the products list with success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Products::class, groups={"list"}))
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Need a valid token to access this request"
     * )
     
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
   * @Rest\Get(path = "/api/product/{id}", name = "product_details", requirements={"id"="\d+"})
   *
   * @return Response
   * @SWG\Tag(name="products")
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
     *     description="The unique product identifier",
     *     required=true
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Get the detail of a mobile with success",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Products::class))
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Need a valide token to access this request"
     * )
   */
 public function getProductAction($id)
  {
    $repository = $this->getDoctrine()->getRepository(Products::class);
    $cache = new FilesystemAdapter();
    $product = $cache->get('product-'.$id, new ProductCacheCallable($repository, $id));
     if (!$product) {
          return new View("this product does not exisit..", Response::HTTP_NOT_FOUND);
     }

    return $this->handleView($this->view($product));
  }

}

class ProductCacheCallable implements CallbackInterface
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