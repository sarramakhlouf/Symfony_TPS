<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/{name}', name: 'article_list')]
    public function home($name)
    {
    return $this->render('index.html.twig',['name' => $name]);
    }
}

