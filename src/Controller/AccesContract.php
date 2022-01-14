<?php
/**
 * Created by PhpStorm.
 * User: Marta
 * Date: 04/01/2018
 * Time: 13:15
 */

namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\ApiModel;

class AccesContract
{
    /**
     * @Route("/access")
     */
    public function number()
    {
        $number = mt_rand(0, 100);

        return new Response(
            '<html><body>Lucky number: '.$number.'</body></html>'
        );
    }

    /**
     * @Route("/test")
     */
    public function test()
    {
        //command server : C:\xampp\php\php.exe bin/console server:run
        $model= new ApiModel();

        /*$foo1= $model->setExpirationDate("f1046a81aefa46a1e257855f2bb955ec07180ad96c47bc85b8faf2656b918459","tests write Expiration Date ");
        $foo2= $model->setFinancialInstitutionName("f1046a81aefa46a1e257855f2bb955ec07180ad96c47bc85b8faf2656b918459","tests write financial institution name");
        $foo3= $model->setPaymentDate("f1046a81aefa46a1e257855f2bb955ec07180ad96c47bc85b8faf2656b918459","tests write paymet date");
        $foo4= $model->setFactoringExpirationDate("f1046a81aefa46a1e257855f2bb955ec07180ad96c47bc85b8faf2656b918459","tests write factoring expiration date");
        $foo5= $model->setFactoringTotal("f1046a81aefa46a1e257855f2bb955ec07180ad96c47bc85b8faf2656b918459","tests write factoring total");
        return new Response($foo1.$foo2.$foo3.$foo4.$foo5);*/
        //$foo = $model->getInvoiceNumber("f1146a81aefa46a1e257855f2bb955ec07180ad96c47bc85b8faf2656b918459");
        //$foo = $model->getDocumentCount();
        //$foo = $model->getDocumentAtIndex(0);
