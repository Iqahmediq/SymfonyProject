<?php

namespace App\Controller;
use App\Entity\Formation;
use App\Repository\ParticipantRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use App\Entity\Participant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Repository\FormationRepository;


use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantController extends AbstractController
{
    #[Route('/participant', name: 'app_participant')]
    public function index(ParticipantRepository $ParticipantRepository,FormationRepository $FormationRepository): Response
    {
        
        return $this->render('participant/index.html.twig', [
            'controller_name' => 'FormationController',
            'participants' => $ParticipantRepository->findAll(),
            'formations' => $FormationRepository->findAll(),
            'div'=>intdiv(count($FormationRepository->findAll()),5),
            'mod'=>(count($FormationRepository->findAll())%5),
            'nbre'=>(count($FormationRepository->findAll()))

        ]);
    }
    #[Route('/showF/{id}', name: 'formationsP_id')]
    public function index1(ParticipantRepository $ParticipantRepository,$id): Response
    {
        $formation= $this->getDoctrine()
        ->getRepository(Formation::class)
        ->find(($id));
        
        return $this->render('participant/showF.html.twig', [
            
            'formations' => $formation,
            'participants' => $ParticipantRepository->findAll()

        ]);
    }
    #[Route('/showPart/{id}', name: 'Participant')]
    public function index2(ParticipantRepository $ParticipantRepository,FormationRepository $FormationRepository,$id): Response
    {
        $participant= $this->getDoctrine()
        ->getRepository(Participant::class)
        ->find(($id));
        
        return $this->render('participant/showP.html.twig', [
            'formations' => $FormationRepository->findAll(),
            'participants' => $ParticipantRepository->findAll(),
            'participant' => $participant,

        ]);
    }


     /**
     * @Route("/participant/add",name="ajout_participant")
     */
    public function ajout(ParticipantRepository $ParticipantRepository,Request $request,FormationRepository $FormationRepository){
       
        $participant= new Participant();
        $fb = $this->createFormBuilder(($participant))
        ->add('nom',TextType::class,array('label'=>' '))
            ->add('is_subscribe')
            ->add('fonction',TextType::class,array('label'=>' '))
            ->add('email',TextType::class,array('label'=>' '))
            ->add('formation',EntityType::class,[
                'class'=>Formation::class,
                'choice_label'=> 'titre',
            ])
            ->add('valider',SubmitType::class);
        $form = $fb->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em = $this->getDoctrine()->getManager();
            $em ->persist($participant);
            $em-> flush();
            return $this-> redirectToRoute('app_participant');
        }
        return $this->render('participant/ajouter.html.twig',
        ['fa'=>$form->createView(),
        'formations' => $FormationRepository->findAll(),
        'participants' => $ParticipantRepository->findAll(),
    ]);
    }
     /**
     * @Route("/participant/edit/{id}",name="edit_participant")
     */
    public function edit(Request $request,FormationRepository $FormationRepository,ParticipantRepository $ParticipantRepository,$id){
        $participant= $this->getDoctrine()
        ->getRepository(participant::class)
        ->find($id);
        $fb = $this->createFormBuilder(($participant))
        ->add('nom')
            ->add('is_subscribe')
            ->add('fonction')
            ->add('email')
            ->add('formation',EntityType::class,[
                'class'=>Formation::class,
                'choice_label'=> 'titre',
            ])
            ->add('valider',SubmitType::class);
        $form = $fb->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em = $this->getDoctrine()->getManager();
            $em ->persist($participant);
            $em-> flush();
            return $this-> redirectToRoute('app_participant');
        }
        return $this->render('participant/ajouter.html.twig',
        ['fa'=>$form->createView(),'formations' => $FormationRepository->findAll(),'participants' => $ParticipantRepository->findAll()]);
    }
    /**
     * @Route("/participant/delete/{id}",name="delete_participant")
     */
    public function delete(Request $request,$id){
        $participant= $this->getDoctrine()
        ->getRepository(participant::class)
        ->find($id);
        $em = $this->getDoctrine()->getManager();
            $em ->remove($participant);
            $em-> flush();
            return $this-> redirectToRoute('app_participant');
    }
    
/**
* @Route("/updateProfileUser", name="updateUser")
*/
public function update(FormationRepository $FormationRepository)
{
    $username = $this->get('security.token_storage')->getToken()->getUser()->getUsername();
    $Email = $this->get('security.token_storage')->getToken()->getUser()->getEmail();

return $this->render('participant/info.html.twig', [
    'formations' => $FormationRepository->findAll(),
    'username'=>$username,
    'email'=>$Email,
    'error' => '',
    'error1' => '',
    'error2' => '',
    'success' =>'',
    'success1' =>'',
    'success2' =>'',
]);
}

#[Route('/updateUsernameUser', name: 'updateUsernameUser', methods: ['GET', 'POST'])]
public function updateUsername(FormationRepository $FormationRepository,Request $request,UserPasswordHasherInterface $passwordHasher)
{
    $user = $this->get('security.token_storage')->getToken()->getUser();
    $Email = $this->get('security.token_storage')->getToken()->getUser()->getEmail();
    $em = $this->getDoctrine()->getManager();
    $userex = $em->getRepository(User::class)->find($user);
    $plaintextPassword=$request->query->get('psw');
    $newusername=$request->query->get('username');
    if ($em->getRepository(User::class)->findOneBy(array('username' => $newusername))){// new username exist in base 
        return $this->render('participant/info.html.twig', ['formations' => $FormationRepository->findAll(),
            'username'=>$user->getUsername(),'email'=>$Email,
            'error' => 'failed to change !! username is used','error1' => '','error2' => '',
            'success' =>'','success1' =>'','success2' =>'',
            ]);    
    }
    if ($passwordHasher->isPasswordValid($userex, $plaintextPassword)==false) {//wrong password
        return $this->render('participant/info.html.twig', ['formations' => $FormationRepository->findAll(),
            'username'=>$user->getUsername(),'email'=>$Email,
            'error' => ' failed to change !! password invalid','error1' => '','error2' => '',
            'success' =>'','success1' =>'','success2' =>'',]);
    }
    $userex ->setUsername($newusername); //change username and alright
    $em->persist($userex);
    $em->flush();
    return 
        $this->render('participant/info.html.twig', ['formations' => $FormationRepository->findAll(),//alright
            'username'=>$user->getUsername(),'email'=>$Email,
            'error' => '','error1' => '','error2' => '',
            'success' =>'Username Updated','success1' =>'','success2' =>'',]);
}



#[Route('/updateEmailUser', name: 'updateEmailUser', methods: ['GET', 'POST'])]
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
            return $this->render('participant/info.html.twig', [
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
        return $this->render('participant/info.html.twig', [
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
return $this->render('participant/info.html.twig', [
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
#[Route('/updatePasswordUser', name: 'updatePasswordUser', methods: ['GET', 'POST'])]
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
            return $this->render('participant/info.html.twig', [
                'formations' => $FormationRepository->findAll(),
                'username'=>$user->getUsername(),
                'email'=>$Email,
                'error2' => ' failed to change !! old password invalid','error' => '','error1' => '',
                'success' =>'','success1' =>'','success2' =>'',
                ]);
        }
    }
    else {
        return $this->render('participant/info.html.twig', [
            'formations' => $FormationRepository->findAll(),
            'username'=>$user->getUsername(),
            'email'=>$Email,
            'error2' => 'failed to change !! passwords doesn mutch','error' => '','error1' => '',
            'success' =>'','success1' =>'','success2' =>'',
            ]);
    }

    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->flush();
    
return $this->render('participant/info.html.twig', [
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
