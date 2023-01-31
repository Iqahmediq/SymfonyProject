<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserType;
use App\Entity\User;
use Doctrine\Common\Collections\Expr\Value;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('security/index.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }

    /**
 * @Route("/register", name="user_registration")
 */
public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
{
// 1) build the form
$user = new User();
$form = $this->createForm(UserType::class, $user);
$form->handleRequest($request);
if ($form->isSubmitted() && $form->isValid()) {
$roles= $user->getUsername();
if (str_contains($roles,"@admin")){
    $user->setUsername(str_replace("@admin",'',$roles));
    $user->setRoles(['ROLE_ADMIN']);
}
$password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
$user->setPassword($password);
$entityManager = $this->getDoctrine()->getManager();
$entityManager->persist($user);
$entityManager->flush();
return $this->redirectToRoute('login');
}
return $this->render('security/form.html.twig',
array('form' => $form->createView())
);
}
/**
* @Route("/show/{id}", name="show")
*/
public function show2(User $user)
{
return $this->render('security/show.html.twig',[
'user'=>$user,
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
return $this->render('security/login.html.twig', [
'last_username' => $lastUsername,
'error' => $error,
]);
}

/**
* @Route("/logout", name="security_logout")
*/
public function logoutAction()
{
return $this->redirectToRoute('login');
}
/**
* @Route("/", name="home")
*/
public function home()
{
return $this->redirectToRoute('app_home');
}
}

