<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use App\Entity\Products;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductsRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;


class ProductsController extends FOSRestController
{

    const LIMIT_DEFAULT = 3;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ProductsRepository
     */
    private $ProductsRepository;

    /** @var SerializerInterface */
    private $serializer;

    /**
     *  constructor.
     * @param EntityManagerInterface $entityManager
     * @param ProductsRepository $ProductsRepository
     * @param SerializerInterface $serializer
     */
    public function __construct(EntityManagerInterface $entityManager, ProductsRepository $ProductsRepository, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->ProductsRepository = $ProductsRepository;
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
   * Lists all Products.
   * @Rest\Get(path = "/api/products", name = "list_products")
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
  public function getProductsAction(Request $request)
  {
    $page = intval($request->query->get('page', null));
    $limit = intval($request->query->get('limit', self::LIMIT_DEFAULT));

    $products = $this->ProductsRepository->findAllPaginated($page, $limit);
    $nbPages = ceil(count($products) / $limit);

     if ($nbPages == 0) {
          return new View("There are no products for the moment", Response::HTTP_NOT_FOUND);
     }

     return $this->json([
      'Products' => $products,
      '_link' => [
          "self" => [
              "href" => $this->generateUrl('list_products', $this->paginationInfo($page, $limit, $nbPages), UrlGeneratorInterface::ABSOLUTE_URL)
          ],
          "first" => [
              "href" => $this->generateUrl('list_products', $this->paginationInfo(0, $limit, $nbPages), UrlGeneratorInterface::ABSOLUTE_URL)
          ],
          "prev" => [
              "href" => $this->generateUrl('list_products', $this->paginationInfo($page - 1, $limit, $nbPages), UrlGeneratorInterface::ABSOLUTE_URL)
          ],
          "next" => [
              "href" => $this->generateUrl('list_products', $this->paginationInfo($page + 1, $limit, $nbPages), UrlGeneratorInterface::ABSOLUTE_URL)
          ],
          "last" => [
              "href" => $this->generateUrl('list_products', $this->paginationInfo($nbPages - 1, $limit, $nbPages), UrlGeneratorInterface::ABSOLUTE_URL)
          ]
      ]
  ], 200, []);


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
    $product = $repository->find($id);

     if (!$product) {
          return new View("This product does not exist", Response::HTTP_NOT_FOUND);
     }

    return $this->handleView($this->view($product));
  
  }

}