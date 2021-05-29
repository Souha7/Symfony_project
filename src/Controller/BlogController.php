<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Form\ArticleType;


class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo): Response
    {

        #Pour discuter avec Doctrine afin de nous donner un repository(l'Article)
        //$repo = $this->getDoctrine()->getRepository(Article::class); 
        #On n'est plus besoin de cette ligne parce qu'on ajouté l'injection de dépendance


       //$article = $repo->find(12); # Trouver l'article numéro12
       //$articles = $repo->findByTitle('Titre de l\'article');
        $articles = $repo->findAll();

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',

            //pour passer à Twig tous nos variables:
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home(){
        return $this->render('blog/home.html.twig');
    }

    /**
     * @Route("/blog/new", name="blog_create")
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function form(Article $article = null, Request $request, EntityManagerInterface $manager){ #parfois l'article sera null (pour éviter une erreur)
        #Pour Analyser la requête, on utilise l'injection de dépendances:Symfony peut nous fournir les élèments dont nous avons besoin!
        #HttpFoundation\Request:C'est la classe qui premet d'analyser/manipuler la requête HTPP


        if(!$article){
            $article = new Article();
        } #Si il y'a pas d'article, on va créer un!

      

       //$form = $this->createFormBuilder($article)

                // ->add('title')
                 //   ->add('content')
                 //   ->add('image')
                 //   ->getForm(); #On peut effacer tout çà après la création du formulaire avec CLI

                 
        $form = $this->createForm(ArticleType::class, $article);



        $form->handleRequest($request); #demander au formulaire d'analyser la requete Http qu'on a passé ici en paramètre

        if($form->isSubmitted() && $form->isValid()) {

            if(!$article->getId()){
                $article->setCreatedAt(new \DateTime());
            }#Si l'article existe déjà et on veut le modifier, on touche pas à la date de création!
            

            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
        }

        return $this->render('blog/create.html.twig',[
            'formArticle' => $form->createView(),

            'editMode' => $article->getId() !==null
        ]);
        
    }

    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article){

        //$repo = $this->getDoctrine()->getRepository(Article::class);
        #On n'est plus besoin de cette ligne parce qu'on ajouté l'injection de dépendance

        //$article = $repo->find($id); #Trouve-moi l'article ayant l'id comme envoyé dans l'adresse en haut
        #Encore une fois,On aurai plus besoin ded cette ligne si on met Article $article grace au PARAM CONVERTER ! 


        return $this->render('blog/show.html.twig', [
            'article' => $article
        ]);
    }

    /**
     * @Route("/blog/delete/{id}", name="blog_delete")
     */
    public function delete(Article $article){
        $entityManager = $this->getDoctrine()->getManager();
        $article = $entityManager->getRepository(Article::class)->find($article->getId());

        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('blog');
    }    
}