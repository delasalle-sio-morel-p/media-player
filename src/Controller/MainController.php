<?php
/**
 * Created by PhpStorm.
 * User: lbouvet
 * Date: 19/11/2018
 * Time: 11:23
 */

namespace App\Controller;


use App\Entity\Media;
use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class MainController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function home(EntityManagerInterface $em)
    {
        $user = null;
        if($this->isUser() == true)
            $user = $this->getUser();

        if($this->isAdmin() == true){
            $user = $this->getUser();
            return $this->redirectToRoute('admin');
        }

        return $this->render("main/home.html.twig", [
            'status' => $this->isConnected(),
            'isUser' => $this->isUser(),
            'user' => $user,
            'medias' => $em->getRepository(Media::class)->findBy(array('isPublished' => true),array('dateCreated' => 'DESC'))
        ]);
    }

    /**
     * @Route("/register", name="main_register")
     */
    public function registration(Request $request,
                                 UserPasswordEncoderInterface $encoder,
                                 EntityManagerInterface $em){

        $user = new Utilisateur();

        $form = $this->createForm(UtilisateurType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //crypter le mot de passe
            $pass = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($pass);
            $user->setRoles(['ROLE_USER']);

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('home');

        }

        return $this->render('main/register.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('main/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
            'status' => $this->isConnected(),
        ));
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(){

    }

    public function isConnected(){
        $user = $this->getUser();
        if($user!=null){
            dump("connected");
            return true;
        }
        else{
            dump("not connected");
            return false;
        }
    }

    public function isUser(){
        $user = $this->getUser();
        if($user!=null){
            $roles = $user->getRoles();
            if($roles[0]=="ROLE_USER")
                return true;
            return false;
        }
        else{
            return false;
        }
    }

    public function isAdmin(){
        $user = $this->getUser();
        if($user!=null){
            $roles = $user->getRoles();
            if($roles[0]=="ROLE_ADMIN")
                return true;
            return false;
        }
        else{
            return false;
        }
    }

}





