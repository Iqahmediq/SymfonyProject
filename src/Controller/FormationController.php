<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Participant;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use App\Repository\ParticipantRepository;

#[Route('/formation')]
class FormationController extends AbstractController
{
    #[Route('/', name: 'app_formation')]
    public function index(FormationRepository $FormationRepository,ParticipantRepository $ParticipantRepository): Response
    {
           
            return $this->render('formation/index.html.twig', [
            'controller_name' => 'FormationController',
            'formations' => $FormationRepository->findAll(),
            'participants' => $ParticipantRepository->findAll(),
        ]);
    }
    
    #[Route('/show/{id}', name: 'formations_id')]
    public function index1(ParticipantRepository $ParticipantRepository,$id,FormationRepository $FormationRepository): Response
    {
        $formation= $this->getDoctrine()
        ->getRepository(Formation::class)
        ->find(($id));
        
        return $this->render('formation/show.html.twig', [
            'formations' => $formation,
            'participants' => $ParticipantRepository->findAll(),
            'formationsa' => $FormationRepository->findAll()
        ]);
    }
    #[Route('/showP/{id}', name: 'Participant_id')]
    public function index2(ParticipantRepository $ParticipantRepository,FormationRepository $FormationRepository,$id): Response
    {
        $participant= $this->getDoctrine()
        ->getRepository(Participant::class)
        ->find(($id));
        return $this->render('formation/showP.html.twig', [
            'formations' => $FormationRepository->findAll(),
            'participants' => $ParticipantRepository->findAll(),
            'participant' => $participant,
        ]);
    }

    /**
     * @Route("/add",name="ajout_fomation")
     */
    public function ajout(ParticipantRepository $ParticipantRepository,Request $request,FormationRepository $FormationRepository){
        $publicPath = "uploads/formations";
        $formation= new Formation();
        $form = $this->createForm("App\Form\FormationType",$formation);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $image= $form->get('image')->getData();
            $em = $this->getDoctrine()->getManager();
            if($image){
                $x=rand(0,1000);
                $imageName=$formation->getTitre().$x.'.'.$image->guessExtension();
                $image->move($publicPath,$imageName);
                $formation->setImage($imageName);
            }
            $em ->persist($formation);
            $em-> flush();
            return $this-> redirectToRoute('app_formation');
        }
        return $this->render('formation/ajout.html.twig',
        ['f'=>$form->createView(),
            'participants' => $ParticipantRepository->findAll(),
            'formations' => $FormationRepository->findAll()]);
    }
    
/**
* @Route("/updateProfile", name="update")
*/
public function update()
{
    $username = $this->get('security.token_storage')->getToken()->getUser()->getUsername();
    $Email = $this->get('security.token_storage')->getToken()->getUser()->getEmail();

return $this->render('formation/info.html.twig', ['username'=>$username,
    'email'=>$Email,
    'error' => '',
    'error1' => '',
    'error2' => '',
    'success' =>'',
    'success1' =>'',
    'success2' =>'',
]);
}

#[Route('/updateUsername', name: 'updateUsername', methods: ['GET', 'POST'])]
public function updateUsername(FormationRepository $FormationRepository,Request $request,UserPasswordHasherInterface $passwordHasher)
{
    $user = $this->get('security.token_storage')->getToken()->getUser();
    $Email = $this->get('security.token_storage')->getToken()->getUser()->getEmail();
    $em = $this->getDoctrine()->getManager();
    $userex = $em->getRepository(User::class)->find($user);
    $plaintextPassword=$request->query->get('psw');
    $newusername=$request->query->get('username');
    if ($em->getRepository(User::class)->findOneBy(array('username' => $newusername))){// new username exist in base 
        return $this->render('formation/info.html.twig', ['formations' => $FormationRepository->findAll(),
            'username'=>$user->getUsername(),'email'=>$Email,
            'error' => 'failed to change !! username is used','error1' => '','error2' => '',
            'success' =>'','success1' =>'','success2' =>'',
            ]);    
    }
    if ($passwordHasher->isPasswordValid($userex, $plaintextPassword)==false) {//wrong password
        return $this->render('formation/info.html.twig', ['formations' => $FormationRepository->findAll(),
            'username'=>$user->getUsername(),'email'=>$Email,
            'error' => ' failed to change !! password invalid','error1' => '','error2' => '',
            'success' =>'','success1' =>'','success2' =>'',]);
    }
    $userex ->setUsername($newusername); //change username and alright
    $em->persist($userex);
    $em->flush();
    return 
        $this->render('formation/info.html.twig', ['formations' => $FormationRepository->findAll(),//alright
            'username'=>$user->getUsername(),'email'=>$Email,
            'error' => '','error1' => '','error2' => '',
            'success' =>'Username Updated','success1' =>'','success2' =>'',]);
}



