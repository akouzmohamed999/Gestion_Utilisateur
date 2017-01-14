<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Definition\Exception\Exception;
use AppBundle\Form\LoginForm;
use AppBundle\Form\RegisterForm;
use AppBundle\Form\UpdateForm;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DefaultController extends Controller
{
    /**
     * @Route("/home",name="homePage")
     * @Route("/")
     * @Security("is_granted('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {
        $_SESSION["user"] = $request->getSession()->get("SECURITY::LAST_USERNAME");
        if($_SESSION["user"]==null){
            $_SESSION["user"] = $this->get('session')->get('user');
        }

        return $this->render('default/home.html.twig',["user"=>$_SESSION["user"]]);
    }


    /**
     * @Route("/login", name="security_login")
     */
    public function loginAction()
    {
        $authentificationUtils = $this->get('security.authentication_utils');

        $lastUser = $authentificationUtils->getLastUsername();
        $error = $authentificationUtils->getLastAuthenticationError();

        $form = $this->createForm(LoginForm::class,['_username'=>$lastUser])->createView();
        return $this->render("default/login.html.twig",['error'=>$error,'form'=>$form]);
    }

    /**
     * @Route("/logout",name="security_logout")
     */

    public function logoutAction(){
        throw new Exception('this should not be reached');
    }

    /**
     * @Route("/CreateUser",name="register")
     */
    public function registerAction(Request $request){


        $form = $this->createForm(RegisterForm::class);
        $form->handleRequest($request);
        $error = "";
        if($form->isValid()){

            $data =  $form->getData();
            $email = $data['_username'];
            $password = $data['_password'];
            $passwordRepeat = $data['_password_repeat'];
            if($password != $passwordRepeat){
                $error = "Les mots de passes sont pas identiques";
                return $this->render('default/register.html.twig',["form"=>$form->createView(),"error"=>$error]);
            }
            $user = new User();
            $user->setEmail($email);
            $user->setPlainPassword($password);
            $user->setRoles("ROLE_USER");
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $session = $this->get("session");
            $session->set('user',$email);
            $_SESSION["user"]=$email;
            return $this->get('security.authentication.guard_handler')
                ->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $this->get('app.security.login_form_authenticator'),
                    'main'
                );
        }
        return $this->render('default/register.html.twig',["form"=>$form->createView(),"error"=>$error]);

    }

    /**
     * @Route("/UpdateUser",name="update")
     * @Security("is_granted('ROLE_USER')")
     */
    public function updateAction(Request $request){

        $form = $this->createForm(UpdateForm::class);
        $form->handleRequest($request);
        $error = "";
        if($form->isValid()){

            $data =  $form->getData();
            $email = $data['_username'];
            $password = $data['_password'];
            $passwordRepeat = $data['_password_repeat'];
            if($password != $passwordRepeat){
                $error = "Les mots de passes sont pas identiques";
                return $this->render('default/register.html.twig',["form"=>$form->createView(),"error"=>$error]);
            }
            $em = $this->getDoctrine()->getManager();
            $repo =$em->getRepository(User::class);

            $user=$repo->findOneBy(array("email"=>$_SESSION["user"]));

            $user->setEmail($email);
            $user->setPlainPassword($password);
            $user->setRoles("ROLE_USER");
            $em->flush();
            return $this->get('security.authentication.guard_handler')
                ->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $this->get('app.security.login_form_authenticator'),
                    'main'
                );
        }
        return $this->render('default/update.html.twig',["form"=>$form->createView(),"error"=>$error]);
    }

    /**
     * @Route("/DeleteUser",name="delete")
     * @Security("is_granted('ROLE_USER')")
     */
    public function deleteAction(){
        $em = $this->getDoctrine()->getManager();
        $repo =$em->getRepository(User::class);
        $user=$repo->findOneBy(array("email"=>$_SESSION["user"]));
        $em->remove($user);
        $em->flush();
        return  $this->redirectToRoute("homePage");
    }


}
