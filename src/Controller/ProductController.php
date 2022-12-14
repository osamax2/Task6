<?php
// src/Controller/ProductController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\Product;
use App\Entity\Article;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;


$encoders = [new XmlEncoder(), new JsonEncoder()];
$normalizers = [new ObjectNormalizer(null,
    null,
    null,
    new ReflectionExtractor()),new GetSetMethodNormalizer(),
    new ArrayDenormalizer()];
$GLOBALS['serializer'] = new Serializer($normalizers, $encoders);

class ProductController extends AbstractController
{

    private $client;
    private $path = 'https://flapotest.blob.core.windows.net/test/ProductData.json';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }


    #[Route(path: ['/', '/home', '/products'],name: 'home')]
    public function postAction(): Response
    {

        //$this->getContainer()->getParameters('app.SOME_SERVICE_URL');
        $response = $this->client->request(
            'GET',
            $this->path,
            ['headers' => [
                'Content-Type' => 'application/json;charset=UTF-8',
                'charset' => 'UTF-8'],
            ],
        );
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            // $statusCode = 200
            $contentType = $response->getHeaders()['content-type'][0];
            // $contentType = 'application/json'

            $products = $GLOBALS['serializer']->deserialize($response->getContent(), Product::class . '[]', 'json');

            return $this->render('product/products.html.twig', [
                'products' => $products,
            ]);
        } else
            throw $this->createNotFoundException();

    }

    #[Route('/products/{id}',name: 'products_show')]
    public function show(int $id): Response
    {
        // ... return a JSON response with the post
        // retrieve the object from database
        $response = $this->client->request(
            'GET',
            $this->path,
            ['headers' => [
                'Content-Type' => 'application/json;charset=UTF-8',
                'charset' => 'UTF-8'],
            ],
        );
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            // $statusCode = 200
            $contentType = $response->getHeaders()['content-type'][0];
            // $contentType = 'application/json'

            $products = $GLOBALS['serializer']->deserialize($response->getContent(), Product::class . '[]', 'json');
            $product = null;
            foreach ($products as $prod) {
                if ($prod->getId() == $id)
                    $product = $prod;
            }
            if (!$product) {
                throw $this->createNotFoundException('The product does not exist');
            } else {
                return $this->render('product/single_product.html.twig', [
                    'product' => $product,
                ]);
            }
        } else {
            throw $this->createNotFoundException();
        }
    }
}