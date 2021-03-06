<?php

namespace veilleTechnologique\veilleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use veilleTechnologique\veilleBundle\Entity\User as User;
use veilleTechnologique\veilleBundle\Entity\Liste as Liste;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session as Session;

/**
 * Controller USER. Functions :
 * - Register
 * - Login
 */

class UserController extends Controller
{
    /**
     * @Route("/", name="_user_index")
     * @Template()
     */
    public function indexAction()
    {
        // Utilisé pour les tests
        $session = new Session();
        $user = $this->getDoctrine()->getRepository("veilleTechnologiqueveilleBundle:User")->findOneBy(array('id' => $session->get('id')));
        $listes = $this->getDoctrine()->getRepository("veilleTechnologiqueveilleBundle:Liste")->findBy(array("iduser" => $session->get('id')));
        if($listes)
        {
            // L'utilisateur possède une ou plusieurs listes.
            return array("listes" => $listes, "haveListe" => true,"changeListe" => false);
        }
        else
        {
            // L'utilisateur ne possède aucune liste.
            return array("haveListe" => false,"changeListe" => false);
        }
    }
    
    /**
     * @Route("/logout", name="_user_logout")
     * @Template()
     */
    public function logoutAction()
    {
        $session = new Session();
        $session->remove('id');
        $this->get('session')->getFlashBag()->add('success','Vous vous êtes correctement déconnecté !');
        return $this->redirect($this->generateUrl('_default_index', array()));
    }
    
    /**
     * @Route("/createListe", name="_user_create_liste")
     * @Template()
     */
    public function createListeAction(Request $request)
    {
        $nameListe = $request->get("nameListe");
        $session = new Session();
        $user = $this->getDoctrine()->getRepository("veilleTechnologiqueveilleBundle:User")->findOneBy(array('id' => $session->get('id')));
        
        if($nameListe != "" && $nameListe != null)
        {
            $liste = new Liste();
            $liste->setName($nameListe);
            $liste->setIduser($user);
            
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($liste);
            $manager->flush();
            
            $this->get('session')->getFlashBag()->add('success','La liste '.$liste->getName().' a bien été créée !');
            
            /* Une belle documentation sur le forward, redirect & render se trouve ci-contre
             * http://openclassrooms.com/forum/sujet/symfony2-redirect-ou-render#message-85350139
             */
            $response = $this->forward('veilleTechnologiqueveilleBundle:User:index', array());
            return $response;
        }
        else
        {
            $this->get('session')->getFlashBag()->add('warning','Veuillez saisire le nom de votre liste avant de valider !');
            $response = $this->forward('veilleTechnologiqueveilleBundle:User:index', array());
            return $response;
        }
    }
    
     /**
     * @Route("/deleteListe", name="_user_delete_liste")
     * @Template()
     */
    public function deleteListeAction(Request $request)
    {
        $idListe = $request->get("idListe");
        $em = $this->getDoctrine()->getManager();
        $deleteListe = $em->getRepository("veilleTechnologiqueveilleBundle:Liste")->findOneBy(array('id' => $idListe));
        $em->remove($deleteListe);
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('success','La liste '.$deleteListe->getName().' a bien été supprimer !');
        
        $response = $this->redirect($this->generateUrl('_user_index', array()));
        return $response;
    }
    
     /**
     * @Route("/changeListe", name="_user_change_liste")
     * @Template()
     */
    
    public function changeListeAction (Request $request)
    {
        $idListeChange = $request->get("idListe");
        $change = $request->get("change");
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $listes = $em->getRepository("veilleTechnologiqueveilleBundle:Liste")->findBy(array("iduser" => $session->get('id')));
        
        if($change==true){
            $nameListeChange = $request->get("nameListeChange");
            $laliste = $em->getRepository("veilleTechnologiqueveilleBundle:Liste")->findOneBy(array("id" => $idListeChange));
            $laliste->setName($nameListeChange);
            $em->flush();
            $response = $this->redirect($this->generateUrl('_user_index', array()));
        }
        else{
            $response = $this->render('veilleTechnologiqueveilleBundle:User:index.html.twig', array("haveListe" => true,"listes" => $listes,"changeListe" => true,"idListeChange" => $idListeChange));
        }
        return $response;
        
        
    }
    
}