#[Route('/updateEmail', name: 'updateEmail', methods: ['GET', 'POST'])]
public function updateEmail(FormationRepository $FormationRepository,Request $request,UserPasswordHasherInterface $passwordHasher)
{
    $user = $this->get('security.token_storage')->getToken()->getUser();
    $Email = $this->get('security.token_storage')->getToken()->getUser()->getEmail();
    $em = $this->getDoctrine()->getManager();
        $userex = $em->getRepository(User::class)->find($user);

    if ($request->query->get('email') && $user){
        $plaintextPassword=$request->query->get('psw');
        $newEmail=$request->query->get('email');
    if (!$em->getRepository(User::class)->findOneBy(array('email' => $newEmail))){
        if ($passwordHasher->isPasswordValid($userex, $plaintextPassword)) {
            $userex ->setEmail($newEmail);
        }
        else {
            return $this->render('formation/info.html.twig', [
                'formations' => $FormationRepository->findAll(),
                'username'=>$user->getUsername(),
                'email'=>$user->getUsername(),
                'error1' => ' failed to change !! password invalid',
                'error' => '',
                'error2' => '',
                'success' =>'',
                'success1' =>'',
                'success2' =>'',
                ]);
        }
    }
    else {
        return $this->render('formation/info.html.twig', [
            'formations' => $FormationRepository->findAll(),
            'username'=>$user->getUsername(),
            'email'=>$Email,
            'error1' => 'failed to change !! Email is used',
             'error' => '',
             'error2' => '',
             'success' =>'',
             'success1' =>'',
             'success2' =>'',

            ]);
    }

    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->flush();
    }
return $this->render('formation/info.html.twig', [
    'formations' => $FormationRepository->findAll(),
    'username'=>$user->getUsername(),
    'email'=>$newEmail,
    'error' => '',
    'error1' => '',
    'error2' => '',
    'success' =>'',
    'success1' =>'Email Updated',
    'success2' =>'',
    ]);
}
#[Route('/updatePassword', name: 'updatePassword', methods: ['GET', 'POST'])]
public function updatePassword(FormationRepository $FormationRepository,Request $request,UserPasswordHasherInterface $passwordHasher)
{
    $user = $this->get('security.token_storage')->getToken()->getUser();
    $Email = $this->get('security.token_storage')->getToken()->getUser()->getEmail();
    $em = $this->getDoctrine()->getManager();
        $userex = $em->getRepository(User::class)->find($user);
        $plaintextPassword=$request->query->get('password');
        $newPassword=$request->query->get('psw');
        $newPasswordc=$request->query->get('pswc');
    if ($newPassword==$newPasswordc){
        if ($passwordHasher->isPasswordValid($userex, $plaintextPassword)) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $newPassword
            );
            $userex ->setPassword($hashedPassword);
        }
        else {
            return $this->render('formation/info.html.twig', [
                'formations' => $FormationRepository->findAll(),
                'username'=>$user->getUsername(),
                'email'=>$Email,
                'error2' => ' failed to change !! old password invalid','error' => '','error1' => '',
                'success' =>'','success1' =>'','success2' =>'',
                ]);
        }
    }
    else {
        return $this->render('formation/info.html.twig', [
            'formations' => $FormationRepository->findAll(),
            'username'=>$user->getUsername(),
            'email'=>$Email,
            'error2' => 'failed to change !! passwords doesn mutch','error' => '','error1' => '',
            'success' =>'','success1' =>'','success2' =>'',
            ]);
    }

    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->flush();
    
return $this->render('formation/info.html.twig', [
    'formations' => $FormationRepository->findAll(),
    'username'=>$user->getUsername(),
    'email'=>$Email,
    'error' => '',
    'error1' => '',
    'error2' => '',
    'success' =>'',
        'success1' =>'',
    'success2' =>'Password Updated',
    ]);
}




}
