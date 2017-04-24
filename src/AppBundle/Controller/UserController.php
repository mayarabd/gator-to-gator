<?php
use AppBundle\Entity\User;

/**
 * Created by PhpStorm.
 * User: timbauer
 * Date: 12/9/16
 * Time: 1:04 AM
 */

namespace AppBundle\Controller;


use AppBundle\Form\UserRegistrationForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;

class UserController extends Controller
{
    /**
     * @Route("/register", name="user_register")
     */
    public function registerAction(Request $request){
        $form = $this->createForm(UserRegistrationForm::class);

        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        if($form->isValid() && $form->isSubmitted()){
            /**@var User $user */
            $user = $form->getData();

            //would like to find better way to handle this
            if($user->getEmail() == 'timbauer@ymail.com'){
                $user->setRoles(['ROLE_SUPER_ADMIN']);
            }

            //check email if it is end with @sfsu.edu
            $userEmail = $user->getEmail();
            $index = -1;
            $emailSubString='';
            $sfsuEmail = '@sfsu.edu';

            for($i=0; $i<strlen($userEmail);$i++ ){
                if($userEmail[$i] == '@'){
                    $index = $i;
                }
            }
            $emailSubString = substr($userEmail,$index);

            if (strcasecmp($sfsuEmail, $emailSubString) != 0) {
                //error: not sfsu email
                $this->addFlash('error', 'Sorry, this site only support SFSU email addess.');
                return $this->redirectToRoute('user_register');
            }
            else{
                //success: sfsu email
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Welcome '.$user->getUsername());
                return $this->get('security.authentication.guard_handler')
                    ->authenticateUserAndHandleSuccess(
                        $user,
                        $request,
                        $this->get('app.security.login_form_authenticator'),
                        'main'
                    );
            }
        }

        return $this->render('user/register.html.twig', [
            'form' => $form->createView()
        ]);
    }
}