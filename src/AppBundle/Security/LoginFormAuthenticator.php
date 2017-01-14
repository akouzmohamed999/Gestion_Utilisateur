<?php

/**
 * Created by PhpStorm.
 * User: Mohamed
 * Date: 14/01/2017
 * Time: 12:35
 */
namespace AppBundle\Security;
use Doctrine\ORM\EntityManager;
use AppBundle\Form\LoginForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;
    function __construct(FormFactoryInterface $formFactory, EntityManager $entityManager, RouterInterface $router, UserPasswordEncoder $userPasswordEncoder)
    {
        $this->formfactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    protected function getLoginUrl()
    {
        return $router = $this->router->generate("security_login");
    }

    public function getCredentials(Request $request)
    {
        $login = $this->router->generate("security_login");
        if(!($request->getPathInfo()==$login&&$request->isMethod("POST"))){
            return null;
        }
        $form = $this->formfactory->create(LoginForm::class);
        $form->handleRequest($request);
        $data =  $form->getData();
        $lastUser= $request->getSession()->set('SECURITY::LAST_USERNAME',$data['_username']);
        return $data;

    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $em = $this->entityManager->getRepository("AppBundle:User");
        $user = $em->findOneBy(["email"=>$credentials["_username"]]);
        return $user;
    }


    public function checkCredentials($credentials, UserInterface $user)
    {
        $password = $credentials['_password'];
        if($this->userPasswordEncoder->isPasswordValid($user,$password)){
            return true;
        }
        return false;
    }
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);

        if (!$targetPath) {
            $targetPath = $this->getDefaultSuccessRedirectUrl();
        }

        return new RedirectResponse($targetPath);
    }

    private function getDefaultSuccessRedirectUrl()
    {
        return $this->router->generate('homePage');
    }


}